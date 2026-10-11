<?php
defined('ARGENTUM') || exit;
$outIcons = [['receipt', 'coin'], ['certificate'], ['flask'], ['flask']];
?>
<section class="page-hero">
  <div class="wrap"><div class="page-hero__text">
    <h1><?= __('process.hero.title') ?></h1>
    <p class="lead"><?= __('process.hero.sub') ?></p>
  </div></div>
</section>

<section class="section" style="padding-block-start:0" aria-label="<?= __('home.process.title') ?>">
  <div class="wrap">
    <ol class="steps-full">
      <?php foreach (tl('steps') as $i => $s): $photo = media('step' . ($i + 1)); ?>
        <li class="step-full">
          <div>
            <span class="step-full__num" aria-hidden="true"><?= $i + 1 ?></span>
            <h2><?= e($s['name']) ?></h2>
            <p><?= e($s['text']) ?></p>
            <div class="outputs">
              <p class="outputs__label"><?= __('common.you_receive') ?></p>
              <ul><?php foreach ($s['outputs'] as $j => $o): ?><li class="chip"><?= icon($outIcons[$i][$j] ?? 'check') ?><?= e($o) ?></li><?php endforeach; ?></ul>
            </div>
          </div>
          <div class="step-full__art">
            <?php if ($photo): ?><?= media_picture($photo, 'photo') ?><?php else: partial('step-art', ['i' => $i]); endif; ?>
          </div>
        </li>
      <?php endforeach; ?>
    </ol>
  </div>
</section>

<section class="section section--ink" aria-labelledby="method-title">
  <div class="wrap">
    <div class="statement" style="border-color:var(--ink-line);max-width:52rem">
      <h2 class="label" id="method-title"><?= __('process.method.title') ?></h2>
      <p><?= __('process.method.text') ?></p>
    </div>
  </div>
</section>

<section class="section" aria-labelledby="trail-title">
  <div class="wrap">
    <div class="section-head">
      <h2 id="trail-title"><?= __('process.table.title') ?></h2>
      <p><?= __('paper.intro') ?></p>
    </div>
    <div class="table-wrap">
      <table class="trail-table">
        <thead><tr><?php foreach (tl('process.table.cols') as $c): ?><th scope="col"><?= e($c) ?></th><?php endforeach; ?></tr></thead>
        <tbody>
          <?php foreach (tl('process.table.rows') as $i => $r): ?>
            <tr><th scope="row"><span class="n"><?= $i + 1 ?></span><?= e($r[0]) ?></th><td><?= e($r[1]) ?></td><td><?= e($r[2]) ?></td></tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <p class="paper-trail__foot"><a class="text-link" href="<?= e(page_url('verify')) ?>"><?= icon('search') ?><?= __('paper.verify') ?></a></p>
  </div>
</section>

<?php partial('cta-band'); ?>
