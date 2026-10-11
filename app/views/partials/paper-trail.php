<?php
/** "Proof at every step": the documents every collection produces. */
defined('ARGENTUM') || exit;
$icons = ['receipt', 'certificate', 'flask'];
$codes = ['', 'ARG-CD-2610-…', 'ARG-AS-2610-…'];
?>
<ul class="paper-trail">
  <?php foreach (tl('paper.items') as $i => $item): ?>
    <li class="paper">
      <div class="paper__head"><span class="paper__when"><?= e($item['when']) ?></span><?= icon($icons[$i]) ?></div>
      <h3><?= e($item['title']) ?></h3>
      <p><?= e($item['text']) ?></p>
      <div class="paper__lines" aria-hidden="true"><i></i><i></i><i></i></div>
      <?php if ($codes[$i]): ?><p class="paper__code" aria-hidden="true"><?= e($codes[$i]) ?></p><?php endif; ?>
    </li>
  <?php endforeach; ?>
</ul>
<p class="paper-trail__foot"><a class="text-link" href="<?= e(page_url('verify')) ?>"><?= icon('search') ?><?= __('paper.verify') ?></a></p>
