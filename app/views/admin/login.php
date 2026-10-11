<?php
/** @var string $error */
defined('ARGENTUM') || exit;
$brand = url('assets/brand/logo-symbols.svg');
$noPassword = (string) config('admin.password_hash') === '';
$generated = $generated ?? '';
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Sign in · Argentum admin</title>
<link rel="icon" href="<?= e(url('assets/brand/favicon.svg')) ?>" type="image/svg+xml">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600&family=Source+Serif+4:opsz,wght@8..60,600&display=swap">
<link rel="stylesheet" href="<?= e(asset('css/site.css')) ?>">
<link rel="stylesheet" href="<?= e(asset('css/admin.css')) ?>">
</head>
<body class="admin-login">
<main class="login-card">
  <p class="brand"><svg class="brand__icon" viewBox="0 0 107.8 144" aria-hidden="true"><use href="<?= e($brand) ?>#icon"></use></svg><svg class="brand__word" viewBox="0 0 291.9 36.2" aria-hidden="true"><use href="<?= e($brand) ?>#wordmark"></use></svg></p>
  <?php if ($noPassword): ?>
    <h1>Set up the admin password</h1>
    <p class="muted">No password is configured yet. Choose one below: this page turns it into a secure hash. Paste the hash into <code>app/config.php</code> → <code>admin.password_hash</code>. Nothing is saved here.</p>
    <?php if ($error): ?><p class="form-alert"><?= icon('alert') ?><?= e($error) ?></p><?php endif; ?>
    <?php if ($generated): ?>
      <label class="field"><span class="field__label">Your password hash</span><textarea class="textarea" rows="3" readonly onclick="this.select()"><?= e($generated) ?></textarea></label>
    <?php else: ?>
      <form method="post" class="lead-form">
        <label class="field"><span class="field__label">New password (10+ characters)</span><input class="input" type="password" name="new_password" minlength="10" required autocomplete="new-password"></label>
        <button class="btn btn--primary" type="submit">Generate hash</button>
      </form>
    <?php endif; ?>
  <?php else: ?>
    <h1>Sign in</h1>
    <?php if ($error): ?><p class="form-alert"><?= icon('alert') ?><?= e($error) ?></p><?php endif; ?>
    <form method="post" class="lead-form">
      <label class="field"><span class="field__label">User name</span><input class="input" name="user" required autocomplete="username" value="admin"></label>
      <label class="field"><span class="field__label">Password</span><input class="input" type="password" name="password" required autocomplete="current-password" autofocus></label>
      <button class="btn btn--primary" type="submit">Sign in</button>
    </form>
  <?php endif; ?>
</main>
</body>
</html>
