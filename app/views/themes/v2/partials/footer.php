<?php
/** v2 footer: light, with a silver wordmark across the full width. */
defined('ARGENTUM') || exit;

$brand = url('assets/brand/logo-symbols.svg');
$email = (string) setting('contact.email', '');
$phone = (string) setting('contact.phone', '');
$hours = (string) setting_l('contact.hours', '');
?>
<footer class="v2-footer">
  <div class="wrap v2-footer__top">
    <p class="v2-footer__slogan" data-split><?= __('meta.slogan') ?></p>
    <div class="v2-footer__cols">
      <ul>
        <li><a href="<?= e(page_url('hospitals', null, 'request')) ?>"><?= __('common.request_collection') ?></a></li>
        <li><a href="<?= e(page_url('buyers', null, 'request')) ?>"><?= __('common.buy_silver') ?></a></li>
        <li><a href="<?= e(page_url('verify')) ?>"><?= __('footer.verify') ?></a></li>
      </ul>
      <ul>
        <li><a href="<?= e(page_url('process')) ?>"><?= __('nav.process') ?></a></li>
        <li><a href="<?= e(page_url('about')) ?>"><?= __('nav.about') ?></a></li>
        <li><a href="<?= e(page_url('contact')) ?>"><?= __('nav.contact') ?></a></li>
      </ul>
      <ul>
        <li><a href="<?= e(whatsapp_url()) ?>"><?= __('common.whatsapp_chat') ?></a></li>
        <?php if ($phone): ?><li><a href="tel:+<?= e(digits($phone)) ?>" dir="ltr"><?= e($phone) ?></a></li><?php endif; ?>
        <?php if ($email): ?><li><a href="mailto:<?= e($email) ?>"><?= e($email) ?></a></li><?php endif; ?>
        <?php if ($hours): ?><li><span><?= e($hours) ?></span></li><?php endif; ?>
      </ul>
    </div>
  </div>
  <div class="wrap v2-footer__mark" aria-hidden="true"><svg viewBox="0 0 291.9 36.2"><use href="<?= e($brand) ?>#wordmark"></use></svg></div>
  <div class="wrap v2-footer__base">
    <p><?= __('footer.about') ?></p>
    <p><?= __('footer.rights', ['year' => date('Y')]) ?> · <a href="<?= e(page_url('privacy')) ?>"><?= __('footer.privacy') ?></a> · <a href="<?= e(page_url('home', other_lang())) ?>" hreflang="<?= other_lang() ?>" lang="<?= other_lang() ?>"><?= other_lang() === 'ar' ? 'عربي' : 'English' ?></a></p>
  </div>
</footer>
