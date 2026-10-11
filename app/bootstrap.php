<?php
/**
 * Loads configuration and shared code. Included once by index.php.
 */
defined('ARGENTUM') || exit;

const ROOT_DIR    = __DIR__ . '/..';
const APP_DIR     = __DIR__;
const CONTENT_DIR = ROOT_DIR . '/content';
const STORAGE_DIR = ROOT_DIR . '/storage';
const UPLOADS_DIR = ROOT_DIR . '/uploads';
const LANGS       = ['en', 'ar'];
const ASSET_VERSION = '1.0.0';

$configFile = is_file(APP_DIR . '/config.php') ? APP_DIR . '/config.php' : APP_DIR . '/config.sample.php';
$GLOBALS['config'] = require $configFile;
$GLOBALS['config']['_is_sample'] = !is_file(APP_DIR . '/config.php');

date_default_timezone_set(config('timezone', 'Asia/Beirut'));
mb_internal_encoding('UTF-8');

if (config('debug')) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
}

require APP_DIR . '/helpers.php';
require APP_DIR . '/content.php';
require APP_DIR . '/db.php';
require APP_DIR . '/forms.php';
require APP_DIR . '/notify.php';
require APP_DIR . '/documents.php';
require APP_DIR . '/router.php';
require APP_DIR . '/admin.php';

/** Reads a value from app/config.php using dot notation. */
function config(string $key, mixed $default = null): mixed
{
    $value = $GLOBALS['config'];
    foreach (explode('.', $key) as $part) {
        if (!is_array($value) || !array_key_exists($part, $value)) {
            return $default;
        }
        $value = $value[$part];
    }
    return $value;
}
