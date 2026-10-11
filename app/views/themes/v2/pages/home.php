<?php
/** v2 home: a photo-led story. Film becomes silver becomes green. */
defined('ARGENTUM') || exit;
$brand = url('assets/brand/logo-symbols.svg');
$title = t('home.hero.title');
$lines = preg_split('/(?<=[.!؟])\s+/u', $title) ?: [$title];
$words = tl('v2.hero_words');
$steps = tl('steps');
$stepPhotos = ['collection-boxes', 'xray-shoulder', 'lab-glass', 'silver-bars-grid'];
$outIcons = [['receipt', 'coin'], ['certificate'], ['flask'], ['flask']];
// Manifesto: {photo-key} tokens become small inline photos
// and stay glued to the word before them and the punctuation after them.
$manifesto = preg_replace_callback('/(\S+)\s*\{([a-z-]+)\}([,.،]?)/u', fn ($m) => '<span class="v2-glue">' . $m[1] . '<span class="v2-chip" aria-hidden="true">' . photo($m[2], '', '160px', false, '') . '</span>' . $m[3] . '</span>', e(t('v2.manifesto')));
$regions = (array) tv('forms.fields.region.options');
?>
<section class="v2-hero" data-v2-hero>
  <div class="v2-hero__pin">
    <div class="v2-hero__text">
      <p class="v2-kicker"><svg viewBox="0 0 107.8 144" aria-hidden="true"><use href="<?= e($brand) ?>#icon"></use></svg><?= __('home.hero.slogan') ?></p>
      <h1 class="v2-hero__title" data-split="hero"><?php foreach ($lines as $line): ?><span class="line"><?= str_replace('X-ray', '<span class="nowrap">X-ray</span>', e($line)) ?></span> <?php endforeach; ?></h1>
      <p class="v2-hero__sub" data-hero-fade><?= __('home.hero.sub') ?></p>
      <div class="btn-row" data-hero-fade>
        <a class="btn btn--primary btn--lg" data-magnetic href="<?= e(page_url('hospitals', null, 'request')) ?>"><?= __('common.request_collection') ?></a>
        <a class="btn btn--outline btn--lg" data-magnetic href="<?= e(page_url('buyers', null, 'request')) ?>"><?= __('common.buy_silver') ?></a>
      </div>
    </div>
    <div class="v2-hero__frame" data-v2-frame>
      <div class="v2-layer v2-layer--film"><?= photo('xray-chest', '', '(min-width: 64rem) 60vw, 100vw', true) ?></div>
      <div class="v2-layer v2-layer--silver" data-v2-layer="silver"><?= photo('silver-bars-grid', '', '100vw', false) ?></div>
      <div class="v2-layer v2-layer--green" data-v2-layer="green"><?= photo('leaf-veins', '', '100vw', false) ?></div>
      <span class="v2-scan" data-v2-scan aria-hidden="true"></span>
      <div class="v2-word" aria-hidden="true"><span class="v2-word__stack" data-v2-words><?php foreach ($words as $w): ?><span><?= e($w) ?></span><?php endforeach; ?></span></div>
      <ol class="v2-meter" aria-hidden="true" data-v2-meter><?php foreach ($words as $i => $w): ?><li<?= $i === 0 ? ' data-on' : '' ?>><?= e($w) ?></li><?php endforeach; ?></ol>
      <p class="v2-frame__slogan" data-v2-slogan aria-hidden="true"><?= __('meta.slogan') ?></p>
      <div class="v2-frame__tile" aria-hidden="true"><span>47</span><b>Ag</b><span>107.87</span></div>
    </div>
  </div>
</section>

<section class="v2-manifesto" aria-label="<?= __('home.world.label') ?>">
  <div class="wrap">
    <p class="v2-manifesto__text" data-v2-manifesto><?= $manifesto ?></p>
  </div>
</section>

<section class="v2-process" data-v2-process aria-labelledby="v2-process-title">
  <div class="v2-process__pin">
    <div class="wrap v2-process__head">
      <h2 id="v2-process-title" data-split><?= __('v2.process_title') ?></h2>
      <p class="v2-process__count" aria-hidden="true"><b data-v2-step-now>01</b>/04</p>
    </div>
    <div class="v2-process__track" data-v2-track>
      <?php foreach ($steps as $i => $s): ?>
        <article class="v2-step">
          <figure class="v2-step__media"><?= photo($stepPhotos[$i], '', '(min-width: 64rem) 40vw, 100vw') ?></figure>
          <div class="v2-step__body">
            <span class="v2-step__n" aria-hidden="true">0<?= $i + 1 ?></span>
            <h3><?= e($s['name']) ?></h3>
            <p><?= e($s['text']) ?></p>
            <ul class="chips">
              <?php foreach ($s['outputs'] as $j => $o): ?><li class="chip"><?= icon($outIcons[$i][$j] ?? 'check') ?><?= e($o) ?></li><?php endforeach; ?>
            </ul>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
    <div class="wrap v2-process__bar" aria-hidden="true"><i data-v2-bar></i></div>
  </div>
