<?php defined('ARGENTUM') || exit; ?>
<section class="section section--mist" aria-labelledby="cta-title">
  <div class="wrap cta-band">
    <div>
      <h2 id="cta-title"><?= __('home.cta.title') ?></h2>
      <p><?= __('home.cta.text') ?></p>
    </div>
    <div class="btn-row">
      <a class="btn btn--primary" href="<?= e(page_url('hospitals', null, 'request')) ?>"><?= __('common.request_collection') ?></a>
      <a class="btn btn--outline" href="<?= e(whatsapp_url()) ?>"><?= icon('whatsapp') ?><?= __('common.whatsapp_chat') ?></a>
    </div>
  </div>
</section>
