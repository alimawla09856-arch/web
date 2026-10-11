# Argentum — website

Bilingual (English / Arabic) B2B website for **Argentum**, a Lebanese company that buys used X-ray film from hospitals and imaging centres, destroys it securely and recovers the silver inside.

Built for the **Premium package** (see `docs/HANDOVER.md` for the feature checklist): two lead forms with smart conditional fields, instant e-mail and WhatsApp alerts, an admin panel with Excel/CSV export, Google Analytics / Search Console hooks, and a full right-to-left Arabic version.

- **Stack:** plain PHP 8.1+ and SQLite, vanilla JavaScript, one CSS file. No framework, no build step, no Composer — it runs on any standard cPanel hosting.
- **Motion:** GSAP 3.15 (ScrollTrigger, SplitText, DrawSVG, ScrambleText — free standard licence) and Lenis smooth scrolling, vendored in `assets/vendor/`. The home page's scroll story is a small custom WebGL particle engine (`assets/js/world.js`, no 3D library). Reduced-motion and no-WebGL visitors get a calm, fully readable version.
- **Languages:** separate URLs per language (`/en/…`, `/ar/…`), English by default, the visitor's choice is remembered.
- **Speed:** v1 has no photos on the critical path (the hero is drawn on a canvas); v2 serves responsive WebP photos (640 / 1200 / 2000 px) with only the first hero photo loaded eagerly. Google Fonts use `display=swap`.

## Two design versions

The site ships with two complete designs that share the same content, forms, admin panel and URLs. Switch with one line in `app/config.php`:

```php
'theme' => 'v1',   // or 'v2'
```

| | v1 — “Scroll world” | v2 — “Real photos” |
| --- | --- | --- |
| Home | WebGL particle story (film → container → shreds → ingot → Lebanon → leaf) | Photo-led story: the X-ray film is wiped into silver, then into a leaf as you scroll; a manifesto with inline photos; the four steps in a horizontal scroll; a fanned deck of documents; Beirut with the counters; the word *Green* filled with a real leaf |
| Inner pages | Line illustrations, light heroes | Full-bleed photo heroes, photo steps, photos that follow the pointer on the hospitals list, full-screen menu on mobile |
| Files | `app/views/pages/*`, `assets/js/world.js` | `app/views/themes/v2/*` (overrides only), `assets/css/v2.css`, `assets/js/v2.js`, `assets/photos/` |

A file in `app/views/themes/v2/` replaces the file with the same path in `app/views/`; everything else is shared. For a quick test without editing the config: `ARGENTUM_THEME=v2 php -S localhost:8000 index.php`.

### Photos (v2)

`assets/photos/` holds 13 photos supplied by the client (stock images), exported as WebP in several widths with English and Arabic alt text (`manifest.json`). They are processed by `tools/process-photos.py`, which also blurs what must not appear on the site: signage in the hospital corridor and the mint names stamped on the stock silver bars. Replace the silver photos with Argentum’s own silver as soon as it exists, then rerun:

```bash
python3 tools/process-photos.py path/to/originals
```

## Pages

| URL | Page |
| --- | --- |
| `/en/` | Home: a scroll story in 3D particles (X-ray film → sealed container → shredded film → silver ingot → Lebanon → the green leaf), the two audiences, rolling counters, documents in a horizontal scroll, drag-to-compare impact |
| `/en/hospitals/` | For hospitals: benefits, what we collect, confidentiality, FAQ, **Form A — Request a collection** |
| `/en/silver-buyers/` | For silver buyers: purity, pricing, assay report, **Form B — Buy recovered silver** |
| `/en/process/` | The four documented steps and the paper trail |
| `/en/impact/` | Impact & about: mission, vision, environmental impact, documents issued |
| `/en/contact/` | Contact details, map, both forms behind a chooser |
| `/en/verify/` | Public check of a certificate of destruction or assay report code |
| `/en/privacy/` | Privacy policy |
| `/admin/` | Admin panel |
| `/sitemap.xml`, `/robots.txt` | Generated automatically, with `hreflang` alternates |

Every `/en/` page exists under `/ar/` in Arabic.

## Run it locally

```bash
php -S localhost:8000 index.php
```

Open <http://localhost:8000>. Without `app/config.php` the sample configuration is used; the admin panel then shows a password-hash generator on `/admin/login`.

## Design preview on Netlify

`preview/` (v1) and `preview-v2/` (v2) are static copies of every page for showing the design before launch (forms show their confirmation without sending, `/verify/` accepts the sample code `ARG-CD-2610-7K3Q`, and every page is `noindex`).

- Rebuild after any change: `php tools/build-preview.php` and `php tools/build-preview.php --theme=v2`
- Netlify, linked to this repository: publish directory `preview` (already set in `netlify.toml`, no build command). For a second link with v2, create another Netlify site from the same repository with publish directory `preview-v2`.
- Or drag the `preview/` or `preview-v2/` folder onto <https://app.netlify.com/drop>.

