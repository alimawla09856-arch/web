<?php
/** @var array $errors @var array $old */
defined('ARGENTUM') || exit;
$err = fn ($k) => isset($errors[$k]) ? '<p class="field__error" style="display:flex">' . icon('alert') . e($errors[$k]) . '</p>' : '';
?>
<header class="admin-head"><div><p class="muted small"><a href="<?= e(url('admin/documents')) ?>">Documents</a> / new</p><h1 class="admin-title">Issue a document</h1></div></header>
<form class="panel lead-form narrow" method="post" action="<?= e(url('admin/documents/new')) ?>">
  <?= csrf_field() ?>
  <input type="hidden" name="lead_id" value="<?= (int) ($old['lead_id'] ?? 0) ?>">
  <fieldset class="choices choices--inline">
    <legend class="field__label">Type</legend>
    <label class="choice"><input type="radio" name="kind" value="destruction"<?= ($old['kind'] ?? '') === 'destruction' ? ' checked' : '' ?>><span>Certificate of destruction</span></label>
    <label class="choice"><input type="radio" name="kind" value="assay"<?= ($old['kind'] ?? '') === 'assay' ? ' checked' : '' ?>><span>Assay report</span></label>
  </fieldset>
  <?= $err('kind') ?>
  <label class="field"><span class="field__label">Issued to (organisation)</span><input class="input" name="organisation" value="<?= e($old['organisation'] ?? '') ?>" required maxlength="160"></label>
  <?= $err('organisation') ?>
  <div class="field-row field-row--2">
    <label class="field"><span class="field__label">Date (collection or assay)</span><input class="input" type="date" name="issued_on" value="<?= e($old['issued_on'] ?? '') ?>" required></label>
    <label class="field"><span class="field__label">Net weight (kg)</span><input class="input" type="number" step="0.001" min="0" name="weight_kg" value="<?= e($old['weight_kg'] ?? '') ?>"></label>
  </div>
  <?= $err('issued_on') . $err('weight_kg') ?>
  <div class="field-row field-row--2">
    <label class="field"><span class="field__label">Purity % (assay reports)</span><input class="input" type="number" step="0.01" min="0" max="100" name="purity" value="<?= e($old['purity'] ?? '') ?>"></label>
    <label class="field"><span class="field__label">Reference (batch or collection no.)</span><input class="input" name="reference" value="<?= e($old['reference'] ?? '') ?>" maxlength="80"></label>
  </div>
  <?= $err('purity') ?>
  <label class="field"><span class="field__label">Internal notes (not printed)</span><textarea class="textarea" name="notes" rows="3"><?= e($old['notes'] ?? '') ?></textarea></label>
  <div class="btn-row"><button class="btn btn--primary" type="submit">Issue and print</button></div>
</form>
