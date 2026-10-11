<?php
/**
 * Instant alerts for new leads: e-mail to the team (routed by form),
 * a confirmation e-mail to the visitor, and an optional WhatsApp alert.
 */
defined('ARGENTUM') || exit;

/** Sends every alert configured for a new lead and records what went out. */
function notify_lead(array $lead, array $data): void
{
    $sent = [];
    $type = $lead['type'];
    $label = $type === 'collection' ? 'Collection request' : 'Silver purchase enquiry';
    $flag = $lead['priority'] === 'high' ? '[PRIORITY] ' : '';
    $subject = $flag . $label . ' #' . $lead['id'] . ' — ' . $lead['organisation'];

    // 1. Team e-mail, routed to the people who handle this form
    $recipients = lead_recipients($type);
    if ($recipients) {
        $html = lead_email_html($lead, $data, $label);
        $text = lead_email_text($lead, $data, $label);
        foreach ($recipients as $to) {
            if (send_mail($to, $subject, $html, $text, $lead['email'])) {
                $sent[] = 'email';
            }
        }
    }

    // 2. Confirmation to the visitor, in their language
    if (setting('notifications.auto_reply', true) && $lead['email']) {
        set_lang($lead['lang']);
        $body = t('emails.reply_' . $type, ['name' => $lead['contact_person']]);
        $html = '<div style="font:15px/1.6 Arial,sans-serif;color:#121816;max-width:560px" dir="' . (is_rtl() ? 'rtl' : 'ltr') . '">'
            . paragraphs($body) . '<p style="color:#4B5553">Argentum — ' . e(t('meta.slogan')) . '</p></div>';
        if (send_mail($lead['email'], t('emails.reply_subject'), $html, $body, (string) setting('contact.email', ''))) {
            $sent[] = 'reply';
        }
    }

    // 3. WhatsApp alert to the team
    $numbers = array_filter(array_map('trim', explode(',', (string) setting('notifications.whatsapp_numbers', ''))));
    foreach ($numbers as $number) {
        $msg = $flag . "New {$label} #{$lead['id']}\n{$lead['organisation']} — {$lead['contact_person']}\n"
            . "{$lead['phone']} · {$lead['email']}\n"
            . ($lead['quantity'] !== '' ? "Quantity: {$lead['quantity']}\n" : '')
            . absolute_url('admin/leads/' . $lead['id']);
        if (send_whatsapp($number, $msg, $lead)) {
            $sent[] = 'whatsapp';
        }
    }

    db()->prepare('UPDATE leads SET notified = ? WHERE id = ?')
        ->execute([implode(',', array_unique($sent)), $lead['id']]);
}

/** Team addresses for a lead type, falling back to the main lead address. */
function lead_recipients(string $type): array
{
    $key = $type === 'collection' ? 'notifications.collection_emails' : 'notifications.silver_emails';
    $list = (string) setting($key, '') ?: (string) setting('notifications.lead_email', '');
    return array_values(array_filter(array_map('trim', explode(',', $list)), fn ($a) => filter_var($a, FILTER_VALIDATE_EMAIL)));
}

/** Human-readable rows for e-mails and the admin panel. */
function lead_rows(string $type, array $data): array
{
    $rows = [];
    $saved = current_lang();
    set_lang('en');
    foreach (form_schema($type) as $name => $f) {
        if (!array_key_exists($name, $data) || $data[$name] === '' || $data[$name] === []) {
            continue;
        }
        $value = $data[$name];
        if (isset($f['options'])) {
            $value = implode(', ', array_map(fn ($v) => t("forms.fields.$name.options.$v") ?: $v, (array) $value));
        }
        $rows[t("forms.fields.$name.label") ?: $name] = (string) $value;
    }
    set_lang($saved);
    return $rows;
}

function lead_email_html(array $lead, array $data, string $label): string
{
    $rows = '';
    foreach (lead_rows($lead['type'], $data) as $k => $v) {
        $rows .= '<tr><td style="padding:8px 12px;color:#4B5553;border-bottom:1px solid #EEF2F0;white-space:nowrap;vertical-align:top">' . e($k)
            . '</td><td style="padding:8px 12px;border-bottom:1px solid #EEF2F0">' . nl2br(e($v)) . '</td></tr>';
    }
    $priority = $lead['priority'] === 'high'
        ? '<p style="background:#EEF2F0;border-left:4px solid #2F6B1C;padding:10px 14px;margin:0 0 16px">Priority lead — large volume or recurring order. Call back first.</p>' : '';
    return '<div style="font:15px/1.5 Arial,sans-serif;color:#121816;max-width:640px">'
        . '<h2 style="font:600 20px Georgia,serif;margin:0 0 4px">' . e($label) . ' #' . (int) $lead['id'] . '</h2>'
        . '<p style="color:#4B5553;margin:0 0 16px">Received ' . e($lead['created_at']) . ' · page language: ' . strtoupper(e($lead['lang'])) . '</p>'
        . $priority
        . '<table style="border-collapse:collapse;width:100%">' . $rows . '</table>'
        . '<p style="margin:20px 0 0"><a href="' . e(absolute_url('admin/leads/' . $lead['id'])) . '" style="background:#2F6B1C;color:#fff;padding:10px 16px;text-decoration:none;border-radius:6px">Open in admin panel</a></p>'
        . '</div>';
}

function lead_email_text(array $lead, array $data, string $label): string
{
    $out = "$label #{$lead['id']}\n\n";
    foreach (lead_rows($lead['type'], $data) as $k => $v) {
        $out .= "$k: $v\n";
    }
    return $out . "\n" . absolute_url('admin/leads/' . $lead['id']) . "\n";
}

/* ---------- E-mail ---------- */

