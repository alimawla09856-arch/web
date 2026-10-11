"""
Prepares stock / client photos for the website.

- Blurs areas that must not be readable (hospital signage, third-party brand
  names, any patient label) using the boxes in REDACT below.
- Exports each photo as WebP at 640, 1200 and 2000 px wide (never upscaled),
  with metadata stripped.
- Writes assets/photos/manifest.json (sizes + English/Arabic descriptions).

Usage: python3 tools/process-photos.py <folder with the original photos>
"""
import json
import os
import sys

from PIL import Image, ImageDraw, ImageFilter

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
OUT = os.path.join(ROOT, 'assets', 'photos')
WIDTHS = (640, 1200, 2000)

# (x0, y0, x1, y1, blur radius) in original pixels
REDACT = {
    'hospital-corridor': [
        (130, 180, 225, 660, 7), (430, 318, 580, 372, 6), (585, 380, 620, 632, 5),
        (1030, 462, 1100, 780, 7), (1380, 326, 1432, 636, 7), (1460, 410, 1510, 490, 5),
        (1384, 616, 1436, 668, 5), (1255, 285, 1290, 310, 4),
    ],
    'silver-bars': [
        (560, 190, 1335, 800, 4), (1320, 425, 1615, 885, 7),
        (90, 250, 600, 920, 2.2), (420, 640, 1420, 1180, 2.2), (1560, 460, 1900, 1050, 2.2),
    ],
    'silver-bars-grid': [(0, 0, 1800, 1200, 1.4)],
}

ALT = {
    'xray-chest': ('Chest X-ray film on a light box', 'فيلم أشعة للصدر على لوح مضيء'),
    'xray-shoulder': ('X-ray film of a shoulder', 'فيلم أشعة للكتف'),
    'xray-wrist': ('X-ray film of a wrist and hand', 'فيلم أشعة للمعصم واليد'),
    'silver-bars': ('Bars of recovered silver', 'سبائك من الفضة'),
    'silver-bars-grid': ('Rows of silver bars', 'صفوف من سبائك الفضة'),
    'lab-glass': ('Laboratory glassware', 'أدوات زجاجية في مختبر'),
    'lab-bench': ('A clean laboratory bench', 'طاولة مختبر نظيفة'),
    'archive-boxes': ('Archive boxes on shelves', 'صناديق أرشيف على الرفوف'),
    'collection-boxes': ('Sealed boxes ready for collection', 'صناديق مغلقة جاهزة للجمع'),
    'hospital-corridor': ('A quiet hospital corridor', 'ممرّ هادئ في مستشفى'),
    'beirut': ('Beirut and the Raouché rocks from above', 'بيروت وصخرة الروشة من الأعلى'),
    'leaf-veins': ('Close-up of a green leaf with water drops', 'ورقة خضراء عن قرب مع قطرات ماء'),
    'leaf-drops': ('Water drops on a green leaf', 'قطرات ماء على ورقة خضراء'),
}


def redact(im, boxes):
    for x0, y0, x1, y1, r in boxes:
        x0, y0 = max(0, x0), max(0, y0)
        x1, y1 = min(im.width, x1), min(im.height, y1)
        if x1 <= x0 or y1 <= y0:
            continue
        pad = int(r * 3)
        bx = (max(0, x0 - pad), max(0, y0 - pad), min(im.width, x1 + pad), min(im.height, y1 + pad))
        region = im.crop(bx).filter(ImageFilter.GaussianBlur(r))
        mask = Image.new('L', region.size, 0)
        ImageDraw.Draw(mask).rectangle((x0 - bx[0], y0 - bx[1], x1 - bx[0], y1 - bx[1]), fill=255)
        mask = mask.filter(ImageFilter.GaussianBlur(pad / 2))
        im.paste(region, bx[:2], mask)
    return im


def main(src):
    os.makedirs(OUT, exist_ok=True)
    manifest = {}
    for name in sorted(os.listdir(src)):
        stem, ext = os.path.splitext(name)
        if ext.lower() not in ('.webp', '.jpg', '.jpeg', '.png'):
            continue
        im = Image.open(os.path.join(src, name)).convert('RGB')
        im = redact(im, REDACT.get(stem, []))
        sizes = []
        for w in WIDTHS:
            tw = min(w, im.width)
            if tw in sizes:
                continue
            th = round(im.height * tw / im.width)
            im.resize((tw, th), Image.LANCZOS).save(os.path.join(OUT, f'{stem}-{tw}.webp'), 'WEBP', quality=80, method=6)
            sizes.append(tw)
        en, ar = ALT.get(stem, ('', ''))
        manifest[stem] = {'w': im.width, 'h': im.height, 'sizes': sizes, 'alt_en': en, 'alt_ar': ar}
        print(stem, im.size, sizes)
    with open(os.path.join(OUT, 'manifest.json'), 'w', encoding='utf-8') as f:
        json.dump(manifest, f, ensure_ascii=False, indent=2)


if __name__ == '__main__':
    main(sys.argv[1])
