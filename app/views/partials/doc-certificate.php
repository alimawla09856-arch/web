<?php
/** Sample certificate of destruction (same layout as the printable one in the admin panel). */
defined('ARGENTUM') || exit;
$brand = url('assets/brand/logo-symbols.svg');
$d = 'docs.certificate.';
?>
<article class="doc" data-sample="<?= __('common.sample') ?>" aria-label="<?= __($d . 'title') ?> (<?= __('common.sample') ?>)">
  <div class="doc__top">
    <div>
      <p class="doc__brand"><svg viewBox="0 0 107.8 144" aria-hidden="true"><use href="<?= e($brand) ?>#icon"></use></svg><svg viewBox="0 0 291.9 36.2" aria-hidden="true"><use href="<?= e($brand) ?>#wordmark"></use></svg></p>
      <h3 class="doc__title" style="margin-block-start:.9rem"><?= __($d . 'title') ?></h3>
    </div>
    <p class="doc__code"><?= __($d . 'no') ?><strong>ARG-CD-2610-7K3Q</strong></p>
  </div>
  <dl class="doc__fields">
    <div class="wide"><dt><?= __($d . 'to') ?></dt><dd><?= __($d . 'to_value') ?></dd></div>
    <div><dt><?= __($d . 'date') ?></dt><dd><span dir="ltr">14 / 10 / 2026</span></dd></div>
    <div><dt><?= __($d . 'weight') ?></dt><dd><span dir="ltr">184.6 kg</span></dd></div>
    <div><dt><?= __($d . 'material') ?></dt><dd><?= __($d . 'material_value') ?></dd></div>
    <div><dt><?= __($d . 'method') ?></dt><dd><?= __($d . 'method_value') ?></dd></div>
  </dl>
  <p class="doc__statement"><?= __($d . 'statement') ?></p>
  <div class="doc__foot">
    <div class="doc__sign"><?php partial('signature'); ?><span><?= __($d . 'signature') ?></span><span><?= __($d . 'verify') ?></span></div>
    <span class="doc__stamp" aria-hidden="true"><svg viewBox="0 0 107.8 144"><use href="<?= e($brand) ?>#icon"></use></svg></span>
  </div>
</article>
