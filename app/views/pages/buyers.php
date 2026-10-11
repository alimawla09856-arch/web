<?php
defined('ARGENTUM') || exit;
$photo = media('silver');
?>
<section class="page-hero">
  <div class="wrap page-hero__grid page-hero__grid--visual">
    <div class="page-hero__text">
      <p class="label"><?= __('buyers.hero.label') ?></p>
      <h1><?= __('buyers.hero.title') ?></h1>
      <p class="lead"><?= __('buyers.hero.sub') ?></p>
      <div class="btn-row">
        <a class="btn btn--primary" href="#request"><?= __('common.buy_silver') ?></a>
        <a class="btn btn--outline" href="<?= e(whatsapp_url(t('common.whatsapp_message_silver'))) ?>"><?= icon('whatsapp') ?><?= __('common.whatsapp_chat') ?></a>
      </div>
    </div>
    <div class="page-hero__visual">
      <?php partial('doc-assay'); ?>
    </div>
  </div>
</section>

<section class="section section--tight" aria-labelledby="specs-title">
  <div class="wrap">
    <div class="section-head"><h2 id="specs-title"><?= __('buyers.specs.title') ?></h2></div>
    <dl class="specs">
      <?php foreach (tl('buyers.specs.items') as $s): ?>
        <div><dt><?= e($s['label']) ?></dt><dd><b><?= e($s['value']) ?></b><span><?= e($s['note']) ?></span></dd></div>
      <?php endforeach; ?>
    </dl>
  </div>
</section>

<section class="section section--ink" aria-labelledby="story-title">
  <div class="wrap grid-2" style="align-items:center">
    <div class="section-head" style="margin:0">
      <h2 id="story-title"><?= __('buyers.story.title') ?></h2>
      <p><?= __('buyers.story.text') ?></p>
      <p><a class="text-link" href="<?= e(page_url('about')) ?>"><?= __('routes.link') ?></a></p>
    </div>
    <?php if ($photo): ?>
      <?= media_picture($photo, 'photo', '(min-width: 56rem) 45vw, 100vw') ?>
    <?php else: ?>
    <ol class="seal-steps">
      <?php foreach (array_slice(tl('routes.argentum.nodes'), 1) as $i => $node): ?>
        <li><?= icon(['seal', 'flask', 'leaf'][$i]) ?><span><?= e($node) ?></span></li>
      <?php endforeach; ?>
    </ol>
    <?php endif; ?>
  </div>
</section>

<section class="section section--mist" id="request" aria-labelledby="request-title">
  <div class="wrap form-layout">
    <div class="form-layout__intro">
      <h2 id="request-title"><?= __('buyers.form.title') ?></h2>
      <p class="lead"><?= __('buyers.form.intro') ?></p>
      <div class="aside-card">
        <h3><?= __('buyers.form.aside_title') ?></h3>
        <p><?= __('buyers.form.aside_text') ?></p>
        <a class="btn btn--outline btn--sm" href="<?= e(whatsapp_url(t('common.whatsapp_message_silver'))) ?>"><?= icon('whatsapp') ?><?= __('common.whatsapp_chat') ?></a>
      </div>
    </div>
    <?php partial('lead-form', ['type' => 'silver', 'page' => 'buyers']); ?>
  </div>
</section>
