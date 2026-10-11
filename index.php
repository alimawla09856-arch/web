<?php
/**
 * Argentum website — front controller.
 * Every request that is not a real file is routed from here (see .htaccess).
 *
 * Local preview: php -S localhost:8000 index.php
 */
declare(strict_types=1);

if (PHP_SAPI === 'cli-server') {
    $file = __DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $private = preg_match('#^/(app|content|storage|docs)(/|$)#', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    if (!$private && is_file($file) && !str_ends_with($file, '.php')) {
        return false;
    }
}

define('ARGENTUM', true);
require __DIR__ . '/app/bootstrap.php';

dispatch();
