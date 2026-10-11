<?php
/** @var array $leads @var array $f */
defined('ARGENTUM') || exit;
$query = http_build_query(array_filter($f));
?>
<header class="admin-head">
  <h1 class="admin-title">Leads <span class="muted">(<?= count($leads) ?>)</span></h1>
  <div class="btn-row">
    <a class="btn btn--outline btn--sm" href="<?= e(url('admin/export?format=csv&' . $query)) ?>">Export CSV</a>
    <a class="btn btn--primary btn--sm" href="<?= e(url('admin/export?format=xlsx&' . $query)) ?>">Export Excel</a>
  </div>
</header>

<form class="filters panel" method="get">
  <label class="field"><span class="field__label">Search</span><input class="input" type="search" name="q" value="<?= e($f['q']) ?>" placeholder="Name, e-mail, phone"></label>
  <label class="field"><span class="field__label">Form</span>
    <select class="select" name="type"><option value="">All</option><option value="collection"<?= $f['type'] === 'collection' ? ' selected' : '' ?>>Collection requests</option><option value="silver"<?= $f['type'] === 'silver' ? ' selected' : '' ?>>Silver enquiries</option></select>
  </label>
  <label class="field"><span class="field__label">Status</span>
    <select class="select" name="status"><option value="">All</option><?php foreach (LEAD_STATUSES as $s): ?><option value="<?= $s ?>"<?= $f['status'] === $s ? ' selected' : '' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select>
  </label>
  <label class="field"><span class="field__label">Priority</span>
    <select class="select" name="priority"><option value="">All</option><option value="high"<?= $f['priority'] === 'high' ? ' selected' : '' ?>>Priority only</option><option value="normal"<?= $f['priority'] === 'normal' ? ' selected' : '' ?>>Normal</option></select>
  </label>
  <label class="field"><span class="field__label">From</span><input class="input" type="date" name="from" value="<?= e($f['from']) ?>"></label>
  <label class="field"><span class="field__label">To</span><input class="input" type="date" name="to" value="<?= e($f['to']) ?>"></label>
  <div class="filters__actions"><button class="btn btn--dark btn--sm" type="submit">Filter</button><?php if ($query): ?><a class="text-link" href="<?= e(url('admin/leads')) ?>">Clear</a><?php endif; ?></div>
</form>

<?php if (!$leads): ?>
  <p class="panel muted">No leads match these filters.</p>
<?php else: ?>
  <div class="panel"><?= render('admin/lead-table', ['leads' => $leads]) ?></div>
<?php endif; ?>
