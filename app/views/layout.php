<?php
/** @var string $page  @var string $content */
defined('ARGENTUM') || exit;

$lang = current_lang();
$metaKey = 'meta.pages.' . $page;
$title = t($metaKey . '.title');
$description = t($metaKey . '.description');
$isPublic = $page !== '404';
$preview = defined('ARGENTUM_PREVIEW');
$canonical = $isPublic ? absolute_url(page_url($page)) : '';
$ga = (string) setting('analytics.ga4_id', '');
$gsc = (string) setting('analytics.gsc_verification', '');
$fonts = 'family=Montserrat:wght@400;500;600&family=Source+Serif+4:opsz,wght@8..60,400;8..60,600';
if (is_rtl()) {
    $fonts .= '&family=Noto+Naskh+Arabic:wght@600;700&family=Tajawal:wght@400;500;700';
}
$org = array_filter([
    '@context' => 'https://schema.org',
    '@type'    => 'Organization',
    'name'     => 'Argentum',
    'url'      => absolute_url(page_url('home', 'en')),
    'logo'     => absolute_url('assets/brand/argentum-512.png'),
    'slogan'   => t('meta.slogan'),
    'areaServed' => 'LB',
    'email'    => setting('contact.email'),
    'telephone' => setting('contact.phone'),
    'sameAs'   => array_values(array_filter([setting('contact.linkedin'), setting('contact.facebook'), setting('contact.instagram')])),
]);
?><!doctype html>
<html lang="<?= $lang ?>" dir="<?= is_rtl() ? 'rtl' : 'ltr' ?>" class="no-js">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?></title>
<meta name="description" content="<?= e($description) ?>">
<?php if ($isPublic): ?>
<link rel="canonical" href="<?= e($canonical) ?>">
<?php foreach (LANGS as $l): ?>
<link rel="alternate" hreflang="<?= $l ?>" href="<?= e(absolute_url(page_url($page, $l))) ?>">
<?php endforeach; ?>
<link rel="alternate" hreflang="x-default" href="<?= e(absolute_url(page_url($page, 'en'))) ?>">
<?php else: ?>
<meta name="robots" content="noindex">
<?php endif; ?>
<?php if ($preview && $isPublic): ?><meta name="robots" content="noindex, nofollow"><?php endif; ?>
<meta property="og:type" content="website">
<meta property="og:site_name" content="Argentum">
<meta property="og:title" content="<?= e($title) ?>">
<meta property="og:description" content="<?= e($description) ?>">
<meta property="og:locale" content="<?= $lang === 'ar' ? 'ar_LB' : 'en_GB' ?>">
<?php if ($isPublic): ?><meta property="og:url" content="<?= e($canonical) ?>"><?php endif; ?>
<meta property="og:image" content="<?= e(absolute_url('assets/img/og-' . $lang . '.png')) ?>">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta name="twitter:card" content="summary_large_image">
<?php if ($gsc !== ''): ?><meta name="google-site-verification" content="<?= e($gsc) ?>"><?php endif; ?>
<meta name="theme-color" content="#FFFFFF">
<link rel="icon" href="<?= e(url('assets/brand/favicon.svg')) ?>" type="image/svg+xml">
<link rel="icon" href="<?= e(url('assets/brand/favicon-32.png')) ?>" sizes="32x32" type="image/png">
<link rel="apple-touch-icon" href="<?= e(url('assets/brand/apple-touch-icon.png')) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?<?= $fonts ?>&display=swap">
<link rel="stylesheet" href="<?= e(asset('css/site.css')) ?>">
<?php if (theme() === 'v2'): ?><link rel="stylesheet" href="<?= e(asset('css/v2.css')) ?>"><?php endif; ?>
<script>
(function (h) {
  h.classList.replace('no-js', 'js');
  if (matchMedia('(prefers-reduced-motion: reduce)').matches) return;
  h.classList.add('motion-pending');
  setTimeout(function () { h.classList.remove('motion-pending', 'is-loading'); }, 4500);
<?php if ($page === 'home'): ?>
  try { if (!sessionStorage.getItem('argentum-intro')) h.classList.add('is-loading'); } catch (e) {}
<?php endif; ?>
})(document.documentElement);
</script>
<script type="application/ld+json"><?= json_encode($org, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
<?php if ($page === 'hospitals'): ?>
<script type="application/ld+json"><?= json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => array_map(fn ($i) => ['@type' => 'Question', 'name' => $i['q'], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $i['a']]], tl('hospitals.faq.items')),
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
<?php endif; ?>
</head>
<body class="page-<?= e($page) ?> theme-<?= theme() ?>">
<?php if ($page === 'home'): ?>
<div class="preloader" data-preloader aria-hidden="true">
  <div class="preloader__inner">
    <svg class="preloader__leaf" viewBox="0 0 107.8 144"><path d="<?= e(json_decode((string) file_get_contents(ROOT_DIR . '/assets/brand/icon-path.json'), true)['d'] ?? '') ?>"/></svg>
    <p class="preloader__count"><b data-preloader-count>00</b>Ag</p>
  </div>