Do not upload `preview/`, `preview-v2/` or `tools/` to the PHP host (`.htaccess` blocks them anyway).

## Deploy to cPanel

1. Upload every file of this repository to `public_html/` (or a subfolder — paths adapt automatically). Keep the `.htaccess` files.
2. Copy `app/config.sample.php` to `app/config.php` and fill it in:
   - `base_url` — `https://argentumco.com`
   - `admin.password_hash` — open `/admin/login` once: while no hash is set, the page generates one for you to paste here.
   - `smtp` — create `info@argentumco.com` (or `no-reply@`) in cPanel → Email Accounts and use `mail.argentumco.com`, port 465, `ssl`.
   - `captcha` — optional Google reCAPTCHA v3 or Cloudflare Turnstile keys (honeypot + timing protection is always on).
   - `whatsapp_alerts` — optional WhatsApp lead alerts (CallMeBot for a simple personal alert, or the WhatsApp Business Cloud API).
3. Make sure `storage/` and `uploads/` are writable by PHP (755 is enough on most hosts).
4. Install the SSL certificate (cPanel → SSL/TLS Status → Run AutoSSL), then uncomment the HTTPS redirect at the top of `.htaccess`.
5. Sign in to `/admin/`, open **Settings** and fill in the contact details, lead e-mails, WhatsApp number, Google Analytics ID and Search Console code. The dashboard's **Launch checklist** shows what is still missing.
6. In Google Search Console, submit `https://argentumco.com/sitemap.xml`.

`app/`, `content/` and `storage/` are blocked from the web by `.htaccess`; uploaded files in `uploads/` can never run as scripts.

## Admin panel

| Section | What it does |
| --- | --- |
| Dashboard | New and priority leads, monthly count, launch checklist |
| Leads | Filter by form, status, priority, date or text; open a lead, change its status, add internal notes; **Export CSV / Excel** (respects the filters) |
| Documents | Issue a certificate of destruction or assay report (from a lead or directly), print it in English or Arabic with a QR code, revoke it |
| Website text | Every sentence of the site, English and Arabic side by side; saving publishes immediately and keeps a backup in `storage/` |
| Settings | Contact details, counters (launch “commitments” or real totals), lead alert routing, company documents, Google IDs |
| Photos | Replace the built-in illustrations with real photos; images are resized, compressed, stripped of metadata and converted to WebP |

## How leads flow

1. A visitor sends Form A or Form B. Fields are validated in the browser and again on the server.
2. The lead is stored in `storage/argentum.sqlite` and flagged **Priority** when it is a large volume (500 kg+) or a recurring silver order.
3. Alerts go out immediately: e-mail to the team (collection requests and silver enquiries can go to different addresses), a confirmation e-mail to the visitor in their language, and a WhatsApp message to the team numbers if configured.

## Project structure

```
index.php              front controller (all routes)
.htaccess              Apache rewrite, caching, private folders
app/
  bootstrap.php        loads config and modules
  config.sample.php    copy to config.php on the server (secrets)
  router.php           URLs, sitemap, robots
  content.php          translations, settings, uploaded photos
  forms.php            form schema, validation, spam protection, POST /api/lead
  notify.php           e-mail (SMTP or mail()), WhatsApp alerts
  documents.php        certificate / assay codes, GET /api/verify
  admin.php            admin panel controller, CSV / XLSX export
  views/               layout, pages, partials, admin templates
  views/themes/v2/     v2 overrides of pages and partials
content/
  en.json, ar.json     all website text (edited from the admin panel)
  site.json            settings (edited from the admin panel)
assets/
  css/site.css         design system and components (RTL via logical properties)
  css/v2.css           v2 design layer (loaded only when theme = v2)
  css/admin.css        admin panel
  js/site.js           forms, navigation, tabs, verify, consent
  js/motion.js         shared motion layer: smooth scroll, reveals, counters
  js/world.js          v1 WebGL scroll world
  js/v2.js             v2 scenes: photo wipes, horizontal steps, document deck
  photos/              v2 photo library (WebP + manifest.json)
  brand/               logo SVGs (traced from the supplied artwork), favicons
  img/                 icon sprite, social share images
  vendor/              qrcode-generator 1.4.4 (MIT) for printed documents
storage/               database and backups (not in git, not web-accessible)
uploads/               photos uploaded from the admin panel (not in git)
```

## Brand rules applied

Colours, fonts and Arabic terminology follow the client brief exactly: white and Mist backgrounds, green used sparingly (Moss `#2F6B1C` for anything on white, Signature `#78E24B` only on dark), Source Serif 4 + Montserrat in English, Noto Naskh Arabic + Tajawal in Arabic, no metallic gradients, “Argentum” always in Latin letters.
