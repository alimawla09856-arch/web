<?php
defined('ARGENTUM') || exit;
$icons = ['certificate', 'scale', 'flask'];
?>
<section class="trust" aria-label="Argentum">
  <div class="wrap">
    <ul class="trust__list">
      <?php foreach (tl('home.trust') as $i => $item): ?>
        <li class="trust__item"><?= icon($icons[$i] ?? 'check') ?><strong><?= e($item['title']) ?></strong><span><?= e($item['text']) ?></span></li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
