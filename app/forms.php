<?php
/**
 * The two lead forms: "Request a collection" (hospitals & suppliers) and
 * "Buy recovered silver" (silver shops & refiners).
 *
 * The schema below drives both the HTML rendering and server validation, so
 * a field only has to be declared once. Labels live in content/{lang}.json
 * under forms.fields.<name>.
 */
defined('ARGENTUM') || exit;

const LEAD_TYPES = ['collection', 'silver'];
const LEAD_STATUSES = ['new', 'contacted', 'scheduled', 'won', 'closed'];

function form_schema(string $type): array
{
    $regions = ['beirut', 'mount-lebanon', 'north', 'south', 'bekaa', 'nabatieh', 'akkar', 'baalbek-hermel'];
    return match ($type) {
        'collection' => [
            'organisation'     => ['type' => 'text', 'required' => true, 'max' => 160, 'step' => 1, 'autocomplete' => 'organization'],
            'org_type'         => ['type' => 'select', 'required' => true, 'options' => ['hospital', 'imaging', 'clinic', 'dental', 'other'], 'step' => 1],
            'org_type_other'   => ['type' => 'text', 'max' => 120, 'step' => 1, 'show_if' => ['org_type', 'other'], 'required_if' => true],
            'region'           => ['type' => 'select', 'required' => true, 'options' => $regions, 'step' => 1],
            'contact_person'   => ['type' => 'text', 'required' => true, 'max' => 120, 'step' => 2, 'autocomplete' => 'name'],
            'job_title'        => ['type' => 'text', 'max' => 120, 'step' => 2, 'autocomplete' => 'organization-title'],
            'email'            => ['type' => 'email', 'required' => true, 'max' => 160, 'step' => 2, 'autocomplete' => 'email'],
            'phone'            => ['type' => 'tel', 'required' => true, 'max' => 40, 'step' => 2, 'autocomplete' => 'tel'],
            'quantity'         => ['type' => 'choice', 'required' => true, 'options' => ['lt50', '50-200', '200-500', '500plus', 'unsure'], 'step' => 3],
            'sites'            => ['type' => 'select', 'options' => ['one', 'two-three', 'four-plus'], 'step' => 3, 'show_if' => ['quantity', '500plus']],
            'film_types'       => ['type' => 'checkboxes', 'options' => ['archived', 'ongoing', 'unsure'], 'step' => 3],
            'certificate'      => ['type' => 'radio', 'options' => ['yes', 'no'], 'step' => 3],
            'certificate_name' => ['type' => 'text', 'max' => 160, 'step' => 3, 'show_if' => ['certificate', 'yes']],
            'preferred_date'   => ['type' => 'date', 'step' => 3],
            'notes'            => ['type' => 'textarea', 'max' => 2000, 'step' => 3],
        ],
        'silver' => [
            'company'        => ['type' => 'text', 'required' => true, 'max' => 160, 'autocomplete' => 'organization'],
            'contact_person' => ['type' => 'text', 'required' => true, 'max' => 120, 'autocomplete' => 'name'],
            'email'          => ['type' => 'email', 'required' => true, 'max' => 160, 'autocomplete' => 'email'],
            'phone'          => ['type' => 'tel', 'required' => true, 'max' => 40, 'autocomplete' => 'tel'],
            'quantity_kg'    => ['type' => 'number', 'min' => 0, 'max' => 100000, 'step_value' => '0.1'],
            'frequency'      => ['type' => 'choice', 'options' => ['one-time', 'monthly', 'quarterly']],
            'notes'          => ['type' => 'textarea', 'max' => 2000],
        ],
        default => [],
    };
}

/** Is a conditional field visible for the submitted values? */
function field_visible(array $field, array $input): bool
{
    if (empty($field['show_if'])) {
        return true;
    }
    [$other, $value] = $field['show_if'];
    $given = $input[$other] ?? '';
    return is_array($given) ? in_array($value, $given, true) : $given === $value;
}

/**
 * Validates submitted values against the schema.
 * Returns [clean values, errors keyed by field => message key].
 */
function validate_lead(string $type, array $input): array
{
    $clean = [];
    $errors = [];
    foreach (form_schema($type) as $name => $f) {
        if (!field_visible($f, $input)) {
            continue;
        }
        $raw = $input[$name] ?? ($f['type'] === 'checkboxes' ? [] : '');
        $required = !empty($f['required']) || !empty($f['required_if']);

        if ($f['type'] === 'checkboxes') {
            $values = array_values(array_intersect((array) $raw, $f['options']));
            if ($required && !$values) {
                $errors[$name] = 'required';
            }
            $clean[$name] = $values;
            continue;
        }

        $value = trim(is_string($raw) ? $raw : '');
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $value) ?? '';
        if ($value === '') {
            if ($required) {
                $errors[$name] = 'required';
            }
            continue;
        }
        if (isset($f['max']) && $f['type'] !== 'number' && mb_strlen($value) > $f['max']) {
            $errors[$name] = 'too_long';
            continue;
        }
        switch ($f['type']) {
            case 'email':
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$name] = 'email';
                }
                break;
            case 'tel':
                $n = strlen(digits($value));
                if ($n < 7 || $n > 15 || !preg_match('/^[+\d\s().-]+$/', $value)) {
                    $errors[$name] = 'phone';
                }
                break;
            case 'select':
            case 'radio':
            case 'choice':
                if (!in_array($value, $f['options'], true)) {
                    $errors[$name] = 'option';
                }
                break;
            case 'number':
                if (!is_numeric($value) || $value < $f['min'] || $value > $f['max']) {
                    $errors[$name] = 'number';
                }
                break;
            case 'date':
                $d = DateTime::createFromFormat('!Y-m-d', $value);
                if (!$d || $d->format('Y-m-d') !== $value) {
                    $errors[$name] = 'date';
                } elseif ($value < date('Y-m-d')) {
                    $errors[$name] = 'date_past';
                }
                break;
        }
        $clean[$name] = $value;
    }
    return [$clean, $errors];
}

