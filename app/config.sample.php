<?php
/**
 * Argentum — server configuration.
 *
 * Copy this file to app/config.php on the server and fill in the values.
 * app/config.php is never committed: it holds passwords and API keys.
 * Everything that is not a secret (contact details, counters, notification
 * e-mails, analytics ID…) is edited from the admin panel instead.
 */
defined('ARGENTUM') || exit;

return [
    // Public address of the site, without a trailing slash.
    'base_url' => 'https://argentumco.com',

    // Show PHP errors in the browser. Keep false in production.
    'debug' => false,

    'timezone' => 'Asia/Beirut',

    // Admin panel login. Generate the hash from /admin/login (the page shows
    // a generator while no hash is set) or with:
    //   php -r "echo password_hash('your-password', PASSWORD_DEFAULT);"
    'admin' => [
        'user'          => 'admin',
        'password_hash' => '',
    ],

    // Outgoing e-mail. Leave 'host' empty to use the hosting's PHP mail().
    // With cPanel, create info@argentumco.com and use mail.argentumco.com.
    'smtp' => [
        'host'       => '',
        'port'       => 465,
        'encryption' => 'ssl',            // 'ssl' (465) or 'tls' (587)
        'username'   => '',
        'password'   => '',
        'from_email' => 'no-reply@argentumco.com',
        'from_name'  => 'Argentum website',
    ],

    // Spam protection. Honeypot + timing checks are always on.
    // Optionally add Google reCAPTCHA v3 or Cloudflare Turnstile keys.
    'captcha' => [
        'provider'   => '',               // '', 'recaptcha' or 'turnstile'
        'site_key'   => '',
        'secret_key' => '',
    ],

    // WhatsApp alerts for new leads (Premium package).
    //  - 'callmebot': free personal alerts, see https://www.callmebot.com/blog/free-api-whatsapp-messages/
    //  - 'cloud':     WhatsApp Business Cloud API (Meta), needs an approved template
    'whatsapp_alerts' => [
        'provider'        => '',          // '', 'callmebot' or 'cloud'
        'callmebot_key'   => '',
        'cloud_token'     => '',
        'cloud_phone_id'  => '',
        'cloud_template'  => 'new_lead',
        'cloud_language'  => 'en',
    ],
];
