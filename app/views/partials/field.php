<?php
/**
 * One form field rendered from the schema in app/forms.php.
 * @var string $form  @var string $name  @var array $f
 */
defined('ARGENTUM') || exit;

$id = $form . '-' . $name;
$base = 'forms.fields.' . $name;
$label = t($base . '.label');
$hint = t($base . '.hint');
$required = !empty($f['required']) || !empty($f['required_if']);
$optional = empty($f['required']) && empty($f['required_if']);
$showIf = isset($f['show_if']) ? implode('=', $f['show_if']) : '';
$describedBy = trim(($hint ? $id . '-hint ' : '') . $id . '-error');
$attrs = ' id="' . e($id) . '" name="' . e($name) . '" aria-describedby="' . e($describedBy) . '"'
    . ($required ? ' required aria-required="true"' : '')
    . (!empty($f['autocomplete']) ? ' autocomplete="' . e($f['autocomplete']) . '"' : '')
    . (isset($f['max']) && $f['type'] !== 'number' ? ' maxlength="' . (int) $f['max'] . '"' : '');
$group = in_array($f['type'], ['radio', 'choice', 'checkboxes'], true);
$wide = in_array($f['type'], ['textarea', 'choice', 'checkboxes'], true) || $name === 'quantity';
?>
<div class="field<?= $wide ? ' field--wide' : '' ?>" data-field="<?= e($name) ?>"<?= $showIf ? ' data-show-if="' . e($showIf) . '"' : '' ?>>
<?php if ($group): ?>
  <fieldset class="choices<?= $f['type'] === 'radio' ? ' choices--inline' : '' ?>" aria-describedby="<?= e($describedBy) ?>">
    <legend class="field__label"><?= e($label) ?><?php if ($optional): ?><span class="opt">(<?= __('common.optional') ?>)</span><?php endif; ?></legend>
    <?php foreach ($f['options'] as $opt): ?>
      <label class="choice<?= $f['type'] === 'checkboxes' ? ' choice--check' : '' ?>">
        <input type="<?= $f['type'] === 'checkboxes' ? 'checkbox' : 'radio' ?>" name="<?= e($name . ($f['type'] === 'checkboxes' ? '[]' : '')) ?>" value="<?= e($opt) ?>"<?= $required && $f['type'] !== 'checkboxes' ? ' required' : '' ?>>
        <span><?= __($base . '.options.' . $opt) ?></span>
      </label>
    <?php endforeach; ?>
  </fieldset>
<?php else: ?>
  <label class="field__label" for="<?= e($id) ?>"><?= e($label) ?><?php if ($optional): ?><span class="opt">(<?= __('common.optional') ?>)</span><?php endif; ?></label>
  <?php if ($f['type'] === 'select'): ?>
    <select class="select"<?= $attrs ?>>
      <option value=""><?= __($base . '.placeholder') ?></option>
      <?php foreach ($f['options'] as $opt): ?>
        <option value="<?= e($opt) ?>"><?= __($base . '.options.' . $opt) ?></option>
      <?php endforeach; ?>
    </select>
  <?php elseif ($f['type'] === 'textarea'): ?>
    <textarea class="textarea" rows="4"<?= $attrs ?>></textarea>
  <?php elseif ($f['type'] === 'tel'): ?>
    <input class="input" type="tel" inputmode="tel" value="+961 " dir="ltr"<?= $attrs ?>>
  <?php elseif ($f['type'] === 'number'): ?>
    <input class="input" type="number" inputmode="decimal" min="<?= (int) $f['min'] ?>" max="<?= (int) $f['max'] ?>" step="<?= e($f['step_value'] ?? '1') ?>"<?= $attrs ?>>
  <?php elseif ($f['type'] === 'date'): ?>
    <input class="input" type="date" min="<?= date('Y-m-d') ?>"<?= $attrs ?>>
  <?php else: ?>
    <input class="input" type="<?= $f['type'] === 'email' ? 'email' : 'text' ?>"<?= $f['type'] === 'email' ? ' inputmode="email" dir="ltr"' : '' ?><?= $attrs ?>>
  <?php endif; ?>
<?php endif; ?>
  <?php if ($hint): ?><p class="field__hint" id="<?= e($id) ?>-hint"><?= e($hint) ?></p><?php endif; ?>
  <p class="field__error" id="<?= e($id) ?>-error" role="alert"><?= icon('alert') ?><span data-error-text></span></p>
  <?php foreach ((array) tv($base . '.notes') as $value => $note): ?>
    <p class="option-note" data-note-for="<?= e($name) ?>" data-note-value="<?= e($value) ?>" hidden><?= icon('alert') ?><span><?= e($note) ?></span></p>
  <?php endforeach; ?>
</div>
