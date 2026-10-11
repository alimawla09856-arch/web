<?php
/** @var array $sections @var string $section @var array $en @var array $ar */
defined('ARGENTUM') || exit;
$names = ['meta' => 'Page titles (SEO)', 'nav' => 'Menu', 'common' => 'Buttons', 'home' => 'Home page', 'steps' => 'Process steps', 'counters' => 'Counters', 'routes' => 'Impact comparison', 'paper' => 'Documents section', 'docs' => 'Sample documents', 'hospitals' => 'For hospitals', 'buyers' => 'For silver buyers', 'process' => 'Our process', 'about' => 'Impact & about', 'contact' => 'Contact', 'verify' => 'Verify page', 'privacy' => 'Privacy policy', 'notfound' => 'Page not found', 'forms' => 'Forms', 'footer' => 'Footer', 'cookie' => 'Cookie notice', 'emails' => 'Automatic e-mails'];
$label = function (string $path) use ($section): string {
    $parts = explode('.', substr($path, strlen($section) + 1));
    return implode(' › ', array_map(fn ($p) => ctype_digit($p) ? '#' . ($p + 1) : ucfirst(str_replace('_', ' ', $p)), $parts));
};
?>
<header class="admin-head">
  <div>
    <h1 class="admin-title">Website text</h1>
    <p class="muted">Edit English and Arabic side by side. Saving publishes the change immediately; a backup of the previous text is kept on the server.</p>
  </div>
</header>
<nav class="tabs" aria-label="Sections">
  <?php foreach ($sections as $s): ?>
    <a href="<?= e(url('admin/content?section=' . $s)) ?>"<?= $s === $section ? ' aria-current="page"' : '' ?>><?= e($names[$s] ?? ucfirst($s)) ?></a>
  <?php endforeach; ?>
</nav>
<form class="panel content-form" method="post">
  <?= csrf_field() ?>
  <input type="hidden" name="section" value="<?= e($section) ?>">
  <div class="content-row content-row--head"><span></span><span>English</span><span lang="ar" dir="rtl">العربية</span></div>
  <?php foreach ($en as $path => $value): $long = mb_strlen($value) > 70 || str_contains($value, "\n"); ?>
    <div class="content-row">
      <span class="content-row__label"><?= e($label($path)) ?></span>
      <?php if ($long): ?>
        <textarea class="textarea" name="en[<?= e($path) ?>]" rows="<?= min(8, 2 + intdiv(mb_strlen($value), 70)) ?>" aria-label="<?= e($label($path)) ?> (English)"><?= e($value) ?></textarea>
        <textarea class="textarea" name="ar[<?= e($path) ?>]" rows="<?= min(8, 2 + intdiv(mb_strlen($value), 70)) ?>" dir="rtl" lang="ar" aria-label="<?= e($label($path)) ?> (Arabic)"><?= e($ar[$path] ?? '') ?></textarea>
      <?php else: ?>
        <input class="input" name="en[<?= e($path) ?>]" value="<?= e($value) ?>" aria-label="<?= e($label($path)) ?> (English)">
        <input class="input" name="ar[<?= e($path) ?>]" value="<?= e($ar[$path] ?? '') ?>" dir="rtl" lang="ar" aria-label="<?= e($label($path)) ?> (Arabic)">
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
  <div class="sticky-save"><button class="btn btn--primary" type="submit">Save <?= e($names[$section] ?? $section) ?></button></div>
</form>
