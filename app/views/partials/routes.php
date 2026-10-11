<?php
/** Impact comparison: what happens to used film with and without Argentum. */
defined('ARGENTUM') || exit;
$routes = ['dumped' => ['film', 'x', 'alert'], 'argentum' => ['film', 'seal', 'flask', 'leaf']];
$showLink = $showLink ?? true;
?>
<div class="routes" data-routes>
  <div class="segmented" role="tablist" aria-label="<?= __('routes.tabs_label') ?>">
    <?php foreach (array_keys($routes) as $key): ?>
      <button type="button" role="tab" id="route-tab-<?= $key ?>" aria-controls="route-<?= $key ?>" data-route="<?= $key ?>"
              aria-selected="<?= $key === 'argentum' ? 'true' : 'false' ?>" tabindex="<?= $key === 'argentum' ? '0' : '-1' ?>"><?= __('routes.' . $key . '.tab') ?></button>
    <?php endforeach; ?>
  </div>
  <?php foreach ($routes as $key => $icons): ?>
    <div class="route route--<?= $key ?>" id="route-<?= $key ?>" role="tabpanel" aria-labelledby="route-tab-<?= $key ?>"<?= $key === 'dumped' ? ' data-js-hidden' : '' ?>>
      <ol class="route__flow">
        <?php foreach (tl('routes.' . $key . '.nodes') as $i => $node): ?>
          <li class="route__node"><span class="route__dot"><?= icon($icons[$i] ?? 'check') ?></span><span><?= e($node) ?></span></li>
        <?php endforeach; ?>
      </ol>
      <p class="route__outcome"><?= __('routes.' . $key . '.outcome') ?></p>
    </div>
  <?php endforeach; ?>
  <?php if ($showLink): ?><p><a class="text-link" href="<?= e(page_url('about')) ?>"><?= __('routes.link') ?></a></p><?php endif; ?>
</div>
