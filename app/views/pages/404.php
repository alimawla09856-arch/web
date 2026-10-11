<?php defined('ARGENTUM') || exit; ?>
<section class="page-hero section">
  <div class="wrap"><div class="page-hero__text">
    <p class="label">404</p>
    <h1><?= __('notfound.title') ?></h1>
    <p class="lead"><?= __('notfound.text') ?></p>
    <div class="btn-row">
      <a class="btn btn--primary" href="<?= e(page_url('home')) ?>"><?= __('common.back_home') ?></a>
      <a class="btn btn--outline" href="<?= e(page_url('hospitals')) ?>"><?= __('nav.hospitals') ?></a>
      <a class="btn btn--outline" href="<?= e(page_url('contact')) ?>"><?= __('nav.contact') ?></a>
    </div>
  </div></div>
</section>
