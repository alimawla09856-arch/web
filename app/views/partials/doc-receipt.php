<?php
/** Sample collection receipt (signed on site, with the weight). */
defined('ARGENTUM') || exit;
$brand = url('assets/brand/logo-symbols.svg');
$d = 'docs.receipt.';
?>
<article class="doc doc--receipt" data-sample="<?= __('common.sample') ?>" aria-label="<?= __($d . 'title') ?> (<?= __('common.sample') ?>)">
  <div class="doc__top">
    <div>
      <p class="doc__brand"><svg viewBox="0 0 107.8 144" aria-hidden="true"><use href="<?= e($brand) ?>#icon"></use></svg><svg viewBox="0 0 291.9 36.2" aria-hidden="true"><use href="<?= e($brand) ?>#wordmark"></use></svg></p>
      <h3 class="doc__title" style="margin-block-start:.9rem"><?= __($d . 'title') ?></h3>
    </div>
    <p class="doc__code"><?= __($d . 'no') ?><strong data-scramble-code>0147</strong></p>
  </div>
  <dl class="doc__fields">
    <div class="wide"><dt><?= __($d . 'to') ?></dt><dd><?= __('docs.certificate.to_value') ?></dd></div>
    <div><dt><?= __($d . 'date') ?></dt><dd><span dir="ltr">14 / 10 / 2026</span></dd></div>
    <div><dt><?= __($d . 'weight') ?></dt><dd><span dir="ltr">184.6 kg</span></dd></div>
    <div class="wide"><dt><?= __($d . 'payment') ?></dt><dd><?= __($d . 'payment_value') ?></dd></div>
  </dl>
  <div class="doc__foot">
    <div class="doc__sign"><?php partial('signature'); ?><span><?= __($d . 'signature') ?></span></div>
    <span class="doc__stamp" aria-hidden="true"><svg viewBox="0 0 107.8 144"><use href="<?= e($brand) ?>#icon"></use></svg></span>
  </div>
</article>
