<?php
/** @var ?array $errors @var ?array $draft */
defined('ARGENTUM') || exit;
$s = $draft ?? settings();
$v = fn (string $group, string $key) => e((string) ($s[$group][$key] ?? ''));
$promiseLabels = array_column(tl('counters.promise'), 'label');
$totalLabels = array_column(tl('counters.totals'), 'label');
$input = function (string $name, string $label, string $value, string $type = 'text', string $hint = '', bool $rtl = false) {
    return '<label class="field"><span class="field__label">' . e($label) . '</span><input class="input" type="' . $type . '" name="' . $name . '" value="' . $value . '"' . ($rtl ? ' dir="rtl" lang="ar"' : '') . '>' . ($hint ? '<span class="field__hint">' . e($hint) . '</span>' : '') . '</label>';
};
?>
<header class="admin-head"><div><h1 class="admin-title">Settings</h1><p class="muted">Contact details, counters, lead alerts and analytics. Passwords and API keys stay in <code>app/config.php</code>.</p></div></header>
<?php if (!empty($errors)): ?>
  <div class="form-alert" role="alert"><?= icon('alert') ?><div><?php foreach ($errors as $er): ?><p><?= e($er) ?></p><?php endforeach; ?></div></div>
<?php endif; ?>
<form class="settings" method="post">
  <?= csrf_field() ?>

  <section class="panel lead-form">
    <h2>Contact details</h2>
    <div class="field-row field-row--2">
      <?= $input('contact_email', 'Public e-mail (shown on the site)', $v('contact', 'email'), 'email') ?>
      <?= $input('contact_phone', 'Main phone', $v('contact', 'phone'), 'tel', 'International format, e.g. +961 1 234 567') ?>
      <?= $input('contact_whatsapp', 'WhatsApp number (click-to-chat)', $v('contact', 'whatsapp'), 'tel', 'Used by the floating WhatsApp button on every page.') ?>
      <?= $input('contact_maps_url', 'Google Maps link', $v('contact', 'maps_url'), 'url') ?>
      <?= $input('contact_address_en', 'Address (English)', $v('contact', 'address_en')) ?>
      <?= $input('contact_address_ar', 'Address (Arabic)', $v('contact', 'address_ar'), 'text', '', true) ?>
      <?= $input('contact_hours_en', 'Working hours (English)', $v('contact', 'hours_en')) ?>
      <?= $input('contact_hours_ar', 'Working hours (Arabic)', $v('contact', 'hours_ar'), 'text', '', true) ?>
    </div>
    <?= $input('contact_maps_embed', 'Map embed link (optional)', $v('contact', 'maps_embed'), 'url', 'Google Maps → Share → Embed a map → copy only the src="…" address.') ?>
    <div class="field-row field-row--2">
      <?= $input('contact_linkedin', 'LinkedIn page', $v('contact', 'linkedin'), 'url') ?>
      <?= $input('contact_facebook', 'Facebook page', $v('contact', 'facebook'), 'url') ?>
      <?= $input('contact_instagram', 'Instagram profile', $v('contact', 'instagram'), 'url') ?>
    </div>
  </section>

  <section class="panel lead-form">
    <h2>Counters on the website</h2>
    <fieldset class="choices choices--inline">
      <legend class="field__label">Which figures to show</legend>
      <label class="choice"><input type="radio" name="counters_mode" value="promise"<?= ($s['counters']['mode'] ?? 'promise') !== 'totals' ? ' checked' : '' ?>><span>Commitments (for launch)</span></label>
      <label class="choice"><input type="radio" name="counters_mode" value="totals"<?= ($s['counters']['mode'] ?? '') === 'totals' ? ' checked' : '' ?>><span>Real totals</span></label>
    </fieldset>
    <div class="field-row field-row--2">
      <div class="stack-sm"><p class="field__label">Commitments</p>
        <?php foreach ($promiseLabels as $i => $l): ?><?= $input("counters_promise_$i", $l, e((string) ($s['counters']['promise'][$i] ?? 0)), 'text') ?><?php endforeach; ?>
      </div>
      <div class="stack-sm"><p class="field__label">Real totals</p>
        <?php foreach ($totalLabels as $i => $l): ?><?= $input("counters_totals_$i", $l, e((string) ($s['counters']['totals'][$i] ?? 0)), 'text') ?><?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="panel lead-form">
    <h2>Lead alerts</h2>
    <?= $input('notifications_lead_email', 'Main lead e-mail (receives every form)', $v('notifications', 'lead_email'), 'text', 'Separate several addresses with commas.') ?>
    <div class="field-row field-row--2">
      <?= $input('notifications_collection_emails', 'Collection requests go to (optional)', $v('notifications', 'collection_emails'), 'text', 'Overrides the main address for hospital requests.') ?>
      <?= $input('notifications_silver_emails', 'Silver enquiries go to (optional)', $v('notifications', 'silver_emails'), 'text', 'Overrides the main address for buyer enquiries.') ?>
    </div>
    <?= $input('notifications_whatsapp_numbers', 'WhatsApp alert numbers', $v('notifications', 'whatsapp_numbers'), 'text', 'Team numbers that get a WhatsApp message for each new lead (needs the WhatsApp provider in config.php).') ?>
    <label class="choice choice--check"><input type="checkbox" name="notifications_auto_reply" value="1"<?= !empty($s['notifications']['auto_reply']) ? ' checked' : '' ?>><span>Send an automatic confirmation e-mail to the visitor, in their language</span></label>
  </section>

  <section class="panel lead-form">
    <h2>Company documents (optional)</h2>
    <p class="muted small">Shown on the “Impact &amp; about” page when filled in.</p>
    <div class="field-row field-row--2">
      <?= $input('company_registration_en', 'Company registration (English)', $v('company', 'registration_en')) ?>
      <?= $input('company_registration_ar', 'Company registration (Arabic)', $v('company', 'registration_ar'), 'text', '', true) ?>
      <?= $input('company_permits_en', 'Permits and licences (English)', $v('company', 'permits_en')) ?>
      <?= $input('company_permits_ar', 'Permits and licences (Arabic)', $v('company', 'permits_ar'), 'text', '', true) ?>
    </div>
  </section>

  <section class="panel lead-form">
    <h2>Google</h2>
    <div class="field-row field-row--2">
      <?= $input('analytics_ga4_id', 'Google Analytics 4 measurement ID', $v('analytics', 'ga4_id'), 'text', 'G-XXXXXXXXXX. When set, visitors are asked for consent before analytics load.') ?>
      <?= $input('analytics_gsc_verification', 'Search Console verification code', $v('analytics', 'gsc_verification'), 'text', 'Paste the HTML tag Google gives you; the code is extracted.') ?>
    </div>
    <p class="muted small">Sitemap for Search Console: <code><?= e(absolute_url('sitemap.xml')) ?></code></p>
  </section>

  <div class="sticky-save"><button class="btn btn--primary" type="submit">Save settings</button></div>
</form>
