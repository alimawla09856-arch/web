<?php
/**
 * Site copy (content/en.json, content/ar.json) and editable settings
 * (content/site.json). Both are edited from the admin panel.
 */
defined('ARGENTUM') || exit;

function current_lang(): string
{
    return $GLOBALS['lang'] ?? 'en';
}

function set_lang(string $lang): void
{
    $GLOBALS['lang'] = in_array($lang, LANGS, true) ? $lang : 'en';
}

function is_rtl(?string $lang = null): bool
{
    return ($lang ?? current_lang()) === 'ar';
}

function other_lang(): string
{
    return current_lang() === 'ar' ? 'en' : 'ar';
}

function load_json(string $file): array
{
    if (!is_file($file)) {
        return [];
    }
    $data = json_decode((string) file_get_contents($file), true);
    return is_array($data) ? $data : [];
}

/** Writes JSON atomically so a failed save never leaves a half-written file. */
function save_json(string $file, array $data): bool
{
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $tmp = $file . '.' . bin2hex(random_bytes(4)) . '.tmp';
    if ($json === false || file_put_contents($tmp, $json . "\n", LOCK_EX) === false) {
        return false;
    }
    return rename($tmp, $file);
}

function strings(?string $lang = null): array
{
    static $cache = [];
    $lang ??= current_lang();
    return $cache[$lang] ??= load_json(CONTENT_DIR . '/' . $lang . '.json');
}

/** Raw translated value (string or list) at a dot path, e.g. 'home.hero.title'. */
function tv(string $key, ?string $lang = null): mixed
{
    $value = strings($lang);
    foreach (explode('.', $key) as $part) {
        if (!is_array($value) || !array_key_exists($part, $value)) {
            return null;
        }
        $value = $value[$part];
    }
    return $value;
}

/** Translated text, unescaped. {placeholders} are replaced from $vars. */
function t(string $key, array $vars = [], ?string $lang = null): string
{
    $value = tv($key, $lang);
    if (!is_string($value)) {
        return config('debug') ? '[' . $key . ']' : '';
    }
    foreach ($vars as $k => $v) {
        $value = str_replace('{' . $k . '}', (string) $v, $value);
    }
    return $value;
}

/** Translated text, escaped for HTML. */
function __(string $key, array $vars = []): string
{
    return e(t($key, $vars));
}

/** Translated list (steps, FAQ items…). */
function tl(string $key): array
{
    $value = tv($key);
    return is_array($value) ? $value : [];
}

function settings(): array
{
    return $GLOBALS['settings'] ??= load_json(CONTENT_DIR . '/site.json');
}

function setting(string $key, mixed $default = null): mixed
{
    $value = settings();
    foreach (explode('.', $key) as $part) {
        if (!is_array($value) || !array_key_exists($part, $value)) {
            return $default;
        }
        $value = $value[$part];
    }
    return $value === '' || $value === null ? $default : $value;
}

/** A setting that exists per language, e.g. contact.address_en / contact.address_ar */
function setting_l(string $key, mixed $default = null): mixed
{
    return setting($key . '_' . current_lang(), $default);
}

/**
 * Photo uploaded from the admin panel for a named slot, or null when the
 * slot still uses its built-in illustration.
 */
function media(string $slot): ?array
{
    $item = setting('media.' . $slot);
    if (!is_array($item) || empty($item['file']) || !is_file(UPLOADS_DIR . '/' . $item['file'])) {
        return null;
    }
    return [
        'src'  => url('uploads/' . $item['file']),
        'webp' => !empty($item['webp']) && is_file(UPLOADS_DIR . '/' . $item['webp']) ? url('uploads/' . $item['webp']) : null,
        'w'    => (int) ($item['w'] ?? 0),
        'h'    => (int) ($item['h'] ?? 0),
        'alt'  => (string) ($item['alt_' . current_lang()] ?? ''),
    ];
}

/** <picture> markup for an uploaded photo. */
function media_picture(array $m, string $class = '', string $sizes = '100vw', bool $eager = false): string
{
    $img = '<img src="' . e($m['src']) . '" alt="' . e($m['alt']) . '"'
        . ($m['w'] ? ' width="' . $m['w'] . '" height="' . $m['h'] . '"' : '')
        . ($eager ? ' fetchpriority="high"' : ' loading="lazy"') . ' decoding="async">';
    $source = $m['webp'] ? '<source type="image/webp" srcset="' . e($m['webp']) . '">' : '';
    return '<picture' . ($class ? ' class="' . e($class) . '"' : '') . '>' . $source . $img . '</picture>';
}
