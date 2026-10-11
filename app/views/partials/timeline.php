<?php
/** Interactive 4-step recycling timeline (tabs). Without JS every step is shown. */
defined('ARGENTUM') || exit;
$steps = tl('steps');
$outIcons = [['receipt', 'coin'], ['certificate'], ['flask'], ['flask']];
?>
<div class="timeline" data-timeline>
  <div class="timeline__rail" role="tablist" aria-label="<?= __('home.process.title') ?>">
    <?php foreach ($steps as $i => $s): ?>
      <button class="timeline__tab" type="button" role="tab" id="step-tab-<?= $i ?>" aria-controls="step-panel-<?= $i ?>"
              aria-selected="<?= $i === 0 ? 'true' : 'false' ?>" tabindex="<?= $i === 0 ? '0' : '-1' ?>"<?= $i === 0 ? ' data-reached' : '' ?>>
        <span class="timeline__num"><?= $i + 1 ?></span>
        <span class="timeline__name"><?= e($s['name']) ?></span>
      </button>
    <?php endforeach; ?>
  </div>
  <?php foreach ($steps as $i => $s): ?>
    <div class="timeline__panel" role="tabpanel" id="step-panel-<?= $i ?>" aria-labelledby="step-tab-<?= $i ?>" tabindex="0">
      <div>
        <h3><?= e($s['name']) ?></h3>
        <p><?= e($s['text']) ?></p>
        <div class="outputs">
          <p class="outputs__label"><?= __('common.you_receive') ?></p>
          <ul>
            <?php foreach ($s['outputs'] as $j => $o): ?><li class="chip"><?= icon($outIcons[$i][$j] ?? 'check') ?><?= e($o) ?></li><?php endforeach; ?>
          </ul>
        </div>
      </div>
      <div class="timeline__art"><?php partial('step-art', ['i' => $i]); ?></div>
    </div>
  <?php endforeach; ?>
</div>
