<?php
/** Floating click-to-chat button, on every page. @var string $page */
defined('ARGENTUM') || exit;

$message = $page === 'buyers' ? t('common.whatsapp_message_silver') : t('common.whatsapp_message');
$external = digits((string) setting('contact.whatsapp', '')) !== '';
?>
<a class="wa-fab" href="<?= e(whatsapp_url($message)) ?>"<?= $external ? ' target="_blank" rel="noopener"' : '' ?> data-track="whatsapp">
  <?= icon('whatsapp', 'ico wa-fab__icon') ?>
  <span class="wa-fab__label"><?= __('common.whatsapp_chat') ?></span>
</a>
