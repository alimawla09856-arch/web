<?php
/**
 * v2 interior page hero: a full-bleed photo with the title over it.
 * @var string $photo  @var string $title  @var string $label  @var string $sub  @var array $actions  @var string $doc  @var bool $compact
 */
defined('ARGENTUM') || exit;
$label ??= '';
$sub ??= '';
$actions ??= [];
$doc ??= '';
$compact ??= false;
?>
<section class="v2-page-hero<?= $doc ? ' v2-page-hero--doc' : '' ?><?= $compact ? ' v2-page-hero--compact' : '' ?>" data-v2-page-hero>
  <figure class="v2-page-hero__media"><?= photo($photo, '', '100vw', true, '') ?></figure>
  <div class="wrap v2-page-hero__inner">
    <div class="v2-page-hero__text">
      <?php if ($label): ?><p class="v2-kicker"><?= e($label) ?></p><?php endif; ?>
      <h1><?= e($title) ?></h1>
      <?php if ($sub): ?><p class="v2-page-hero__sub"><?= e($sub) ?></p><?php endif; ?>
      <?php if ($actions): ?>
        <div class="btn-row">
          <?php foreach ($actions as [$href, $text, $class, $ico]): ?>
            <a class="btn <?= e($class) ?>" data-magnetic href="<?= e($href) ?>"><?= $ico ? icon($ico) : '' ?><?= e($text) ?></a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
    <?php if ($doc): ?><div class="v2-page-hero__doc"><?php partial($doc); ?></div><?php endif; ?>
  </div>
</section>
