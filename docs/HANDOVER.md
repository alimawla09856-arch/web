# Argentum website — first design draft

Prepared for Argentum · Premium package · October 2026

This draft contains every page and every feature of the Premium package, working end to end. The open items at the bottom are the details we still need from Argentum to finish and publish the site.

## Scope delivered

| Package item (proposal) | Where it is |
| --- | --- |
| Hero section | Home: slogan, headline and sub-headline from the brief, two direct buttons, animated “film → silver → green” viewer, trust strip |
| Process flow / interactive recycling lifecycle timeline | Home (interactive, four tabs) and the full *Our process* page |
| Mission & environmental impact | *Impact & about*: mission, vision, environmental impact, patient confidentiality, an interactive “dumped vs collected” comparison |
| Dynamic statistics counters | Home and *Impact & about*. Launch uses the commitment figures from the brief (100% destroyed, 0 kg to landfill, 48 h response, 99.9% purity); switch to real totals from **Admin → Settings** |
| B2B lead forms | Form A *Request a collection* (3 steps) and Form B *Buy recovered silver*, with the exact fields from the brief |
| Smart conditional lead form | “Other” organisation type asks for details; 500 kg+ shows a priority note and asks how many sites; “certificate needed” asks for the name to print; recurring silver orders are flagged; priority leads are marked in the admin |
| Instant alerts — e-mail & WhatsApp | Team e-mail routed per form, confirmation e-mail to the visitor in their language, optional WhatsApp alert to team numbers |
| Admin dashboard | `/admin/`: leads, statuses, notes, launch checklist |
| Excel / CSV export | Leads page, filtered or complete, Arabic-safe |
| Bilingual Arabic / English | Separate URLs `/en/` and `/ar/`, full RTL layout, language remembered, Arabic terminology from the brief |
| Floating WhatsApp button | Every page, with the pre-filled message from the brief (EN/AR) |
| On-page SEO basics | Titles and descriptions per page and language, canonical + hreflang, sitemap.xml, robots.txt, structured data, social share images |
| Google Analytics / Search Console | GA4 loads only after cookie consent; Search Console code field; both set from the admin panel |
| Sub-2-second speed | ~21 KB of compressed CSS + JS, no heavy libraries, canvas hero instead of video |
| Spam protection | Honeypot + timing check + rate limit always on; reCAPTCHA v3 or Cloudflare Turnstile when keys are added |
| Admin editing of text, counters and photos | *Website text*, *Settings* and *Photos* sections |

### Complimentary gift feature: verifiable documents

Every certificate of destruction and assay report can be issued from the admin panel, printed in English or Arabic, and carries a unique code and QR code. Anyone — a hospital auditor, a silver buyer — can check it on `/verify/`. It turns “proof over slogans” into a working tool and protects partners against forged paperwork.

## What we need from Argentum

1. **Hosting & domain:** cPanel access for argentumco.com (the brief leaves hosting empty).
2. **Contact details:** public e-mail, lead notification e-mail, main phone, WhatsApp number, address, Google Maps link, LinkedIn / Facebook / Instagram links.
3. **Company documents:** commercial registration and any Ministry of Environment permits, if they should appear on the site.
4. **Photos:** collection (containers, weighing), facility, recovered silver, X-ray film close-ups. Until then the site uses its own line illustrations instead of stock photos.
5. **Original logo files:** we rebuilt the logo as vector files from the PDF; please send the original SVG/PNG set mentioned in the brief so we can compare.
6. **Real figures** for the counters when you want to switch from commitments to totals.

## Points to confirm

- **Printing presses:** section 0 mentions film from “hospitals and printing presses”, but the forms and counters list medical sources only. Should printing presses be added as an organisation type?
- **Arabic term for “recovered silver”:** the terminology table asks for *الفضة المستخلصة*, while two supplied sentences use *الفضة المستردّة*. The site uses *الفضة المستخلصة* everywhere for consistency.
- **FAQ answers** on the hospitals page are written only from facts in the brief; please confirm them.
- **Privacy policy** text needs a review by Argentum.
- **Sample documents** on the site use illustrative numbers and are watermarked “Sample”.
- **Optional add-on (quoted separately):** live silver price widget on the buyers page.
