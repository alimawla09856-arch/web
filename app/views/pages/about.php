<?php
defined('ARGENTUM') || exit;
$brand = url('assets/brand/logo-symbols.svg');
$registration = (string) setting_l('company.registration', '');
$permits = (string) setting_l('company.permits', '');
?>
<section class="page-hero">
  <div class="wrap page-hero__grid page-hero__grid--visual">
    <div class="page-hero__text">
      <h1><?= __('about.hero.title') ?></h1>
      <p class="lead"><?= __('about.hero.sub') ?></p>
    </div>
    <figure class="element" aria-label="<?= __('about.name.text') ?>" style="margin-block:0">
      <span class="n">47</span><span class="w">107.87</span>
      <span class="sym">Ag</span>
      <span class="name">Argentum<svg viewBox="0 0 107.8 144" aria-hidden="true"><use href="<?= e($brand) ?>#icon"></use></svg></span>
    </figure>
  </div>
</section>

<section class="section section--tight" aria-label="<?= __('about.mission.label') ?> / <?= __('about.vision.label') ?>">
  <div class="wrap grid-2">
    <div class="statement"><h2 class="label"><?= __('about.mission.label') ?></h2><p><?= __('about.mission.text') ?></p></div>
    <div class="statement"><h2 class="label"><?= __('about.vision.label') ?></h2><p><?= __('about.vision.text') ?></p></div>
  </div>
</section>

<section class="section section--mist" aria-labelledby="name-title">
  <div class="wrap grid-2" style="align-items:start">
    <div class="section-head" style="margin:0">
      <h2 id="name-title"><?= __('about.name.title') ?></h2>
      <p><?= __('about.name.text') ?></p>
    </div>
    <div class="section-head" style="margin:0">
      <h2><?= __('about.confidentiality.title') ?></h2>
      <p><?= __('about.confidentiality.text') ?></p>
    </div>
  </div>
</section>

<section class="section section--ink" aria-labelledby="impact-title">
  <div class="wrap">
    <div class="section-head" style="max-width:52rem">
      <h2 id="impact-title"><?= __('about.impact.title') ?></h2>
      <p><?= __('about.impact.text') ?></p>
    </div>
    <?php partial('routes', ['showLink' => false]); ?>
  </div>
</section>

<?php partial('counters'); ?>

<section class="section section--mist" aria-labelledby="docs-title">
  <div class="wrap">
    <div class="section-head"><h2 id="docs-title"><?= __('about.documents.title') ?></h2></div>
    <ul class="issued">
      <?php foreach (tl('about.documents.items') as $i => $doc): ?>
        <li><?= icon($i === 0 ? 'certificate' : 'flask') ?><h3><?= e($doc['title']) ?></h3><p class="status"><?= icon('check') ?><?= __('about.documents.available') ?></p><p><?= e($doc['text']) ?></p></li>
      <?php endforeach; ?>
      <?php if ($registration): ?><li><?= icon('receipt') ?><h3><?= __('about.documents.registration') ?></h3><p><?= e($registration) ?></p></li><?php endif; ?>
      <?php if ($permits): ?><li><?= icon('shield') ?><h3><?= __('about.documents.permits') ?></h3><p><?= e($permits) ?></p></li><?php endif; ?>
    </ul>
    <p class="paper-trail__foot"><a class="text-link" href="<?= e(page_url('verify')) ?>"><?= icon('search') ?><?= __('paper.verify') ?></a></p>
  </div>
</section>

<?php partial('cta-band'); ?>
