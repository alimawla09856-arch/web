<?php
/** Animated counters. Admin chooses "promise" figures or real totals. */
defined('ARGENTUM') || exit;
$mode = setting('counters.mode', 'promise') === 'totals' ? 'totals' : 'promise';
$values = (array) setting('counters.' . $mode, []);
$labels = tl('counters.' . $mode);
?>
<section class="section section--tight<?= ($tone ?? '') === 'mist' ? ' section--mist' : '' ?>" aria-labelledby="counters-title">
  <div class="wrap">
    <h2 class="sr-only" id="counters-title"><?= __('counters.title') ?></h2>
    <dl class="readout" data-counters>
      <?php foreach ($labels as $i => $c):
          $v = (float) ($values[$i] ?? 0);
          $decimals = floor($v) == $v ? 0 : 1; ?>
        <div class="readout__cell">
          <dt class="readout__label"><?= e($c['label']) ?></dt>
          <dd class="readout__value">
            <span class="readout__num" data-count="<?= e((string) $v) ?>" data-decimals="<?= $decimals ?>"><?= e(number_format($v, $decimals)) ?></span>
            <?php if ($c['unit'] !== ''): ?><span class="readout__unit"><?= e($c['unit']) ?></span><?php endif; ?>
          </dd>
        </div>
      <?php endforeach; ?>
    </dl>
  </div>
</section>
