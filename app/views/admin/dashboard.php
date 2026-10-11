<?php
/** @var array $stats @var array $recent @var array $checklist */
defined('ARGENTUM') || exit;
$done = count(array_filter($checklist, fn ($c) => $c[1]));
?>
<header class="admin-head">
  <h1 class="admin-title">Dashboard</h1>
  <div class="btn-row">
    <a class="btn btn--outline btn--sm" href="<?= e(url('admin/export?format=xlsx')) ?>">Export all leads (Excel)</a>
    <a class="btn btn--primary btn--sm" href="<?= e(url('admin/documents/new')) ?>">Issue a document</a>
  </div>
</header>

<section class="kpis" aria-label="Summary">
  <a class="kpi" href="<?= e(url('admin/leads?status=new')) ?>"><b><?= $stats['new'] ?></b><span>New leads to answer</span></a>
  <a class="kpi kpi--accent" href="<?= e(url('admin/leads?priority=high')) ?>"><b><?= $stats['priority'] ?></b><span>Priority leads open</span></a>
  <div class="kpi"><b><?= $stats['month'] ?></b><span>Leads this month</span></div>
  <a class="kpi" href="<?= e(url('admin/leads?type=collection')) ?>"><b><?= $stats['collection'] ?></b><span>Collection requests</span></a>
  <a class="kpi" href="<?= e(url('admin/leads?type=silver')) ?>"><b><?= $stats['silver'] ?></b><span>Silver enquiries</span></a>
  <a class="kpi" href="<?= e(url('admin/documents')) ?>"><b><?= $stats['documents'] ?></b><span>Documents issued</span></a>
</section>

<div class="admin-grid">
  <section class="panel">
    <h2>Latest leads</h2>
    <?php if (!$recent): ?>
      <p class="muted">No leads yet. Requests sent from the website forms appear here instantly.</p>
    <?php else: ?>
      <?= render('admin/lead-table', ['leads' => $recent]) ?>
      <p><a class="text-link" href="<?= e(url('admin/leads')) ?>">All leads</a></p>
    <?php endif; ?>
  </section>
  <section class="panel">
    <h2>Launch checklist <span class="muted">(<?= $done ?>/<?= count($checklist) ?>)</span></h2>
    <ul class="checks">
      <?php foreach ($checklist as [$label, $ok]): ?>
        <li<?= $ok ? ' data-ok' : '' ?>><?= icon($ok ? 'check' : 'alert') ?><?= e($label) ?></li>
      <?php endforeach; ?>
    </ul>
    <p class="muted small">Secrets (SMTP, WhatsApp API, spam keys) go in <code>app/config.php</code>. Everything else is under <a href="<?= e(url('admin/settings')) ?>">Settings</a>.</p>
  </section>
</div>
