<?php
/**
 * Maps URLs to pages. Public pages live under /en/ and /ar/ (separate URLs
 * per language for SEO); the admin panel lives under /admin/.
 */
defined('ARGENTUM') || exit;

/** URL slug => page template */
const PAGES = [
    ''              => 'home',
    'hospitals'     => 'hospitals',
    'silver-buyers' => 'buyers',
    'process'       => 'process',
    'impact'        => 'about',
    'contact'       => 'contact',
    'verify'        => 'verify',
    'privacy'       => 'privacy',
];

/** Pages listed in the sitemap, with their priority. */
const SITEMAP = ['home' => '1.0', 'hospitals' => '0.9', 'buyers' => '0.8', 'process' => '0.8', 'about' => '0.7', 'contact' => '0.7', 'verify' => '0.4', 'privacy' => '0.2'];

function dispatch(): void
{
    security_headers();
    $path = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
    if (base_path() !== '' && str_starts_with($path, base_path())) {
        $path = substr($path, strlen(base_path())) ?: '/';
    }
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if ($path === '/' || $path === '/index.php') {
        redirect(page_url('home', preferred_lang()), 302);
    }
    if ($path === '/sitemap.xml') {
        sitemap();
    }
    if ($path === '/robots.txt') {
        robots();
    }
    if ($path === '/api/lead' && $method === 'POST') {
        api_lead();
    }
    if ($path === '/api/verify') {
        api_verify();
    }
    if ($path === '/admin' || str_starts_with($path, '/admin/')) {
        admin_dispatch(substr($path, 6) ?: '/', $method);
        return;
    }

    if (preg_match('#^/(en|ar)(?:/([a-z-]+))?/?$#', $path, $m)) {
        $slug = $m[2] ?? '';
        if (!str_ends_with($path, '/')) {
            redirect(url($path . '/'), 301);
        }
        set_lang($m[1]);
        if (array_key_exists($slug, PAGES)) {
            remember_lang($m[1]);
            show_page(PAGES[$slug]);
            return;
        }
    } else {
        // Unknown URL outside a language folder: answer in the visitor's language
        set_lang(preferred_lang());
    }
    not_found();
}

/** Last language the visitor chose, otherwise English (the default). */
function preferred_lang(): string
{
    $cookie = $_COOKIE['lang'] ?? '';
    return in_array($cookie, LANGS, true) ? $cookie : 'en';
}

function remember_lang(string $lang): void
{
    if (($_COOKIE['lang'] ?? '') !== $lang) {
        setcookie('lang', $lang, [
            'expires'  => time() + 365 * 86400,
            'path'     => base_path() . '/',
            'secure'   => is_https(),
            'httponly' => false,
            'samesite' => 'Lax',
        ]);
    }
}

function show_page(string $page, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: text/html; charset=utf-8');
    $content = render('pages/' . $page, ['page' => $page]);
    echo render('layout', ['page' => $page, 'content' => $content]);
}

function not_found(): void
{
    show_page('404', 404);
}

function sitemap(): never
{
    header('Content-Type: application/xml; charset=utf-8');
    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
        . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n";
    foreach (SITEMAP as $page => $priority) {
        foreach (LANGS as $lang) {
            $xml .= '  <url><loc>' . e(absolute_url(page_url($page, $lang))) . '</loc>';
            foreach (LANGS as $alt) {
                $xml .= '<xhtml:link rel="alternate" hreflang="' . $alt . '" href="' . e(absolute_url(page_url($page, $alt))) . '"/>';
            }
            $xml .= '<xhtml:link rel="alternate" hreflang="x-default" href="' . e(absolute_url(page_url($page, 'en'))) . '"/>';
            $xml .= '<priority>' . $priority . '</priority></url>' . "\n";
        }
    }
    echo $xml . '</urlset>' . "\n";
    exit;
}

function robots(): never
{
    header('Content-Type: text/plain; charset=utf-8');
    echo "User-agent: *\nDisallow: /admin/\nDisallow: /api/\n\nSitemap: " . absolute_url('sitemap.xml') . "\n";
    exit;
}
