<?php
defined('ARGENTUM') || exit;
$code = normalise_code((string) ($_GET['code'] ?? ''));
?>
<section class="page-hero">
  <div class="wrap"><div class="page-hero__text">
    <h1><?= __('verify.title') ?></h1>
    <p class="lead"><?= __('verify.intro') ?></p>
  </div></div>
</section>
<section class="section" style="padding-block-start:0">
  <div class="wrap"><div class="verify" data-verify>
    <form class="field" method="get" action="<?= e(page_url('verify')) ?>" data-verify-form>
      <label class="field__label" for="verify-code"><?= __('verify.label') ?></label>
      <div class="verify__form">
        <input class="input" id="verify-code" name="code" type="text" required autocomplete="off" spellcheck="false" maxlength="24"
               placeholder="<?= __('verify.placeholder') ?>" value="<?= e($code) ?>" aria-describedby="verify-where">
        <button class="btn btn--primary" type="submit"><?= icon('search') ?><?= __('verify.button') ?></button>
      </div>
      <p class="field__hint" id="verify-where"><strong><?= __('verify.where') ?></strong> <?= __('verify.where_text') ?></p>
      <?php if (defined('ARGENTUM_PREVIEW')): ?><p class="field__hint"><?= is_rtl() ? 'للتجربة، أدخلوا رمز الشهادة النموذجية:' : 'To try it, enter the code of the sample certificate:' ?> <code dir="ltr">ARG-CD-2610-7K3Q</code></p><?php endif; ?>
    </form>
    <div class="verify__result" data-verify-result aria-live="polite" hidden></div>
  </div></div>
</section>
