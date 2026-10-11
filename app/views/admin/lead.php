<?php
/** @var array $lead @var array $data @var array $docs */
defined('ARGENTUM') || exit;
$wa = digits($lead['phone']);
?>
<header class="admin-head">
  <div>
    <p class="muted small"><a href="<?= e(url('admin/leads')) ?>">Leads</a> / #<?= (int) $lead['id'] ?></p>
    <h1 class="admin-title"><?= e($lead['organisation']) ?></h1>
    <p class="muted"><?= $lead['type'] === 'collection' ? 'Collection request' : 'Silver purchase enquiry' ?> · received <?= e(date('j M Y, H:i', strtotime($lead['created_at']))) ?> · page in <?= $lead['lang'] === 'ar' ? 'Arabic' : 'English' ?><?php if ($lead['priority'] === 'high'): ?> · <span class="tag tag--accent">Priority</span><?php endif; ?></p>
  </div>
  <div class="btn-row">
    <?php if ($wa): ?><a class="btn btn--outline btn--sm" href="https://wa.me/<?= e($wa) ?>" target="_blank" rel="noopener"><?= icon('whatsapp') ?>WhatsApp</a><?php endif; ?>
    <?php if ($lead['email']): ?><a class="btn btn--outline btn--sm" href="mailto:<?= e($lead['email']) ?>"><?= icon('mail') ?>E-mail</a><?php endif; ?>
    <a class="btn btn--primary btn--sm" href="<?= e(url('admin/documents/new?lead=' . $lead['id'])) ?>"><?= icon('certificate') ?><?= $lead['type'] === 'collection' ? 'Issue certificate' : 'Issue assay report' ?></a>
  </div>
</header>

<div class="admin-grid">
  <section class="panel">
    <h2>Details</h2>
    <dl class="details">
      <?php foreach (lead_rows($lead['type'], $data) as $k => $v): ?>
        <div><dt><?= e($k) ?></dt><dd><?= nl2br(e($v)) ?></dd></div>
      <?php endforeach; ?>
    </dl>
    <p class="muted small">Alerts sent: <?= e($lead['notified'] ?: 'none (check the notification settings)') ?></p>
  </section>

  <div class="stack">
    <form class="panel lead-form" method="post">
      <?= csrf_field() ?>
      <h2>Follow-up</h2>
      <label class="field"><span class="field__label">Status</span>
        <select class="select" name="status"><?php foreach (LEAD_STATUSES as $s): ?><option value="<?= $s ?>"<?= $lead['status'] === $s ? ' selected' : '' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select>
      </label>
      <label class="field"><span class="field__label">Priority</span>
        <select class="select" name="priority"><option value="normal">Normal</option><option value="high"<?= $lead['priority'] === 'high' ? ' selected' : '' ?>>Priority</option></select>
      </label>
      <label class="field"><span class="field__label">Internal notes</span><textarea class="textarea" name="internal_notes" rows="5"><?= e($lead['internal_notes']) ?></textarea></label>
      <div class="btn-row"><button class="btn btn--primary" type="submit">Save</button></div>
    </form>

    <?php if ($docs): ?>
      <section class="panel">
        <h2>Documents for this lead</h2>
        <ul class="plain">
          <?php foreach ($docs as $d): ?><li><a href="<?= e(url('admin/documents/' . $d['id'] . '/print')) ?>"><?= e($d['code']) ?></a> · <?= e($d['issued_on']) ?></li><?php endforeach; ?>
        </ul>
      </section>
    <?php endif; ?>

    <form method="post" onsubmit="return confirm('Delete this lead permanently?')">
      <?= csrf_field() ?>
      <button class="btn btn--quiet btn--sm danger" type="submit" name="delete" value="1">Delete lead</button>
    </form>
  </div>
</div>
