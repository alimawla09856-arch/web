<?php
defined('ARGENTUM') || exit;
$brand = url('assets/brand/logo-symbols.svg');
$title = t('home.hero.title');
// Two sentences in the headline: each starts on its own line
$lines = preg_split('/(?<=[.!؟])\s+/u', $title) ?: [$title];
?>
<section class="hero">
  <div class="wrap hero__grid">
    <div class="hero__text">
      <p class="hero__slogan"><svg viewBox="0 0 107.8 144" aria-hidden="true"><use href="<?= e($brand) ?>#icon"></use></svg><?= __('home.hero.slogan') ?></p>
      <h1><?php foreach ($lines as $line): ?><span class="line"><?= str_replace('X-ray', '<span class="nowrap">X-ray</span>', e($line)) ?></span> <?php endforeach; ?></h1>
      <p class="hero__sub"><?= __('home.hero.sub') ?></p>
      <div class="btn-row">
        <a class="btn btn--primary" href="<?= e(page_url('hospitals', null, 'request')) ?>"><?= __('common.request_collection') ?></a>
        <a class="btn btn--outline" href="<?= e(page_url('buyers', null, 'request')) ?>"><?= __('common.buy_silver') ?></a>
      </div>
    </div>
    <?php partial('lightbox'); ?>
  </div>
</section>

<?php partial('trust'); ?>

<section class="section section--mist" aria-label="<?= __('nav.hospitals') ?> / <?= __('nav.buyers') ?>">
  <div class="wrap doors">
    <article class="door door--hospitals">
      <p class="label"><?= __('home.doors.hospitals.label') ?></p>
      <h2><?= __('home.doors.hospitals.title') ?></h2>
      <p class="door__text"><?= __('home.doors.hospitals.text') ?></p>
      <ul class="checklist">
        <?php foreach (tl('home.doors.hospitals.points') as $point): ?>
          <li><?= icon('check') ?><span><?= e($point) ?></span></li>
        <?php endforeach; ?>
      </ul>
      <div class="btn-row">
        <a class="btn btn--primary" href="<?= e(page_url('hospitals', null, 'request')) ?>"><?= __('common.request_collection') ?></a>
        <a class="text-link" href="<?= e(page_url('hospitals')) ?>"><?= __('home.doors.hospitals.link') ?></a>
      </div>
    </article>
    <article class="door door--buyers on-ink">
      <p class="label"><?= __('home.doors.buyers.label') ?></p>
      <h2><?= __('home.doors.buyers.title') ?></h2>
      <p class="door__text"><?= __('home.doors.buyers.text') ?></p>
      <p class="door__purity"><b>99.9%</b><span><?= __('home.doors.buyers.stat_label') ?></span></p>
      <div class="btn-row">
        <a class="btn btn--signal" href="<?= e(page_url('buyers', null, 'request')) ?>"><?= __('common.buy_silver') ?></a>
        <a class="text-link" href="<?= e(page_url('buyers')) ?>"><?= __('home.doors.buyers.link') ?></a>
      </div>
    </article>
  </div>
</section>

<section class="section" aria-labelledby="process-title">
  <div class="wrap">
    <div class="section-head section-head--split">
      <h2 id="process-title"><?= __('home.process.title') ?></h2>
      <div style="display:grid;gap:1rem;justify-items:start">
        <p class="lead"><?= __('home.process.intro') ?></p>
        <a class="text-link" href="<?= e(page_url('process')) ?>"><?= __('home.process.cta') ?></a>
      </div>
    </div>
    <?php partial('timeline'); ?>
  </div>
</section>

<?php partial('counters', ['tone' => 'mist']); ?>

<section class="section section--ink" aria-labelledby="routes-title">
  <div class="wrap">
    <div class="section-head">
      <h2 id="routes-title"><?= __('routes.title') ?></h2>
      <p><?= __('routes.intro') ?></p>
    </div>
    <?php partial('routes'); ?>
  </div>
</section>

<section class="section" aria-labelledby="paper-title">
  <div class="wrap">
    <div class="section-head">
      <h2 id="paper-title"><?= __('paper.title') ?></h2>
      <p><?= __('paper.intro') ?></p>
    </div>
    <?php partial('paper-trail'); ?>
  </div>
</section>

<?php partial('cta-band'); ?>
