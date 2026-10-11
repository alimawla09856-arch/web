<?php
/** @var array $docs */
defined('ARGENTUM') || exit;
?>
<header class="admin-head">
  <div>
    <h1 class="admin-title">Documents</h1>
    <p class="muted">Certificates of destruction and assay reports. Each code can be checked by anyone on <a href="<?= e(page_url('verify', 'en')) ?>" target="_blank" rel="noopener">the verify page</a>.</p>
  </div>
  <a class="btn btn--primary btn--sm" href="<?= e(url('admin/documents/new')) ?>">Issue a document</a>
</header>
<?php if (!$docs): ?>
  <p class="panel muted">No documents yet. Issue one from a lead (button “Issue certificate”) or with “Issue a document”.</p>
<?php else: ?>
<div class="panel table-scroll">
  <table class="data">
    <thead><tr><th>Code</th><th>Type</th><th>Issued to</th><th>Date</th><th>Weight</th><th>Purity</th><th>Status</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($docs as $d): ?>
      <tr>
        <td class="nowrap"><a href="<?= e(url('admin/documents/' . $d['id'] . '/print')) ?>"><?= e($d['code']) ?></a></td>
        <td><?= $d['kind'] === 'destruction' ? 'Certificate of destruction' : 'Assay report' ?></td>
        <td><?= e($d['organisation']) ?></td>
        <td class="nowrap"><?= e($d['issued_on']) ?></td>
        <td><?= $d['weight_kg'] !== null ? e(rtrim(rtrim(number_format((float) $d['weight_kg'], 3), '0'), '.')) . ' kg' : '' ?></td>
        <td><?= $d['purity'] !== null ? e((string) (float) $d['purity']) . '%' : '' ?></td>
        <td><span class="tag tag--<?= $d['revoked'] ? 'closed' : 'won' ?>"><?= $d['revoked'] ? 'Revoked' : 'Valid' ?></span></td>
        <td class="nowrap">
          <a href="<?= e(url('admin/documents/' . $d['id'] . '/print')) ?>">Print EN</a> ·
          <a href="<?= e(url('admin/documents/' . $d['id'] . '/print?lang=ar')) ?>">AR</a> ·
          <form method="post" action="<?= e(url('admin/documents/' . $d['id'] . '/revoke')) ?>" class="inline" onsubmit="return confirm('Change the status of this document?')"><?= csrf_field() ?><button type="submit" class="link-button"><?= $d['revoked'] ? 'Restore' : 'Revoke' ?></button></form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>
