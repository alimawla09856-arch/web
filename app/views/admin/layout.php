<?php
/** @var string $content @var string $title @var string $view */
defined('ARGENTUM') || exit;
$brand = url('assets/brand/logo-symbols.svg');
$nav = ['dashboard' => ['', 'Dashboard'], 'leads' => ['leads', 'Leads'], 'documents' => ['documents', 'Documents'], 'content' => ['content', 'Website text'], 'settings' => ['settings', 'Settings'], 'media' => ['media', 'Photos']];
$active = match ($view) { 'lead' => 'leads', 'document-form' => 'documents', default => $view };
$new = (int) db()->query("SELECT COUNT(*) FROM leads WHERE status = 'new'")->fetchColumn();
$message = flash();
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= e($title) ?> · Argentum admin</title>
<link rel="icon" href="<?= e(url('assets/brand/favicon.svg')) ?>" type="image/svg+xml">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600&family=Source+Serif+4:opsz,wght@8..60,600&family=Tajawal:wght@400;500&display=swap">
<link rel="stylesheet" href="<?= e(asset('css/site.css')) ?>">
<link rel="stylesheet" href="<?= e(asset('css/admin.css')) ?>">
</head>
<body class="admin">
<aside class="admin-side">
  <a class="brand admin-brand" href="<?= e(url('admin/')) ?>">
    <svg class="brand__icon" viewBox="0 0 107.8 144" aria-hidden="true"><use href="<?= e($brand) ?>#icon"></use></svg>
    <svg class="brand__word" viewBox="0 0 291.9 36.2" aria-hidden="true"><use href="<?= e($brand) ?>#wordmark"></use></svg>
  </a>
  <nav aria-label="Admin">
    <ul>
      <?php foreach ($nav as $key => [$path, $label]): ?>
        <li><a href="<?= e(url('admin/' . $path)) ?>"<?= $active === $key ? ' aria-current="page"' : '' ?>><?= e($label) ?><?php if ($key === 'leads' && $new): ?><span class="badge"><?= $new ?></span><?php endif; ?></a></li>
      <?php endforeach; ?>
    </ul>
  </nav>
  <div class="admin-side__foot">
    <a href="<?= e(page_url('home', 'en')) ?>" target="_blank" rel="noopener">View website</a>
    <form method="post" action="<?= e(url('admin/logout')) ?>"><?= csrf_field() ?><button type="submit">Sign out</button></form>
  </div>
</aside>
<main class="admin-main">
  <?php if ($message): ?><p class="flash" role="status"><?= icon('check') ?><?= e($message) ?></p><?php endif; ?>
  <?= $content ?>
</main>
</body>
</html>
