<?php
defined('ARGENTUM') || exit;
$benefitIcons = ['coin', 'shield', 'certificate', 'calendar'];
$sealIcons = ['seal', 'shield', 'certificate'];
$photo = media('film');
?>
<section class="page-hero">
  <div class="wrap page-hero__grid page-hero__grid--visual">
    <div class="page-hero__text">
      <p class="label"><?= __('hospitals.hero.label') ?></p>
      <h1><?= __('hospitals.hero.title') ?></h1>
      <p class="lead"><?= __('hospitals.hero.sub') ?></p>
      <div class="btn-row">
        <a class="btn btn--primary" href="#request"><?= __('common.request_collection') ?></a>
        <a class="btn btn--outline" href="<?= e(whatsapp_url()) ?>"><?= icon('whatsapp') ?><?= __('common.whatsapp_chat') ?></a>
      </div>
    </div>
    <div class="page-hero__visual">
      <?php partial('doc-certificate'); ?>
    </div>
  </div>
</section>

<section class="section section--tight" aria-labelledby="benefits-title">
  <div class="wrap">
    <div class="section-head"><h2 id="benefits-title"><?= __('hospitals.benefits.title') ?></h2></div>
    <ul class="benefits">
      <?php foreach (tl('hospitals.benefits.items') as $i => $b): ?>
        <li class="benefit"><?= icon($benefitIcons[$i] ?? 'check') ?><h3><?= e($b['title']) ?></h3><p><?= e($b['text']) ?></p></li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>

<section class="section section--mist" aria-labelledby="collect-title">
  <div class="wrap">
    <div class="<?= $photo ? 'grid-2' : '' ?>" style="align-items:center">
    <div>
    <div class="section-head"><h2 id="collect-title"><?= __('hospitals.collect.title') ?></h2></div>
    <div class="collect">
      <div class="collect__group">
        <h3><?= __('hospitals.collect.from_label') ?></h3>
        <ul class="tags"><?php foreach (tl('hospitals.collect.from') as $x): ?><li><?= e($x) ?></li><?php endforeach; ?></ul>
      </div>
      <div class="collect__group">
        <h3><?= __('hospitals.collect.film_label') ?></h3>
        <ul class="tags"><?php foreach (tl('hospitals.collect.film') as $x): ?><li><?= e($x) ?></li><?php endforeach; ?></ul>
      </div>
    </div>
    <p class="note" style="margin-block-start:2rem"><?= icon('alert') ?><span><?= __('hospitals.collect.note') ?></span></p>
    </div>
    <?php if ($photo): ?><?= media_picture($photo, 'photo', '(min-width: 56rem) 45vw, 100vw') ?><?php endif; ?>
    </div>
  </div>
</section>

<section class="section section--ink" aria-labelledby="conf-title">
  <div class="wrap grid-2" style="align-items:center">
    <div class="section-head" style="margin:0">
      <h2 id="conf-title"><?= __('hospitals.confidentiality.title') ?></h2>
      <p><?= __('hospitals.confidentiality.text') ?></p>
      <p><a class="text-link" href="<?= e(page_url('verify')) ?>"><?= icon('search') ?><?= __('paper.verify') ?></a></p>
    </div>
    <ol class="seal-steps">
      <?php foreach (tl('hospitals.confidentiality.steps') as $i => $s): ?>
        <li><?= icon($sealIcons[$i]) ?><span><?= e($s) ?></span></li>
      <?php endforeach; ?>
    </ol>
  </div>
</section>

<section class="section" aria-labelledby="faq-title">
  <div class="wrap grid-2">
    <div class="section-head"><h2 id="faq-title"><?= __('hospitals.faq.title') ?></h2></div>
    <div class="faq">
      <?php foreach (tl('hospitals.faq.items') as $i => $item): ?>
        <details<?= $i === 0 ? ' open' : '' ?>>
          <summary><?= e($item['q']) ?><?= icon('plus') ?></summary>
          <p><?= e($item['a']) ?></p>
        </details>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section section--mist" id="request" aria-labelledby="request-title">
  <div class="wrap form-layout">
    <div class="form-layout__intro">
      <h2 id="request-title"><?= __('hospitals.form.title') ?></h2>
      <p class="lead"><?= __('hospitals.form.intro') ?></p>
      <div class="aside-card">
        <h3><?= __('hospitals.form.aside_title') ?></h3>
        <p><?= __('hospitals.form.aside_text') ?></p>
        <a class="btn btn--outline btn--sm" href="<?= e(whatsapp_url()) ?>"><?= icon('whatsapp') ?><?= __('common.whatsapp_chat') ?></a>
      </div>
    </div>
    <?php partial('lead-form', ['type' => 'collection', 'page' => 'hospitals']); ?>
  </div>
</section>
