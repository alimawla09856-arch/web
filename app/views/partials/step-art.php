<?php
/**
 * Line illustrations for the four process steps (used until real photos
 * arrive). Neutral on purpose: no hospital names, no readable patient data,
 * and step 3 stays general, as requested in the brief.
 * @var int $i
 */
defined('ARGENTUM') || exit;
$leaf = 'M6 0l6 6a6.5 6.5 0 0 1-6 10.5A6.5 6.5 0 0 1 0 6z';
?>
<svg class="art" viewBox="0 0 320 220" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
<?php if ($i === 0): /* Collection: sealed container on a scale */ ?>
  <path d="M30 172h260" stroke="#BFC6C4"/>
  <rect x="58" y="150" width="204" height="16" rx="3"/>
  <path d="M80 166v8M240 166v8"/>
  <rect x="122" y="180" width="76" height="28" rx="4" fill="#121816" stroke="#121816"/>
  <text x="160" y="199" fill="#78E24B" stroke="none" font-family="Montserrat, sans-serif" font-size="13" font-weight="600" text-anchor="middle">184.6 kg</text>
  <path d="M160 166v14"/>
  <rect x="92" y="62" width="136" height="88" rx="4" fill="#FFFFFF"/>
  <rect x="84" y="50" width="152" height="18" rx="3" fill="#FFFFFF"/>
  <rect x="152" y="50" width="16" height="100" fill="#2F6B1C" stroke="#2F6B1C"/>
  <path d="M168 82c12 2 22 8 26 18"/>
  <rect x="186" y="98" width="26" height="34" rx="3" fill="#FFFFFF"/>
  <path d="<?= $leaf ?>" transform="translate(193 106)" fill="#2F6B1C" stroke="none"/>
  <path d="M104 80h32M104 90h24" stroke="#BFC6C4"/>
<?php elseif ($i === 1): /* Sorting & secure destruction: film into a slot, strips out */ ?>
  <g transform="rotate(-6 150 70)">
    <rect x="86" y="16" width="128" height="96" rx="6" fill="#1B2421" stroke="#1B2421"/>
    <path d="M150 28v72" stroke="#4B5553" stroke-width="5"/>
    <path d="M110 44c16-6 28-6 36 0M110 58c16-6 28-6 36 0M110 72c16-6 28-6 36 0M190 44c-16-6-28-6-36 0M190 58c-16-6-28-6-36 0M190 72c-16-6-28-6-36 0" stroke="#BFC6C4" stroke-width="2"/>
    <rect x="168" y="22" width="40" height="16" rx="2" fill="#EEF2F0" stroke="none"/>
    <path d="M173 28h22M173 33h14" stroke="#121816" stroke-width="3"/>
  </g>
  <rect x="54" y="122" width="212" height="24" rx="5" fill="#FFFFFF"/>
  <path d="M74 134h172" stroke="#121816" stroke-width="3"/>
  <path d="M90 152v40M102 152v28M114 152v46M126 152v34M138 152v42M150 152v30M162 152v44M174 152v36M186 152v42M198 152v26M210 152v38M222 152v32M234 152v40" stroke="#4B5553"/>
  <circle cx="276" cy="186" r="18" fill="#2F6B1C" stroke="#2F6B1C"/>
  <path d="M268 186l6 6 10-11" stroke="#FFFFFF" stroke-width="2.2"/>
<?php elseif ($i === 2): /* Extraction & refining: general, a vessel and a purity gauge */ ?>
  <path d="M70 70h96M78 70v26l-26 82a10 10 0 0 0 9.5 13h113a10 10 0 0 0 9.5-13l-26-82V70"/>
  <path d="M62 150h112" stroke="#BFC6C4"/>
  <g fill="#BFC6C4" stroke="#4B5553" stroke-width="1.2">
    <circle cx="92" cy="174" r="7"/><circle cx="108" cy="180" r="6"/><circle cx="124" cy="172" r="8"/><circle cx="141" cy="179" r="6"/><circle cx="154" cy="171" r="5"/><circle cx="100" cy="162" r="5"/><circle cx="133" cy="160" r="5"/>
  </g>
  <g fill="#BFC6C4" stroke="none"><circle cx="118" cy="132" r="3"/><circle cx="128" cy="118" r="2.5"/><circle cx="112" cy="104" r="2"/><circle cx="124" cy="92" r="1.6"/></g>
  <path d="M206 150a52 52 0 0 1 104 0" />
  <path d="M206 150a52 52 0 0 1 98-24" stroke="#2F6B1C" stroke-width="5"/>
  <path d="M258 150l34-30" stroke="#121816" stroke-width="2.4"/>
  <circle cx="258" cy="150" r="5" fill="#121816" stroke="#121816"/>
  <text x="258" y="182" fill="#121816" stroke="none" font-family="Source Serif 4, serif" font-size="22" font-weight="600" text-anchor="middle">99.9</text>
<?php else: /* Silver back to market: bars and an assay report */ ?>
  <g fill="#E3E9E6" stroke="#4B5553">
    <path d="M40 190l12-30h84l12 30z"/>
    <path d="M150 190l12-30h84l12 30z" fill="#EEF2F0"/>
    <path d="M95 158l12-30h84l12 30z" fill="#BFC6C4"/>
  </g>
  <path d="M60 176h40M170 176h40M115 144h40" stroke="#FFFFFF" stroke-width="2"/>
  <rect x="196" y="20" width="96" height="124" rx="3" fill="#FFFFFF" transform="rotate(5 244 82)"/>
  <g transform="rotate(5 244 82)">
    <path d="M208 36h50M208 46h34" stroke="#BFC6C4"/>
    <text x="208" y="86" fill="#121816" stroke="none" font-family="Source Serif 4, serif" font-size="26" font-weight="600">Ag</text>
    <text x="246" y="86" fill="#2F6B1C" stroke="none" font-family="Montserrat, sans-serif" font-size="15" font-weight="600">99.9%</text>
    <path d="M208 104h70M208 114h56" stroke="#BFC6C4"/>
    <circle cx="270" cy="128" r="9" stroke="#2F6B1C"/>
  </g>
<?php endif; ?>
</svg>
