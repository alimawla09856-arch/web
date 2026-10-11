<?php
/** v2: floating pill header + full-screen menu with photo previews. @var string $page */
defined('ARGENTUM') || exit;

$links = [
    'process'   => ['nav.process', 'lab-bench'],
    'hospitals' => ['nav.hospitals', 'hospital-corridor'],
    'buyers'    => ['nav.buyers', 'silver-bars-grid'],
    'about'     => ['nav.about', 'beirut'],
    'contact'   => ['nav.contact', 'collection-boxes'],
];
$brand = url('assets/brand/logo-symbols.svg');
$switchPage = $page === '404' ? 'home' : $page;
?>
<header class="v2-header" data-header>
  <div class="v2-header__bar">
    <a class="brand" href="<?= e(page_url('home')) ?>" aria-label="Argentum">
      <svg class="brand__icon" viewBox="0 0 107.8 144" aria-hidden="true"><use href="<?= e($brand) ?>#icon"></use></svg>
      <svg class="brand__word" viewBox="0 0 291.9 36.2" aria-hidden="true"><use href="<?= e($brand) ?>#wordmark"></use></svg>
    </a>
    <nav class="v2-nav" aria-label="<?= __('nav.main') ?>">
      <ul>
        <?php foreach ($links as $target => [$label]): ?>
          <li><a href="<?= e(page_url($target)) ?>"<?= $page === $target ? ' aria-current="page"' : '' ?>><?= __($label) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </nav>
    <div class="v2-header__actions">
      <div class="lang-switch" role="group" aria-label="<?= __('nav.language') ?>">
        <a href="<?= e(page_url($switchPage, 'en')) ?>" hreflang="en" lang="en"<?= current_lang() === 'en' ? ' aria-current="true"' : '' ?> data-lang="en">EN</a>
        <span aria-hidden="true">|</span>
        <a href="<?= e(page_url($switchPage, 'ar')) ?>" hreflang="ar" lang="ar"<?= current_lang() === 'ar' ? ' aria-current="true"' : '' ?> data-lang="ar">عربي</a>
      </div>
      <a class="btn btn--dark btn--sm v2-header__cta" href="<?= e(page_url('hospitals', null, 'request')) ?>"><?= __('common.request_collection') ?></a>
      <button class="v2-menu-btn" type="button" aria-expanded="false" aria-controls="v2-menu" data-v2-menu-toggle>
        <span class="v2-menu-btn__lines" aria-hidden="true"><i></i><i></i></span>
        <span class="v2-menu-btn__label" data-open="<?= __('v2.menu') ?>" data-close="<?= __('v2.close') ?>"><?= __('v2.menu') ?></span>
      </button>
    </div>
  </div>
</header>

<div class="v2-menu" id="v2-menu" data-v2-menu hidden>
  <div class="v2-menu__inner">
    <nav class="v2-menu__nav" aria-label="<?= __('nav.main') ?>">
      <ol>
        <?php $n = 0; foreach ($links as $target => [$label, $img]): $n++; ?>
          <li><a href="<?= e(page_url($target)) ?>" data-photo="<?= e($img) ?>"<?= $page === $target ? ' aria-current="page"' : '' ?>><span class="v2-menu__n">0<?= $n ?></span><?= __($label) ?></a></li>
        <?php endforeach; ?>
      </ol>
    </nav>
    <div class="v2-menu__media" aria-hidden="true">
      <?php foreach ($links as $target => [$label, $img]): ?>
        <figure data-photo-for="<?= e($img) ?>"><?= photo($img, '', '40vw', false, '') ?></figure>
      <?php endforeach; ?>
    </div>
    <div class="v2-menu__foot">
      <a class="btn btn--signal" href="<?= e(page_url('hospitals', null, 'request')) ?>"><?= __('common.request_collection') ?></a>
      <a class="btn btn--ghost-ink" href="<?= e(page_url('buyers', null, 'request')) ?>"><?= __('common.buy_silver') ?></a>
      <a class="text-link" href="<?= e(whatsapp_url()) ?>"><?= icon('whatsapp') ?><?= __('common.whatsapp_chat') ?></a>
    </div>
  </div>
</div>