</div>
<?php endif; ?>
<a class="skip-link" href="#main"><?= __('nav.skip') ?></a>
<?php partial('header', ['page' => $page]); ?>
<main id="main" tabindex="-1">
<?= $content ?>
</main>
<?php partial('footer'); ?>
<?php partial('whatsapp', ['page' => $page]); ?>
<?php if ($ga !== ''): ?>
<div class="consent" data-consent hidden>
  <p><?= __('cookie.text') ?> <a href="<?= e(page_url('privacy')) ?>"><?= __('footer.privacy') ?></a></p>
  <div class="consent__actions">
    <button class="btn btn--quiet btn--sm" type="button" data-consent-choice="no"><?= __('cookie.decline') ?></button>
    <button class="btn btn--primary btn--sm" type="button" data-consent-choice="yes"><?= __('cookie.accept') ?></button>
  </div>
</div>
<?php endif; ?>
<script>window.ARGENTUM=<?= json_encode([
    'lang'    => $lang,
    'rtl'     => is_rtl(),
    'lead'    => url('api/lead'),
    'verify'  => url('api/verify'),
    'brand'   => url('assets/brand/logo-symbols.svg'),
    'icons'   => url('assets/img/icons.svg'),
    'ga'      => $ga,
    'demo'    => $preview ? [
        'ARG-CD-2610-7K3Q' => ['kind' => t('verify.kinds.destruction'), 'issued_on' => '2026-10-14', 'organisation' => t('docs.certificate.to_value'), 'weight_kg' => 184.6, 'purity' => null, 'reference' => ''],
        'ARG-AS-2610-Q8MZ' => ['kind' => t('verify.kinds.assay'), 'issued_on' => '2026-10-21', 'organisation' => '—', 'weight_kg' => 2.45, 'purity' => 99.9, 'reference' => 'B-26-041'],
    ] : null,
    'captcha' => config('captcha.provider') && config('captcha.site_key') ? ['provider' => config('captcha.provider'), 'key' => config('captcha.site_key')] : null,
    'i18n'    => [
        'sending' => t('forms.sending'),
        'error'   => t('forms.messages.error'),
        'fix'     => t('forms.messages.fix_errors'),
        'errors'  => tv('forms.errors'),
        'verify'  => tv('verify'),
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>;</script>
<?php foreach (['gsap/gsap.min.js', 'gsap/ScrollTrigger.min.js', 'gsap/SplitText.min.js', 'gsap/DrawSVGPlugin.min.js', 'gsap/ScrambleTextPlugin.min.js', 'lenis-1.3.26.min.js'] as $lib): ?>
<script src="<?= e(asset('vendor/' . $lib)) ?>" defer></script>
<?php endforeach; ?>
<script src="<?= e(asset('js/site.js')) ?>" defer></script>
<?php if ($page === 'home' && theme() === 'v1'): ?><script src="<?= e(asset('js/world.js')) ?>" defer></script><?php endif; ?>
<script src="<?= e(asset('js/motion.js')) ?>" defer></script>
<?php if (theme() === 'v2'): ?><script src="<?= e(asset('js/v2.js')) ?>" defer></script><?php endif; ?>
</body>
</html>