function send_mail(string $to, string $subject, string $html, string $text, string $replyTo = ''): bool
{
    $fromEmail = (string) config('smtp.from_email', 'no-reply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
    $fromName = (string) config('smtp.from_name', 'Argentum');
    $boundary = 'b' . bin2hex(random_bytes(8));
    $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    $headers = [
        'From: =?UTF-8?B?' . base64_encode($fromName) . '?= <' . $fromEmail . '>',
        'MIME-Version: 1.0',
        'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
    ];
    if ($replyTo && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
        $headers[] = 'Reply-To: ' . $replyTo;
    }
    $body = "--$boundary\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n"
        . chunk_split(base64_encode($text))
        . "--$boundary\r\nContent-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n"
        . chunk_split(base64_encode($html))
        . "--$boundary--\r\n";

    try {
        if (config('smtp.host')) {
            return smtp_send($to, $encodedSubject, $headers, $body, $fromEmail);
        }
        return @mail($to, $encodedSubject, $body, implode("\r\n", $headers), '-f' . $fromEmail);
    } catch (Throwable $e) {
        error_log('Argentum mail error: ' . $e->getMessage());
        return false;
    }
}

/** Minimal SMTP client (SSL or STARTTLS + AUTH LOGIN), so no Composer package is needed. */
function smtp_send(string $to, string $subject, array $headers, string $body, string $from): bool
{
    $host = (string) config('smtp.host');
    $port = (int) config('smtp.port', 465);
    $enc = (string) config('smtp.encryption', 'ssl');
    $socket = @stream_socket_client(($enc === 'ssl' ? 'ssl://' : 'tcp://') . $host . ':' . $port, $errno, $errstr, 10);
    if (!$socket) {
        throw new RuntimeException("SMTP connect failed: $errstr");
    }
    stream_set_timeout($socket, 10);
    $read = function () use ($socket): string {
        $data = '';
        while (($line = fgets($socket, 515)) !== false) {
            $data .= $line;
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }
        return $data;
    };
    $cmd = function (string $command, array $expect) use ($socket, $read): string {
        fwrite($socket, $command . "\r\n");
        $reply = $read();
        if (!in_array((int) substr($reply, 0, 3), $expect, true)) {
            throw new RuntimeException('SMTP error after "' . strtok($command, ' ') . '": ' . trim($reply));
        }
        return $reply;
    };
    $read();
    $ehlo = 'EHLO ' . ($_SERVER['SERVER_NAME'] ?? 'localhost');
    $cmd($ehlo, [250]);
    if ($enc === 'tls') {
        $cmd('STARTTLS', [220]);
        stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
        $cmd($ehlo, [250]);
    }
    if (config('smtp.username')) {
        $cmd('AUTH LOGIN', [334]);
        $cmd(base64_encode((string) config('smtp.username')), [334]);
        $cmd(base64_encode((string) config('smtp.password')), [235]);
    }
    $cmd('MAIL FROM:<' . $from . '>', [250]);
    $cmd('RCPT TO:<' . $to . '>', [250, 251]);
    $cmd('DATA', [354]);
    $message = 'To: <' . $to . ">\r\nSubject: " . $subject . "\r\nDate: " . date('r') . "\r\n"
        . implode("\r\n", $headers) . "\r\n\r\n" . preg_replace('/^\./m', '..', $body);
    $cmd($message . "\r\n.", [250]);
    $cmd('QUIT', [221]);
    fclose($socket);
    return true;
}

/* ---------- WhatsApp ---------- */

function send_whatsapp(string $number, string $message, array $lead): bool
{
    $number = digits($number);
    try {
        switch (config('whatsapp_alerts.provider')) {
            case 'callmebot':
                $url = 'https://api.callmebot.com/whatsapp.php?' . http_build_query([
                    'phone' => $number, 'text' => $message, 'apikey' => config('whatsapp_alerts.callmebot_key'),
                ]);
                return http_get($url) !== null;
            case 'cloud':
                $payload = [
                    'messaging_product' => 'whatsapp',
                    'to'       => $number,
                    'type'     => 'template',
                    'template' => [
                        'name'       => config('whatsapp_alerts.cloud_template', 'new_lead'),
                        'language'   => ['code' => config('whatsapp_alerts.cloud_language', 'en')],
                        'components' => [[
                            'type'       => 'body',
                            'parameters' => [
                                ['type' => 'text', 'text' => ($lead['type'] === 'collection' ? 'Collection' : 'Silver') . ' #' . $lead['id']],
                                ['type' => 'text', 'text' => $lead['organisation'] ?: '-'],
                                ['type' => 'text', 'text' => $lead['phone'] ?: '-'],
                            ],
                        ]],
                    ],
                ];
                $url = 'https://graph.facebook.com/v20.0/' . config('whatsapp_alerts.cloud_phone_id') . '/messages';
                return http_post_json($url, $payload, (string) config('whatsapp_alerts.cloud_token')) !== null;
        }
    } catch (Throwable $e) {
        error_log('Argentum WhatsApp error: ' . $e->getMessage());
    }
    return false;
}

/* ---------- HTTP ---------- */

function http_request(string $url, string $method = 'GET', ?string $body = null, array $headers = []): ?string
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 8,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_HTTPHEADER     => $headers,
    ]);
    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    }
    $out = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);
    return ($out === false || $code >= 400) ? null : (string) $out;
}

function http_get(string $url): ?string
{
    return http_request($url);
}

function http_post_form(string $url, array $fields): ?string
{
    return http_request($url, 'POST', http_build_query($fields), ['Content-Type: application/x-www-form-urlencoded']);
}

function http_post_json(string $url, array $payload, string $bearer): ?string
{
    return http_request($url, 'POST', json_encode($payload), ['Content-Type: application/json', 'Authorization: Bearer ' . $bearer]);
}
