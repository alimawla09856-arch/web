<?php
/**
 * Counters with rolling digits. Admin chooses launch "commitments" or real totals.
 * The final figures are rendered server-side; JavaScript rolls them in.
 */
defined('ARGENTUM') || exit;
$mode = setting('counters.mode', 'promise') === 'totals' ? 'totals' : 'promise';
$values = (array) setting('counters.' . $mode, []);
$labels = tl('counters.' . $mode);
?>
<section class="section section--tight counters" aria-labelledby="counters-title">
  <div class="wrap">
    <h2 class="counters__title" id="counters-title" data-split><?= __('counters.title') ?></h2>
    <dl class="readout" data-odometers>
      <?php foreach ($labels as $i => $c):
          $v = (float) ($values[$i] ?? 0);
          $text = number_format($v, floor($v) == $v ? 0 : 1); ?>
        <div class="readout__cell">
          <dt class="readout__label"><?= e($c['label']) ?></dt>
          <dd class="readout__value">
            <span class="sr-only"><?= e($text . ' ' . $c['unit']) ?></span>
            <span class="odo readout__num" aria-hidden="true" data-odometer><?php foreach (str_split($text) as $ch): ?><?php if (ctype_digit($ch)): ?><span class="odo__digit" style="--d:<?= $ch ?>"><span class="odo__strip"><?php for ($n = 0; $n <= 9; $n++): ?><span><?= $n ?></span><?php endfor; ?></span></span><?php else: ?><span class="odo__sep"><?= e($ch) ?></span><?php endif; ?><?php endforeach; ?></span>
            <?php if ($c['unit'] !== ''): ?><span class="readout__unit" aria-hidden="true"><?= e($c['unit']) ?></span><?php endif; ?>
          </dd>
        </div>
      <?php endforeach; ?>
    </dl>
  </div>
</section>