/** Leads that deserve a faster call back are flagged for the team. */
function lead_priority(string $type, array $data): string
{
    if ($type === 'collection') {
        return ($data['quantity'] ?? '') === '500plus' ? 'high' : 'normal';
    }
    return in_array($data['frequency'] ?? '', ['monthly', 'quarterly'], true) ? 'high' : 'normal';
}

/* ---------- Spam protection ---------- */

function app_secret(): string
{
    $file = STORAGE_DIR . '/app.key';
    if (!is_file($file)) {
        file_put_contents($file, bin2hex(random_bytes(32)), LOCK_EX);
    }
    return trim((string) file_get_contents($file));
}

/** Signed timestamp placed in every form; a submission sent within 3 s is a bot. */
function form_token(): string
{
    $t = (string) time();
    return $t . '.' . substr(hash_hmac('sha256', $t, app_secret()), 0, 20);
}

function form_token_valid(string $token): bool
{
    [$t, $sig] = array_pad(explode('.', $token, 2), 2, '');
    if (!ctype_digit($t) || !hash_equals(substr(hash_hmac('sha256', $t, app_secret()), 0, 20), $sig)) {
        return false;
    }
    $age = time() - (int) $t;
    return $age >= 3 && $age <= 86400 * 2;
}

function captcha_valid(string $response): bool
{
    $provider = config('captcha.provider');
    if (!$provider || !config('captcha.secret_key')) {
        return true;
    }
    $endpoint = $provider === 'turnstile'
        ? 'https://challenges.cloudflare.com/turnstile/v0/siteverify'
        : 'https://www.google.com/recaptcha/api/siteverify';
    $result = http_post_form($endpoint, ['secret' => config('captcha.secret_key'), 'response' => $response]);
    $json = json_decode($result ?? '', true);
    if (!is_array($json) || empty($json['success'])) {
        return false;
    }
    return $provider !== 'recaptcha' || ($json['score'] ?? 1) >= 0.5;
}

/* ---------- POST /api/lead ---------- */

function api_lead(): never
{
    $wantsJson = str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
    $type = (string) ($_POST['form'] ?? '');
    set_lang((string) ($_POST['lang'] ?? 'en'));

    if (!in_array($type, LEAD_TYPES, true)) {
        json_response(['ok' => false, 'message' => t('forms.messages.error')], 400);
    }
    // Bots fill the hidden field or post instantly: pretend success, store nothing.
    if (($_POST['website'] ?? '') !== '' || !form_token_valid((string) ($_POST['_ts'] ?? ''))) {
        lead_reply($wantsJson, $type, true);
    }
    if (!rate_limit('lead:' . client_hash(), 6, 600)) {
        json_response(['ok' => false, 'message' => t('forms.messages.rate_limited')], 429);
    }
    if (!captcha_valid((string) ($_POST['captcha'] ?? ''))) {
        json_response(['ok' => false, 'message' => t('forms.messages.captcha')], 422);
    }

    [$data, $errors] = validate_lead($type, $_POST);
    if ($errors) {
        $messages = [];
        foreach ($errors as $field => $code) {
            $messages[$field] = t('forms.errors.' . $code);
        }
        if (!$wantsJson) {
            lead_reply(false, $type, false);
        }
        json_response(['ok' => false, 'errors' => $messages, 'message' => t('forms.messages.fix_errors')], 422);
    }

    $lead = [
        'type'           => $type,
        'status'         => 'new',
        'priority'       => lead_priority($type, $data),
        'lang'           => current_lang(),
        'organisation'   => $data['organisation'] ?? $data['company'] ?? '',
        'contact_person' => $data['contact_person'] ?? '',
        'email'          => $data['email'] ?? '',
        'phone'          => $data['phone'] ?? '',
        'region'         => $data['region'] ?? '',
        'quantity'       => $data['quantity'] ?? ($data['quantity_kg'] ?? ''),
        'data'           => json_encode($data, JSON_UNESCAPED_UNICODE),
        'source'         => mb_substr((string) ($_POST['source'] ?? ''), 0, 200),
        'created_at'     => now(),
        'updated_at'     => now(),
    ];
    $cols = array_keys($lead);
    $stmt = db()->prepare('INSERT INTO leads (' . implode(',', $cols) . ') VALUES (' . rtrim(str_repeat('?,', count($cols)), ',') . ')');
    $stmt->execute(array_values($lead));
    $lead['id'] = (int) db()->lastInsertId();

    // Answer the visitor first, then send e-mail / WhatsApp alerts.
    if ($wantsJson) {
        http_response_code(200);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => true, 'message' => t('forms.' . $type . '.success_text')], JSON_UNESCAPED_UNICODE);
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }
        notify_lead($lead, $data);
        exit;
    }
    notify_lead($lead, $data);
    lead_reply(false, $type, true);
}

/** Response for bots and for browsers without JavaScript. */
function lead_reply(bool $json, string $type, bool $ok): never
{
    if ($json) {
        json_response(['ok' => true, 'message' => t('forms.' . $type . '.success_text')]);
    }
    $page = $type === 'silver' ? 'buyers' : 'hospitals';
    redirect(page_url($page) . '?' . ($ok ? 'sent=' . $type : 'error=1') . '#request');
}
