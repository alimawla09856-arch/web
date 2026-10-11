<?php
defined('ARGENTUM') || exit;
$brand = url('assets/brand/logo-symbols.svg');
$iconPath = json_decode((string) file_get_contents(ROOT_DIR . '/assets/brand/icon-path.json'), true)['d'] ?? '';
$title = t('home.hero.title');
// Two sentences in the headline: each starts on its own line
$lines = preg_split('/(?<=[.!؟])\s+/u', $title) ?: [$title];
$steps = tl('steps');
$rail = tl('home.world.rail');
$regions = (array) tv('forms.fields.region.options');
$hudIcons = [['receipt', 'coin'], ['certificate'], ['flask'], ['flask']];
?>
<section class="world" data-world data-icon="<?= e($iconPath) ?>" data-lebanon="<?= e(asset('brand/lebanon.json')) ?>" aria-label="<?= __('home.world.label') ?>">
  <div class="world__stage">
    <canvas class="world__canvas" data-world-canvas aria-hidden="true"></canvas>
    <div class="world__shade" aria-hidden="true"></div>
    <div class="world__hud" aria-hidden="true">
      <div class="hud-tile"><span class="n">47</span><span class="w">107.87</span><span class="sym">Ag</span><span class="name">Argentum</span></div>
      <ol class="hud-rail" data-rail>
        <?php foreach ($rail as $i => $label): ?>
          <li<?= $i === 0 ? ' data-active' : '' ?>><span class="hud-rail__dot"></span><span class="hud-rail__t"><?= e($label) ?></span></li>
        <?php endforeach; ?>
      </ol>
      <div class="hud-card" data-hud="1">
        <span class="hud-card__k"><?= __('home.world.weight') ?></span>
        <b class="hud-card__v"><span data-hud-count="184.6" data-decimals="1">0.0</span><small>kg</small></b>
      </div>
      <div class="hud-card hud-card--label" data-hud="2">
        <span class="hud-card__k"><?= __('home.world.patient') ?></span>
        <b class="hud-card__v hud-card__redact" data-hud-scramble>██████ ████████</b>
        <span class="hud-card__stamp"><?= __('home.world.destroyed') ?></span>
      </div>
      <div class="hud-card" data-hud="3">
        <span class="hud-card__k"><?= __('home.world.purity') ?></span>
        <b class="hud-card__v"><span data-hud-count="99.9" data-decimals="1">0.0</span><small>%</small></b>
      </div>
      <ul class="hud-regions" data-hud="4" data-regions>
        <?php foreach ($regions as $key => $label): ?><li data-region="<?= e($key) ?>"><i></i><?= e($label) ?></li><?php endforeach; ?>
      </ul>
    </div>
  </div>

  <div class="world__chapters">
    <header class="chapter chapter--hero" data-chapter="0">
      <div class="chapter__inner">
        <p class="hero__slogan"><svg viewBox="0 0 107.8 144" aria-hidden="true"><use href="<?= e($brand) ?>#icon"></use></svg><?= __('home.hero.slogan') ?></p>
        <h1 class="hero__title" data-split="hero"><?php foreach ($lines as $line): ?><span class="line"><?= str_replace('X-ray', '<span class="nowrap">X-ray</span>', e($line)) ?></span> <?php endforeach; ?></h1>
        <p class="hero__sub" data-hero-fade><?= __('home.hero.sub') ?></p>
        <div class="btn-row" data-hero-fade>
          <a class="btn btn--signal btn--lg" data-magnetic href="<?= e(page_url('hospitals', null, 'request')) ?>"><?= __('common.request_collection') ?></a>
          <a class="btn btn--ghost-ink btn--lg" data-magnetic href="<?= e(page_url('buyers', null, 'request')) ?>"><?= __('common.buy_silver') ?></a>
        </div>
      </div>
      <p class="scroll-hint" data-hero-fade><span><?= __('home.world.scroll') ?></span><i aria-hidden="true"></i></p>
    </header>

    <?php foreach ($steps as $i => $s): ?>
      <article class="chapter" data-chapter="<?= $i + 1 ?>">
        <div class="chapter__inner" data-chapter-text>
          <p class="chapter__num"><span>0<?= $i + 1 ?></span>/04</p>
          <?php if ($i === 3): ?><p class="label"><?= __('home.world.map_title') ?></p><?php endif; ?>
          <h2><?= e($s['name']) ?></h2>
          <p class="chapter__text"><?= e($s['text']) ?></p>
          <ul class="chips chips--ink">
            <?php foreach ($s['outputs'] as $j => $o): ?><li class="chip"><?= icon($hudIcons[$i][$j] ?? 'check') ?><?= e($o) ?></li><?php endforeach; ?>
          </ul>
          <div class="chapter__fallback"><?php partial('step-art', ['i' => $i]); ?></div>
        </div>
      </article>
    <?php endforeach; ?>

    <footer class="chapter chapter--final" data-chapter="5">
      <div class="chapter__inner" data-chapter-text>
        <h2 class="final__title" data-split><?= __('home.world.final_title') ?></h2>
        <p class="chapter__text"><?= __('home.world.final_text') ?></p>
        <div class="btn-row">
          <a class="btn btn--signal btn--lg" data-magnetic href="<?= e(page_url('hospitals', null, 'request')) ?>"><?= __('common.request_collection') ?></a>
          <a class="btn btn--ghost-ink btn--lg" data-magnetic href="<?= e(page_url('process')) ?>"><?= __('home.process.cta') ?></a>
        </div>
      </div>
    </footer>
  </div>
