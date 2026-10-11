<?php
/** @var string $page */
defined('ARGENTUM') || exit;

$links = ['process' => 'nav.process', 'hospitals' => 'nav.hospitals', 'buyers' => 'nav.buyers', 'about' => 'nav.about', 'contact' => 'nav.contact'];
$brand = url('assets/brand/logo-symbols.svg');
$switchPage = $page === '404' ? 'home' : $page;
?>
<header class="site-header" data-header>
  <div class="wrap site-header__inner">
    <a class="brand" href="<?= e(page_url('home')) ?>" aria-label="Argentum">
      <svg class="brand__icon" viewBox="0 0 107.8 144" aria-hidden="true"><use href="<?= e($brand) ?>#icon"></use></svg>
      <svg class="brand__word" viewBox="0 0 291.9 36.2" aria-hidden="true"><use href="<?= e($brand) ?>#wordmark"></use></svg>
    </a>

    <nav class="nav" id="site-nav" aria-label="<?= __('nav.main') ?>" data-nav>
      <ul class="nav__list">
        <?php foreach ($links as $target => $label): ?>
          <li><a class="nav__link" href="<?= e(page_url($target)) ?>"<?= $page === $target ? ' aria-current="page"' : '' ?>><?= __($label) ?></a></li>
        <?php endforeach; ?>
      </ul>
      <div class="nav__sheet-actions">
        <a class="btn btn--primary" href="<?= e(page_url('hospitals', null, 'request')) ?>"><?= __('common.request_collection') ?></a>
        <a class="btn btn--outline" href="<?= e(page_url('buyers', null, 'request')) ?>"><?= __('common.buy_silver') ?></a>
      </div>
    </nav>

    <div class="site-header__actions">
      <div class="lang-switch" role="group" aria-label="<?= __('nav.language') ?>">
        <a href="<?= e(page_url($switchPage, 'en')) ?>" hreflang="en" lang="en"<?= current_lang() === 'en' ? ' aria-current="true"' : '' ?> data-lang="en">EN</a>
        <span aria-hidden="true">|</span>
        <a href="<?= e(page_url($switchPage, 'ar')) ?>" hreflang="ar" lang="ar"<?= current_lang() === 'ar' ? ' aria-current="true"' : '' ?> data-lang="ar">عربي</a>
      </div>
      <a class="btn btn--dark btn--sm site-header__cta" href="<?= e(page_url('contact')) ?>"><?= __('nav.contact_cta') ?></a>
      <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="site-nav" data-nav-toggle>
        <?= icon('menu', 'ico nav-toggle__open') ?><?= icon('x', 'ico nav-toggle__close') ?>
        <span class="sr-only" data-label-open="<?= __('nav.menu') ?>" data-label-close="<?= __('nav.close') ?>"><?= __('nav.menu') ?></span>
      </button>
    </div>
  </div>
</header>
