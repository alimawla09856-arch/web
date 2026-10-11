<?php
/**
 * Certificates of destruction and assay reports issued from the admin panel.
 * Each document gets a unique code that anyone can check on /verify/, which
 * protects hospitals and buyers against forged paperwork.
 */
defined('ARGENTUM') || exit;

const DOC_KINDS = ['destruction' => 'CD', 'assay' => 'AS'];

/** e.g. ARG-CD-2610-7K3Q (kind, year+month, random part without look-alike characters) */
function new_document_code(string $kind): string
{
    $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    do {
        $rand = '';
        for ($i = 0; $i < 4; $i++) {
            $rand .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }
        $code = 'ARG-' . DOC_KINDS[$kind] . '-' . date('ym') . '-' . $rand;
        $exists = db()->prepare('SELECT 1 FROM documents WHERE code = ?');
        $exists->execute([$code]);
    } while ($exists->fetchColumn());
    return $code;
}

function normalise_code(string $code): string
{
    return strtoupper(preg_replace('/[^A-Za-z0-9-]/', '', $code) ?? '');
}

function find_document(string $code): ?array
{
    $stmt = db()->prepare('SELECT * FROM documents WHERE code = ?');
    $stmt->execute([normalise_code($code)]);
    return $stmt->fetch() ?: null;
}

/** "Saint George Hospital" → "Sai••• Geo••• Hos•••" */
function mask_name(string $name): string
{
    $words = preg_split('/\s+/u', trim($name)) ?: [];
    return implode(' ', array_map(fn ($w) => mb_strlen($w) <= 3 ? $w : mb_substr($w, 0, 3) . '•••', $words));
}

/** GET /api/verify?code=… */
function api_verify(): never
{
    set_lang((string) ($_GET['lang'] ?? 'en'));
    if (!rate_limit('verify:' . client_hash(), 30, 600)) {
        json_response(['ok' => false, 'message' => t('verify.rate_limited')], 429);
    }
    $code = normalise_code((string) ($_GET['code'] ?? ''));
    $doc = $code !== '' ? find_document($code) : null;
    if (!$doc) {
        json_response(['ok' => true, 'found' => false, 'code' => $code]);
    }
    json_response([
        'ok'           => true,
        'found'        => true,
        'revoked'      => (bool) $doc['revoked'],
        'code'         => $doc['code'],
        'kind'         => t('verify.kinds.' . $doc['kind']),
        'issued_on'    => $doc['issued_on'],
        'organisation' => mask_name($doc['organisation']),
        'weight_kg'    => $doc['weight_kg'] !== null ? (float) $doc['weight_kg'] : null,
        'purity'       => $doc['purity'] !== null ? (float) $doc['purity'] : null,
        'reference'    => $doc['reference'],
    ]);
}
