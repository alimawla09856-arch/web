<?php
/** Sample assay (purity) report for silver buyers. */
defined('ARGENTUM') || exit;
$brand = url('assets/brand/logo-symbols.svg');
$d = 'docs.assay.';
?>
<article class="doc" data-sample="<?= __('common.sample') ?>" aria-label="<?= __($d . 'title') ?> (<?= __('common.sample') ?>)">
  <div class="doc__top">
    <div>
      <p class="doc__brand"><svg viewBox="0 0 107.8 144" aria-hidden="true"><use href="<?= e($brand) ?>#icon"></use></svg><svg viewBox="0 0 291.9 36.2" aria-hidden="true"><use href="<?= e($brand) ?>#wordmark"></use></svg></p>
      <h3 class="doc__title" style="margin-block-start:.9rem"><?= __($d . 'title') ?></h3>
    </div>
    <p class="doc__code"><?= __($d . 'no') ?><strong>ARG-AS-2610-Q8MZ</strong></p>
  </div>
  <div class="doc__result"><span><?= __($d . 'element') ?></span><b>99.9%</b></div>
  <dl class="doc__fields">
    <div><dt><?= __($d . 'batch') ?></dt><dd><span dir="ltr">B-26-041</span></dd></div>
    <div><dt><?= __($d . 'date') ?></dt><dd><span dir="ltr">21 / 10 / 2026</span></dd></div>
    <div><dt><?= __($d . 'weight') ?></dt><dd><span dir="ltr">2.450 kg</span></dd></div>
    <div><dt><?= __($d . 'result') ?></dt><dd><?= __($d . 'note') ?></dd></div>
  </dl>
  <div class="doc__foot">
    <div class="doc__sign"><?php partial('signature'); ?><span><?= __($d . 'signature') ?></span><span><?= __($d . 'verify') ?></span></div>
    <span class="doc__stamp" aria-hidden="true"><svg viewBox="0 0 107.8 144"><use href="<?= e($brand) ?>#icon"></use></svg></span>
  </div>
</article>