</section>

<section class="marquee" aria-label="<?= __('paper.title') ?>">
  <div class="marquee__track" data-marquee>
    <?php for ($r = 0; $r < 2; $r++): ?>
      <ul class="marquee__group"<?= $r ? ' aria-hidden="true"' : '' ?>>
        <?php foreach (tl('home.trust') as $item): ?>
          <li><?= e($item['title']) ?><svg viewBox="0 0 107.8 144" aria-hidden="true"><use href="<?= e($brand) ?>#icon"></use></svg></li>
        <?php endforeach; ?>
      </ul>
    <?php endfor; ?>
  </div>
</section>

<section class="section section--mist" aria-label="<?= __('nav.hospitals') ?> / <?= __('nav.buyers') ?>">
  <div class="wrap doors">
    <article class="door door--hospitals" data-reveal="door" data-spotlight>
      <p class="label"><?= __('home.doors.hospitals.label') ?></p>
      <h2 data-split><?= __('home.doors.hospitals.title') ?></h2>
      <p class="door__text"><?= __('home.doors.hospitals.text') ?></p>
      <ul class="checklist">
        <?php foreach (tl('home.doors.hospitals.points') as $point): ?>
          <li><?= icon('check') ?><span><?= e($point) ?></span></li>
        <?php endforeach; ?>
      </ul>
      <div class="btn-row">
        <a class="btn btn--primary" data-magnetic href="<?= e(page_url('hospitals', null, 'request')) ?>"><?= __('common.request_collection') ?></a>
        <a class="text-link" href="<?= e(page_url('hospitals')) ?>"><?= __('home.doors.hospitals.link') ?></a>
      </div>
    </article>
    <article class="door door--buyers on-ink" data-reveal="door" data-spotlight>
      <p class="label"><?= __('home.doors.buyers.label') ?></p>
      <h2 data-split><?= __('home.doors.buyers.title') ?></h2>
      <p class="door__text"><?= __('home.doors.buyers.text') ?></p>
      <p class="door__purity"><b><span data-count="99.9" data-decimals="1">99.9</span>%</b><span><?= __('home.doors.buyers.stat_label') ?></span></p>
      <div class="btn-row">
        <a class="btn btn--signal" data-magnetic href="<?= e(page_url('buyers', null, 'request')) ?>"><?= __('common.buy_silver') ?></a>
        <a class="text-link" href="<?= e(page_url('buyers')) ?>"><?= __('home.doors.buyers.link') ?></a>
      </div>
    </article>
  </div>
</section>

<?php partial('counters'); ?>

<section class="paper-scroll" data-paper-scroll aria-labelledby="paper-title">
  <div class="paper-scroll__pin">
    <div class="wrap paper-scroll__head">
      <h2 id="paper-title" data-split><?= __('paper.title') ?></h2>
      <p class="lead"><?= __('paper.intro') ?></p>
      <a class="text-link" href="<?= e(page_url('verify')) ?>"><?= icon('search') ?><?= __('paper.verify') ?></a>
    </div>
    <div class="paper-scroll__track" data-paper-track>
      <?php $items = tl('paper.items'); ?>
      <div class="paper-scroll__item"><p class="paper-scroll__when"><span>01</span><?= e($items[0]['when'] ?? '') ?></p><?php partial('doc-receipt'); ?></div>
      <div class="paper-scroll__item"><p class="paper-scroll__when"><span>02</span><?= e($items[1]['when'] ?? '') ?></p><?php partial('doc-certificate'); ?></div>
      <div class="paper-scroll__item"><p class="paper-scroll__when"><span>03</span><?= e($items[2]['when'] ?? '') ?></p><?php partial('doc-assay'); ?></div>
    </div>
  </div>
</section>

<section class="section section--ink compare-section" aria-labelledby="routes-title">
  <div class="wrap">
    <div class="section-head">
      <h2 id="routes-title" data-split><?= __('routes.title') ?></h2>
      <p><?= __('routes.intro') ?></p>
    </div>
    <?php partial('compare'); ?>
  </div>
</section>

<section class="big-cta" aria-labelledby="cta-title">
  <div class="wrap">
    <h2 id="cta-title" class="big-cta__title" data-split><?= __('home.cta.title') ?></h2>
    <div class="big-cta__row">
      <p class="lead"><?= __('home.cta.text') ?></p>
      <div class="btn-row">
        <a class="btn btn--primary btn--lg" data-magnetic href="<?= e(page_url('hospitals', null, 'request')) ?>"><?= __('common.request_collection') ?></a>
        <a class="btn btn--outline btn--lg" data-magnetic href="<?= e(whatsapp_url()) ?>"><?= icon('whatsapp') ?><?= __('common.whatsapp_chat') ?></a>
      </div>
    </div>
  </div>
</section>
