<?php
/**
 * Builds a static design preview of the site into /preview, for Netlify
 * (or any static host). PHP features are replaced by a demo mode: forms
 * show their success message without sending, and /verify/ accepts the two
 * sample codes. The preview is marked noindex so it never competes with
 * the real site in search results.
 *
 *   php tools/build-preview.php [https://your-preview.netlify.app]               → preview/ (v1)
 *   php tools/build-preview.php --theme=v2 [https://your-preview.netlify.app]    → preview-v2/
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit;
}
$_SERVER['SCRIPT_NAME'] = '/index.php';
$args = array_slice($argv, 1);
$themeArg = 'v1';
foreach ($args as $i => $a) {
    if (str_starts_with($a, '--theme=')) {
        $themeArg = substr($a, 8);
        unset($args[$i]);
    }
}
$args = array_values($args);
define('ARGENTUM_THEME', $themeArg);
$_SERVER['HTTP_HOST'] = 'localhost';
define('ARGENTUM', true);
define('ARGENTUM_PREVIEW', true);
ob_start(); // nothing reaches stdout, so header() calls in the router stay silent
require __DIR__ . '/../app/bootstrap.php';

$GLOBALS['config']['base_url'] = rtrim($args[0] ?? 'https://argentumco.com', '/');
$GLOBALS['config']['_is_sample'] = false;
$GLOBALS['config']['captcha'] = ['provider' => '', 'site_key' => '', 'secret_key' => ''];
$GLOBALS['settings'] = load_json(CONTENT_DIR . '/site.json');
$GLOBALS['settings']['analytics'] = ['ga4_id' => '', 'gsc_verification' => ''];

$out = ROOT_DIR . '/preview' . (theme() === 'v1' ? '' : '-' . theme());
$rm = function (string $dir) use (&$rm): void {
    foreach (glob($dir . '/{,.}[!.,!..]*', GLOB_BRACE) ?: [] as $f) {
        is_dir($f) ? $rm($f) : unlink($f);
    }
    if (is_dir($dir)) {
        rmdir($dir);
    }
};
$rm($out);
mkdir($out, 0755, true);

$write = function (string $path, string $html) use ($out): void {
    $file = $out . '/' . ltrim($path, '/');
    if (str_ends_with($file, '/')) {
        $file .= 'index.html';
    }
    @mkdir(dirname($file), 0755, true);
    file_put_contents($file, $html);
    fwrite(STDERR, '  ' . substr($file, strlen($out)) . "\n");
};

foreach (LANGS as $lang) {
    foreach (PAGES as $slug => $page) {
        set_lang($lang);
        ob_start();
        show_page($page);
        $write('/' . $lang . '/' . ($slug !== '' ? $slug . '/' : ''), (string) ob_get_clean());
    }
}
set_lang('en');
ob_start();
show_page('404');
$write('/404.html', (string) ob_get_clean());

// Static assets (the v1 preview does not use the photo library or the v2 files)
$skip = theme() === 'v1' ? ['/assets/photos', '/assets/css/v2.css', '/assets/js/v2.js'] : [];
$copy = function (string $src, string $dst) use (&$copy, $skip): void {
    if (in_array(substr($src, strlen(ROOT_DIR)), $skip, true)) {
        return;
    }
    if (is_dir($src)) {
        @mkdir($dst, 0755, true);
        foreach (scandir($src) as $f) {
            if ($f !== '.' && $f !== '..') {
                $copy("$src/$f", "$dst/$f");
            }
        }
        return;
    }
    copy($src, $dst);
};
$copy(ROOT_DIR . '/assets', $out . '/assets');

file_put_contents($out . '/_redirects', "/  /en/  302\n");
file_put_contents($out . '/_headers', "/*\n  X-Robots-Tag: noindex, nofollow\n  X-Content-Type-Options: nosniff\n  Referrer-Policy: strict-origin-when-cross-origin\n");
file_put_contents($out . '/robots.txt', "User-agent: *\nDisallow: /\n");
fwrite(STDERR, 'Preview ready in ' . substr($out, strlen(ROOT_DIR)) . "\n");
