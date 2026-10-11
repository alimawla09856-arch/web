<?php
/** @var array $leads */
defined('ARGENTUM') || exit;
?>
<div class="table-scroll">
<table class="data">
  <thead><tr><th>#</th><th>Received</th><th>Form</th><th>Organisation</th><th>Contact</th><th>Quantity</th><th>Status</th></tr></thead>
  <tbody>
  <?php foreach ($leads as $l): ?>
    <tr<?= $l['status'] === 'new' ? ' class="is-new"' : '' ?>>
      <td><a href="<?= e(url('admin/leads/' . $l['id'])) ?>"><?= (int) $l['id'] ?></a></td>
      <td class="nowrap"><?= e(date('d M, H:i', strtotime($l['created_at']))) ?></td>
      <td><?= $l['type'] === 'collection' ? 'Collection' : 'Silver' ?><?php if ($l['priority'] === 'high'): ?> <span class="tag tag--accent">Priority</span><?php endif; ?></td>
      <td><a href="<?= e(url('admin/leads/' . $l['id'])) ?>"><?= e($l['organisation']) ?></a></td>
      <td><?= e($l['contact_person']) ?><br><span class="muted small nowrap" dir="ltr"><?= e($l['phone']) ?></span></td>
      <td><?= e($l['type'] === 'collection' ? (t('forms.fields.quantity.options.' . $l['quantity']) ?: $l['quantity']) : ($l['quantity'] !== '' ? $l['quantity'] . ' kg' : '')) ?></td>
      <td><span class="tag tag--<?= e($l['status']) ?>"><?= e(ucfirst($l['status'])) ?></span></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
