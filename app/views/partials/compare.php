<?php
/** Drag-to-compare: what happens to used film when dumped, and when collected by Argentum. */
defined('ARGENTUM') || exit;
$icons = ['dumped' => ['film', 'x', 'alert'], 'argentum' => ['film', 'seal', 'flask', 'leaf']];
?>
<div class="compare" data-compare>
  <?php foreach (['dumped', 'argentum'] as $key): ?>
    <div class="compare__layer compare__layer--<?= $key ?>">
      <canvas class="compare__fx" data-compare-fx="<?= $key ?>" aria-hidden="true"></canvas>
      <div class="compare__content">
        <h3 class="compare__tag"><?= __('routes.' . $key . '.tab') ?></h3>
        <ol class="compare__flow">
          <?php foreach (tl('routes.' . $key . '.nodes') as $i => $node): ?>
            <li><span class="route__dot"><?= icon($icons[$key][$i] ?? 'check') ?></span><span><?= e($node) ?></span></li>
          <?php endforeach; ?>
        </ol>
        <p class="compare__outcome"><?= __('routes.' . $key . '.outcome') ?></p>
      </div>
    </div>
  <?php endforeach; ?>
  <div class="compare__handle" data-compare-handle role="slider" tabindex="0" aria-label="<?= __('home.compare.label') ?>" aria-valuemin="0" aria-valuemax="100" aria-valuenow="50">
    <span class="compare__grip"><?= icon('chevron', 'ico flip') ?><?= icon('chevron') ?></span>
    <span class="compare__hint"><?= __('home.compare.hint') ?></span>
  </div>
</div>
