<?php defined('ARGENTUM') || exit; ?>
<section class="page-hero">
  <div class="wrap"><div class="page-hero__text">
    <h1><?= __('privacy.title') ?></h1>
    <p class="lead"><?= __('privacy.updated') ?></p>
  </div></div>
</section>
<section class="section" style="padding-block-start:0">
  <div class="wrap"><div class="prose">
    <?php foreach (tl('privacy.sections') as $s): ?>
      <div><h2><?= e($s['title']) ?></h2><p><?= e($s['text']) ?></p></div>
    <?php endforeach; ?>
  </div></div>
</section>
