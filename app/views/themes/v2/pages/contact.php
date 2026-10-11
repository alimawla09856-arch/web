<?php
defined('ARGENTUM') || exit;
$email = (string) setting('contact.email', '');
$phone = (string) setting('contact.phone', '');
$wa = (string) setting('contact.whatsapp', '');
$address = (string) setting_l('contact.address', '');
$hours = (string) setting_l('contact.hours', '');
$mapsUrl = (string) setting('contact.maps_url', '');
$mapsEmbed = (string) setting('contact.maps_embed', '');
$selected = ($_GET['form'] ?? '') === 'silver' || ($_GET['sent'] ?? '') === 'silver' ? 'silver' : 'collection';
$d = 'contact.details.';
?>
<?php partial('photo-hero', ['photo' => 'collection-boxes', 'title' => t('contact.hero.title'), 'sub' => t('contact.hero.sub'), 'compact' => true]); ?>

<section class="section section--mist" id="request" aria-label="<?= __('contact.chooser.title') ?>">
  <div class="wrap form-layout">
    <div class="form-layout__intro">
      <h2><?= __($d . 'title') ?></h2>
      <ul class="contact-list">
        <li><?= icon('whatsapp') ?><small><?= __($d . 'whatsapp') ?></small><a href="<?= e(whatsapp_url()) ?>"<?= $wa ? ' dir="ltr"' : '' ?>><?= $wa ? e($wa) : __('common.whatsapp_chat') ?></a></li>
        <?php if ($phone): ?><li><?= icon('phone') ?><small><?= __($d . 'phone') ?></small><a href="tel:+<?= e(digits($phone)) ?>" dir="ltr"><?= e($phone) ?></a></li><?php endif; ?>
        <?php if ($email): ?><li><?= icon('mail') ?><small><?= __($d . 'email') ?></small><a href="mailto:<?= e($email) ?>"><?= e($email) ?></a></li><?php endif; ?>
        <?php if ($address): ?><li><?= icon('pin') ?><small><?= __($d . 'address') ?></small><span><?= e($address) ?></span></li><?php endif; ?>
        <?php if ($hours): ?><li><?= icon('clock') ?><small><?= __($d . 'hours') ?></small><span><?= e($hours) ?></span></li><?php endif; ?>
      </ul>
      <?php if (!$phone && !$email && !$address): ?><p class="note"><?= icon('alert') ?><span><?= __($d . 'pending') ?></span></p><?php endif; ?>
      <?php if ($mapsEmbed): ?>
        <iframe class="map-frame" src="<?= e($mapsEmbed) ?>" title="<?= __($d . 'map') ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
      <?php endif; ?>
      <?php if ($mapsUrl): ?><a class="text-link" href="<?= e($mapsUrl) ?>" target="_blank" rel="noopener"><?= icon('pin') ?><?= __($d . 'open_map') ?></a><?php endif; ?>
    </div>

    <div>
      <fieldset class="chooser" data-chooser>
        <legend><?= __('contact.chooser.title') ?></legend>
        <?php foreach (['collection', 'silver'] as $type): ?>
          <label>
            <input type="radio" name="chooser" value="<?= $type ?>"<?= $selected === $type ? ' checked' : '' ?>>
            <span><b><?= __('contact.chooser.' . $type . '.title') ?></b><small><?= __('contact.chooser.' . $type . '.text') ?></small></span>
          </label>
        <?php endforeach; ?>
      </fieldset>
      <?php foreach (['collection', 'silver'] as $type): ?>
        <div data-chooser-panel="<?= $type ?>"<?= $selected === $type ? '' : ' data-js-hidden' ?>>
          <?php partial('lead-form', ['type' => $type, 'page' => 'contact']); ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
