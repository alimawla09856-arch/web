<?php
/**
 * Lead form. $type is 'collection' (3 steps) or 'silver' (single step).
 * Works without JavaScript (plain POST); with JavaScript it validates inline,
 * shows conditional fields and submits without leaving the page.
 * @var string $type  @var string $page
 */
defined('ARGENTUM') || exit;

$schema = form_schema($type);
$stepped = $type === 'collection';
$steps = [];
foreach ($schema as $name => $f) {
    $steps[$stepped ? $f['step'] : 1][$name] = $f;
}
$sent = ($_GET['sent'] ?? '') === $type;
$failed = isset($_GET['error']);
$captcha = config('captcha.provider') && config('captcha.site_key');
?>
<div class="form-card" data-form-card>
  <form class="lead-form" method="post" action="<?= e(url('api/lead')) ?>" novalidate
        data-lead-form="<?= e($type) ?>"<?= $stepped ? ' data-steps="' . count($steps) . '"' : '' ?><?= $sent ? ' hidden' : '' ?>>
    <input type="hidden" name="form" value="<?= e($type) ?>">
    <input type="hidden" name="lang" value="<?= e(current_lang()) ?>">
    <input type="hidden" name="_ts" value="<?= e(form_token()) ?>">
    <input type="hidden" name="source" value="<?= e($page) ?>">
    <?php if ($captcha): ?><input type="hidden" name="captcha" value=""><?php endif; ?>
    <div class="hp-field" aria-hidden="true">
      <label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
    </div>

    <?php if ($stepped): ?>
      <div class="progress" style="--n: <?= count($steps) ?>">
        <p class="progress__meta" aria-live="polite">
          <strong data-step-name><?= __('forms.steps.0') ?></strong>
          <span data-step-count><?= __('forms.step_of', ['n' => 1, 'total' => count($steps)]) ?></span>
        </p>
        <div class="progress__bars" aria-hidden="true"><?php for ($i = 0; $i < count($steps); $i++): ?><i<?= $i === 0 ? ' data-on' : '' ?>></i><?php endfor; ?></div>
      </div>
    <?php endif; ?>

    <p class="form-alert" data-form-alert role="alert"<?= $failed ? '' : ' hidden' ?>><?= icon('alert') ?><span data-alert-text><?= $failed ? __('forms.messages.fix_errors') : '' ?></span></p>

    <?php $n = 0; foreach ($steps as $stepNo => $fields): ?>
      <fieldset class="lead-form__step" data-step="<?= $n ?>"<?= $n === 0 ? ' data-current' : '' ?>>
        <?php if ($stepped): ?><legend><?= __('forms.steps.' . $n) ?></legend><?php endif; ?>
        <div class="lead-form__fields">
          <?php foreach ($fields as $name => $f): partial('field', ['form' => $type, 'name' => $name, 'f' => $f]); endforeach; ?>
        </div>
      </fieldset>
    <?php $n++; endforeach; ?>

    <div class="lead-form__actions">
      <?php if ($stepped): ?>
        <button class="btn btn--quiet" type="button" data-step-back hidden><?= icon('chevron', 'ico ico--dir flip') ?><?= __('forms.back') ?></button>
        <button class="btn btn--primary" type="button" data-step-next><?= __('forms.next') ?></button>
      <?php endif; ?>
      <button class="btn btn--primary" type="submit" data-submit<?= $stepped ? ' data-last-only' : '' ?>><?= __('forms.' . $type . '.submit') ?></button>
    </div>
    <p class="lead-form__privacy"><?= __('forms.privacy_note') ?> <a href="<?= e(page_url('privacy')) ?>"><?= __('forms.privacy_link') ?></a></p>
  </form>

  <div class="form-success" data-form-success role="status" tabindex="-1"<?= $sent ? '' : ' hidden' ?>>
    <span class="form-success__mark"><?= icon('check') ?></span>
    <h3><?= __('forms.' . $type . '.success_title') ?></h3>
    <p data-success-text><?= __('forms.' . $type . '.success_text') ?></p>
    <a class="text-link" href="<?= e(page_url($page, null, 'request')) ?>" data-form-again><?= __('forms.' . $type . '.again') ?></a>
  </div>
</div>
