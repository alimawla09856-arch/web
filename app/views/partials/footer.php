<?php
defined('ARGENTUM') || exit;

$brand = url('assets/brand/logo-symbols.svg');
$email = (string) setting('contact.email', '');
$phone = (string) setting('contact.phone', '');
$address = (string) setting_l('contact.address', '');
$hours = (string) setting_l('contact.hours', '');
$socials = array_filter(['linkedin' => setting('contact.linkedin'), 'facebook' => setting('contact.facebook'), 'instagram' => setting('contact.instagram')]);
?>
<footer class="site-footer">
  <div class="wrap site-footer__grid">
    <div class="site-footer__brand">
      <a href="<?= e(page_url('home')) ?>" class="footer-logo" aria-label="Argentum">
        <svg class="footer-logo__icon" viewBox="0 0 107.8 144" aria-hidden="true"><use href="<?= e($brand) ?>#icon"></use></svg>
        <svg class="footer-logo__word" viewBox="0 0 291.9 36.2" aria-hidden="true"><use href="<?= e($brand) ?>#wordmark"></use></svg>
      </a>
      <p class="site-footer__slogan"><?= __('meta.slogan') ?></p>
      <p class="site-footer__about"><?= __('footer.about') ?></p>
    </div>

    <nav class="site-footer__col" aria-label="<?= __('footer.work') ?>">
      <h2 class="site-footer__title"><?= __('footer.work') ?></h2>
      <ul>
        <li><a href="<?= e(page_url('hospitals', null, 'request')) ?>"><?= __('common.request_collection') ?></a></li>
        <li><a href="<?= e(page_url('buyers', null, 'request')) ?>"><?= __('common.buy_silver') ?></a></li>
        <li><a href="<?= e(page_url('verify')) ?>"><?= __('footer.verify') ?></a></li>
      </ul>
    </nav>

    <nav class="site-footer__col" aria-label="<?= __('footer.company') ?>">
      <h2 class="site-footer__title"><?= __('footer.company') ?></h2>
      <ul>
        <li><a href="<?= e(page_url('process')) ?>"><?= __('nav.process') ?></a></li>
        <li><a href="<?= e(page_url('about')) ?>"><?= __('nav.about') ?></a></li>
        <li><a href="<?= e(page_url('contact')) ?>"><?= __('nav.contact') ?></a></li>
      </ul>
    </nav>

    <div class="site-footer__col">
      <h2 class="site-footer__title"><?= __('footer.contact') ?></h2>
      <ul class="site-footer__contact">
        <li><a href="<?= e(whatsapp_url()) ?>"><?= icon('whatsapp') ?><?= __('common.whatsapp_chat') ?></a></li>
        <?php if ($phone): ?><li><a href="tel:+<?= e(digits($phone)) ?>" dir="ltr"><?= icon('phone') ?><?= e($phone) ?></a></li><?php endif; ?>
        <?php if ($email): ?><li><a href="mailto:<?= e($email) ?>"><?= icon('mail') ?><?= e($email) ?></a></li><?php endif; ?>
        <?php if ($address): ?><li><?= icon('pin') ?><span><?= e($address) ?></span></li><?php endif; ?>
        <?php if ($hours): ?><li><?= icon('clock') ?><span><?= e($hours) ?></span></li><?php endif; ?>
      </ul>
      <?php if ($socials): ?>
        <ul class="socials">
          <?php foreach ($socials as $name => $href): ?>
            <li><a href="<?= e($href) ?>" rel="noopener" target="_blank" aria-label="<?= e(ucfirst($name)) ?>"><?= icon($name) ?></a></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>
  </div>

  <div class="wrap site-footer__base">
    <p><?= __('footer.rights', ['year' => date('Y')]) ?></p>
    <p class="site-footer__legal">
      <a href="<?= e(page_url('privacy')) ?>"><?= __('footer.privacy') ?></a>
      <a href="<?= e(page_url('home', other_lang())) ?>" hreflang="<?= other_lang() ?>" lang="<?= other_lang() ?>"><?= other_lang() === 'ar' ? 'عربي' : 'English' ?></a>
    </p>
  </div>
</footer>
