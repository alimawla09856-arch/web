<?php
defined('ARGENTUM') || exit;
?>
<header class="admin-head"><div><h1 class="admin-title">Photos</h1><p class="muted">Until real photos are uploaded, the website shows its own illustrations. JPG, PNG or WebP up to 8 MB; photos are resized and compressed automatically. Blur any patient name visible on a film before uploading.</p></div></header>
<div class="media-grid">
  <?php foreach (MEDIA_SLOTS as $slot => $label): $m = setting('media.' . $slot); $has = media($slot); ?>
    <form class="panel lead-form" method="post" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <input type="hidden" name="slot" value="<?= e($slot) ?>">
      <h2><?= e($label) ?></h2>
      <div class="media-preview"><?php if ($has): ?><img src="<?= e($has['src']) ?>" alt=""><?php else: ?><span class="muted small">Illustration in use</span><?php endif; ?></div>
      <label class="field"><span class="field__label"><?= $has ? 'Replace photo' : 'Upload photo' ?></span><input class="input" type="file" name="photo" accept="image/jpeg,image/png,image/webp"></label>
      <label class="field"><span class="field__label">Description (English)</span><input class="input" name="alt_en" value="<?= e($m['alt_en'] ?? '') ?>" placeholder="What the photo shows"></label>
      <label class="field"><span class="field__label">Description (Arabic)</span><input class="input" name="alt_ar" value="<?= e($m['alt_ar'] ?? '') ?>" dir="rtl" lang="ar"></label>
      <div class="btn-row">
        <button class="btn btn--primary btn--sm" type="submit">Save</button>
        <?php if ($has): ?><button class="btn btn--quiet btn--sm" type="submit" name="remove" value="1">Remove photo</button><?php endif; ?>
      </div>
    </form>
  <?php endforeach; ?>
</div>
