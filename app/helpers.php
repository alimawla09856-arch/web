<?php
/**
 * Small helpers shared by the site, the API and the admin panel.
 */
defined('ARGENTUM') || exit;

/** Escapes text for HTML output. */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Folder the site is installed in ('' at the domain root, '/site' in a subfolder). */
function base_path(): string
{
    static $base = null;
    if ($base === null) {
        $script = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
        $base = rtrim($script === '/' || $script === '.' ? '' : $script, '/');
    }
    return $base;
}

/** Root-relative URL inside the site. */
function url(string $path = '/'): string
{
    return base_path() . '/' . ltrim($path, '/');
}

/** Absolute URL, used for canonical links, sitemaps and e-mails. */
function absolute_url(string $path = '/'): string
{
    $configured = rtrim((string) config('base_url', ''), '/');
    if ($configured === '' || config('_is_sample')) {
        $scheme = is_https() ? 'https' : 'http';
        $configured = $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . base_path();
    }
    return $configured . '/' . ltrim($path, '/');
}

/** Versioned asset URL so browsers can cache files for a long time. */
function asset(string $path): string
{
    $file = ROOT_DIR . '/assets/' . ltrim($path, '/');
    $v = is_file($file) ? substr(md5((string) filemtime($file)), 0, 8) : ASSET_VERSION;
    return url('assets/' . ltrim($path, '/')) . '?v=' . $v;
}

/** URL of a page in a given language: page_url('hospitals', 'ar') → /ar/hospitals/ */
function page_url(string $page, ?string $lang = null, string $hash = ''): string
{
    $lang ??= current_lang();
    $slug = array_search($page, PAGES, true);
    $path = '/' . $lang . '/' . ($slug ? $slug . '/' : '');
    return url($path) . ($hash !== '' ? '#' . $hash : '');
}

function is_https(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
        || (($_SERVER['SERVER_PORT'] ?? '') === '443');
}

function redirect(string $location, int $status = 302): never
{
    header('Location: ' . $location, true, $status);
    exit;
}

function json_response(array $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/** Renders a PHP view with local variables and returns the HTML. */
function render(string $view, array $vars = []): string
{
    extract($vars, EXTR_SKIP);
    ob_start();
    require APP_DIR . '/views/' . $view . '.php';
    return (string) ob_get_clean();
}

/** Includes a partial straight into the output. */
function partial(string $name, array $vars = []): void
{
    echo render('partials/' . $name, $vars);
}

/** Inline icon from the sprite at assets/img/icons.svg. */
function icon(string $name, string $class = 'ico'): string
{
    return '<svg class="' . e($class) . '" aria-hidden="true" focusable="false"><use href="'
        . e(url('assets/img/icons.svg')) . '#' . e($name) . '"></use></svg>';
}

/** Turns blank-line separated text into paragraphs. */
function paragraphs(string $text, string $class = ''): string
{
    $out = '';
    foreach (preg_split('/\n\s*\n/', trim($text)) ?: [] as $p) {
        $out .= '<p' . ($class ? ' class="' . e($class) . '"' : '') . '>' . nl2br(e(trim($p)), false) . '</p>';
    }
    return $out;
}

/** Anonymised visitor fingerprint for rate limiting (no raw IPs are stored). */
function client_hash(): string
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    return hash('sha256', $ip . '|' . (config('admin.password_hash') ?: 'argentum'));
}

/** Digits only, for wa.me and tel: links. */
function digits(string $phone): string
{
    return preg_replace('/\D+/', '', $phone) ?? '';
}

/** Click-to-chat link with the pre-filled message in the visitor's language. */
function whatsapp_url(?string $message = null): string
{
    $number = digits((string) setting('contact.whatsapp', ''));
    $message ??= t('common.whatsapp_message');
    return $number !== ''
        ? 'https://wa.me/' . $number . '?text=' . rawurlencode($message)
        : page_url('contact');
}

function security_headers(): void
{
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('X-Frame-Options: SAMEORIGIN');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
}