</section>

<section class="v2-split" aria-label="<?= __('nav.hospitals') ?> / <?= __('nav.buyers') ?>">
  <a class="v2-split__panel" href="<?= e(page_url('hospitals')) ?>" data-reveal-clip>
    <?= photo('hospital-corridor', 'v2-split__img', '(min-width: 64rem) 50vw, 100vw') ?>
    <span class="v2-split__content">
      <span class="v2-kicker"><?= __('home.doors.hospitals.label') ?></span>
      <span class="v2-split__title"><?= __('home.doors.hospitals.title') ?></span>
      <span class="v2-split__text"><?= __('home.doors.hospitals.text') ?></span>
      <span class="btn btn--signal"><?= __('common.request_collection') ?></span>
    </span>
  </a>
  <a class="v2-split__panel v2-split__panel--silver" href="<?= e(page_url('buyers')) ?>" data-reveal-clip>
    <?= photo('silver-bars', 'v2-split__img', '(min-width: 64rem) 50vw, 100vw') ?>
    <span class="v2-split__content">
      <span class="v2-kicker"><?= __('home.doors.buyers.label') ?></span>
      <span class="v2-split__title"><?= __('home.doors.buyers.title') ?></span>
      <span class="v2-split__stat"><b><span data-count="99.9" data-decimals="1">99.9</span>%</b> <?= __('home.doors.buyers.stat_label') ?></span>
      <span class="btn btn--light"><?= __('common.buy_silver') ?></span>
    </span>
  </a>
</section>

<section class="v2-proof" data-v2-proof aria-labelledby="v2-proof-title">
  <div class="v2-proof__pin">
    <div class="wrap v2-proof__head">
      <h2 id="v2-proof-title" data-split><?= __('paper.title') ?></h2>
      <p class="lead"><?= __('paper.intro') ?></p>
      <a class="text-link" href="<?= e(page_url('verify')) ?>"><?= icon('search') ?><?= __('paper.verify') ?></a>
    </div>
    <div class="v2-proof__deck" data-v2-deck>
      <div class="v2-proof__card"><?php partial('doc-receipt'); ?></div>
      <div class="v2-proof__card"><?php partial('doc-certificate'); ?></div>
      <div class="v2-proof__card"><?php partial('doc-assay'); ?></div>
    </div>
  </div>
</section>

<section class="v2-lebanon" data-v2-lebanon aria-labelledby="v2-lebanon-title">
  <figure class="v2-lebanon__media"><?= photo('beirut', '', '100vw') ?></figure>
  <div class="wrap v2-lebanon__inner">
    <h2 id="v2-lebanon-title" data-split><?= __('v2.lebanon_title') ?></h2>
    <p class="v2-lebanon__text"><?= __('v2.lebanon_text') ?></p>
    <?php partial('counters', ['bare' => true]); ?>
  </div>
  <div class="v2-regions" aria-hidden="true">
    <div class="v2-regions__track" data-v2-regions>
      <?php for ($r = 0; $r < 2; $r++): ?><span><?php foreach ($regions as $label): ?><?= e($label) ?><i></i><?php endforeach; ?></span><?php endfor; ?>
    </div>
  </div>
</section>

<section class="v2-green" data-v2-green aria-labelledby="v2-green-title">
  <div class="v2-green__pin">
    <p class="v2-green__word" aria-hidden="true" style="--img: url('<?= e(url('assets/photos/leaf-veins-1700.webp')) ?>')"><?= __('v2.green_word') ?></p>
    <div class="wrap v2-green__copy">
      <h2 id="v2-green-title"><?= __('meta.slogan') ?></h2>
      <p><?= __('routes.argentum.outcome') ?></p>
      <a class="text-link" href="<?= e(page_url('about')) ?>"><?= __('routes.link') ?></a>
    </div>
  </div>
</section>

<section class="v2-cta" aria-labelledby="v2-cta-title">
  <div class="wrap v2-cta__grid">
    <figure class="v2-cta__media" data-reveal-clip><?= photo('archive-boxes', '', '(min-width: 64rem) 45vw, 100vw') ?></figure>
    <div class="v2-cta__copy">
      <h2 id="v2-cta-title" data-split><?= __('home.cta.title') ?></h2>
      <p class="lead"><?= __('home.cta.text') ?></p>
      <div class="btn-row">
        <a class="btn btn--primary btn--lg" data-magnetic href="<?= e(page_url('hospitals', null, 'request')) ?>"><?= __('common.request_collection') ?></a>
        <a class="btn btn--outline btn--lg" data-magnetic href="<?= e(whatsapp_url()) ?>"><?= icon('whatsapp') ?><?= __('common.whatsapp_chat') ?></a>
      </div>
    </div>
  </div>
</section>
