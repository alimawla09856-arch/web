<?php
/** Home hero visual: film → silver → green, drawn on a canvas by site.js */
defined('ARGENTUM') || exit;
$brand = url('assets/brand/logo-symbols.svg');
$stages = tl('home.lightbox.stages');
?>
<figure class="lightbox on-ink" data-lightbox data-captions="<?= e(json_encode(tl('home.lightbox.captions'), JSON_UNESCAPED_UNICODE)) ?>" data-icon-path="<?= e(json_decode((string) file_get_contents(ROOT_DIR . '/assets/brand/icon-path.json'), true)['d'] ?? '') ?>">
  <canvas class="lightbox__canvas" data-lightbox-canvas role="img" aria-label="<?= __('home.lightbox.label') ?>"></canvas>
  <svg class="lightbox__fallback" viewBox="0 0 107.8 144" aria-hidden="true"><use href="<?= e($brand) ?>#icon"></use></svg>
  <div class="lightbox__tile" aria-hidden="true">
    <span class="n">47</span><span class="w">107.87</span>
    <span class="sym">Ag</span>
    <span class="name"><?= __('home.lightbox.element') ?></span>
  </div>
  <figcaption class="lightbox__foot">
    <div>
      <p class="lightbox__caption" data-lightbox-caption aria-live="polite"><?= e(tl('home.lightbox.captions')[2] ?? '') ?></p>
      <ol class="lightbox__stages" data-lightbox-stages>
        <?php foreach ($stages as $i => $s): ?><li data-done<?= $i === 2 ? ' data-active' : '' ?>><?= e($s) ?></li><?php endforeach; ?>
      </ol>
    </div>
    <button class="lightbox__replay" type="button" data-lightbox-replay><?= icon('replay') ?><?= __('common.replay') ?></button>
  </figcaption>
</figure>
