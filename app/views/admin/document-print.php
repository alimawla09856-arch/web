<?php
/** Printable certificate / assay report (A4). @var array $doc @var ?string $flash */
defined('ARGENTUM') || exit;
$brand = url('assets/brand/logo-symbols.svg');
$isCert = $doc['kind'] === 'destruction';
$d = $isCert ? 'docs.certificate.' : 'docs.assay.';
$verifyUrl = absolute_url(page_url('verify')) . '?code=' . rawurlencode($doc['code']);
$date = date('d / m / Y', strtotime($doc['issued_on']));
$weight = $doc['weight_kg'] !== null ? rtrim(rtrim(number_format((float) $doc['weight_kg'], 3), '0'), '.') . ' kg' : '—';
$fonts = 'family=Montserrat:wght@400;500;600&family=Source+Serif+4:opsz,wght@8..60,400;8..60,600' . (is_rtl() ? '&family=Noto+Naskh+Arabic:wght@700&family=Tajawal:wght@400;500;700' : '');
?><!doctype html>
<html lang="<?= current_lang() ?>" dir="<?= is_rtl() ? 'rtl' : 'ltr' ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= e($doc['code']) ?> · <?= __($d . 'title') ?></title>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?<?= $fonts ?>&display=swap">
<link rel="stylesheet" href="<?= e(asset('css/site.css')) ?>">
<link rel="stylesheet" href="<?= e(asset('css/admin.css')) ?>">
</head>
<body class="print-body">
<div class="print-toolbar">
  <a href="<?= e(url('admin/documents')) ?>">← Documents</a>
  <?php if ($flash): ?><span class="muted"><?= e($flash) ?></span><?php endif; ?>
  <span class="spacer"></span>
  <a href="?lang=en"<?= current_lang() === 'en' ? ' aria-current="true"' : '' ?>>English</a>
  <a href="?lang=ar"<?= current_lang() === 'ar' ? ' aria-current="true"' : '' ?>>عربي</a>
  <button class="btn btn--primary btn--sm" type="button" onclick="window.print()">Print / save as PDF</button>
</div>

<article class="sheet<?= $doc['revoked'] ? ' sheet--revoked' : '' ?>">
  <header class="sheet__head">
    <p class="brand" style="direction:ltr"><svg class="brand__icon" viewBox="0 0 107.8 144" aria-hidden="true"><use href="<?= e($brand) ?>#icon"></use></svg><svg class="brand__word" viewBox="0 0 291.9 36.2" aria-hidden="true"><use href="<?= e($brand) ?>#wordmark"></use></svg></p>
    <p class="sheet__code"><?= __($d . 'no') ?><strong><?= e($doc['code']) ?></strong></p>
  </header>

  <h1 class="sheet__title"><?= __($d . 'title') ?></h1>
  <p class="sheet__slogan"><?= __('meta.slogan') ?></p>

  <?php if (!$isCert && $doc['purity'] !== null): ?>
    <div class="doc__result sheet__result"><span><?= __($d . 'element') ?></span><b><?= e((string) (float) $doc['purity']) ?>%</b></div>
  <?php endif; ?>

  <dl class="doc__fields sheet__fields">
    <?php if ($isCert): ?>
      <div class="wide"><dt><?= __($d . 'to') ?></dt><dd><?= e($doc['organisation']) ?></dd></div>
      <div><dt><?= __($d . 'date') ?></dt><dd><span dir="ltr"><?= e($date) ?></span></dd></div>
      <div><dt><?= __($d . 'weight') ?></dt><dd><span dir="ltr"><?= e($weight) ?></span></dd></div>
      <div><dt><?= __($d . 'material') ?></dt><dd><?= __($d . 'material_value') ?></dd></div>
      <div><dt><?= __($d . 'method') ?></dt><dd><?= __($d . 'method_value') ?></dd></div>
    <?php else: ?>
      <div class="wide"><dt><?= __('verify.fields.organisation') ?></dt><dd><?= e($doc['organisation']) ?></dd></div>
      <div><dt><?= __($d . 'batch') ?></dt><dd><span dir="ltr"><?= e($doc['reference'] ?: '—') ?></span></dd></div>
      <div><dt><?= __($d . 'date') ?></dt><dd><span dir="ltr"><?= e($date) ?></span></dd></div>
      <div><dt><?= __($d . 'weight') ?></dt><dd><span dir="ltr"><?= e($weight) ?></span></dd></div>
      <div><dt><?= __($d . 'result') ?></dt><dd><?= __($d . 'note') ?></dd></div>
    <?php endif; ?>
    <?php if ($isCert && $doc['reference']): ?><div><dt><?= __('verify.fields.reference') ?></dt><dd><span dir="ltr"><?= e($doc['reference']) ?></span></dd></div><?php endif; ?>
  </dl>

  <?php if ($isCert): ?><p class="doc__statement sheet__statement"><?= __($d . 'statement') ?></p><?php endif; ?>

  <footer class="sheet__foot">
    <div class="sheet__sign"><span class="sheet__line"></span><span><?= __($d . 'signature') ?></span></div>
    <span class="doc__stamp sheet__stamp" aria-hidden="true"><svg viewBox="0 0 107.8 144"><use href="<?= e($brand) ?>#icon"></use></svg></span>
    <div class="sheet__verify">
      <div id="qr" data-url="<?= e($verifyUrl) ?>"></div>
      <p><?= __('verify.title') ?><br><span dir="ltr"><?= e(preg_replace('#^https?://#', '', absolute_url(page_url('verify')))) ?></span><br><strong dir="ltr"><?= e($doc['code']) ?></strong></p>
    </div>
  </footer>
</article>
<script src="<?= e(asset('vendor/qrcode-generator-1.4.4.js')) ?>"></script>
<script>
  (function () {
    var el = document.getElementById('qr');
    if (!window.qrcode) return;
    var qr = qrcode(0, 'M');
    qr.addData(el.dataset.url);
    qr.make();
    el.innerHTML = qr.createSvgTag({ cellSize: 3, margin: 0, scalable: true });
  })();
</script>
</body>
</html>
