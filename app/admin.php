<?php
/**
 * Admin panel (/admin/): leads, exports, documents, site text, settings and photos.
 * English only — the team works in English (brief, section 7).
 */
defined('ARGENTUM') || exit;

const MEDIA_SLOTS = [
    'film'   => 'Hospitals page — X-ray film (beside “What we collect”)',
    'silver' => 'Silver buyers page — recovered silver',
    'step1'  => 'Process step 1 — collection',
    'step2'  => 'Process step 2 — sorting & secure destruction',
    'step3'  => 'Process step 3 — extraction & refining',
    'step4'  => 'Process step 4 — silver back to market',
];

function admin_dispatch(string $path, string $method): void
{
    admin_session();
    header('X-Robots-Tag: noindex, nofollow');
    header('Cache-Control: no-store');
    set_lang('en');
    $path = '/' . trim($path, '/');

    if ($path === '/login') {
        $method === 'POST' ? admin_login_post() : admin_login_view();
        return;
    }
    if (!admin_user()) {
        redirect(url('admin/login'));
    }
    if ($method === 'POST' && !hash_equals($_SESSION['csrf'] ?? '', (string) ($_POST['_csrf'] ?? ''))) {
        http_response_code(400);
        exit('This form expired. Go back, reload the page and try again.');
    }

    switch (true) {
        case $path === '/':
            admin_dashboard();
            break;
        case $path === '/logout' && $method === 'POST':
            $_SESSION = [];
            session_destroy();
            redirect(url('admin/login'));
        case $path === '/leads':
            admin_leads();
            break;
        case (bool) preg_match('#^/leads/(\d+)$#', $path, $m):
            $method === 'POST' ? admin_lead_update((int) $m[1]) : admin_lead_view((int) $m[1]);
            break;
        case $path === '/export':
            admin_export();
            break;
        case $path === '/documents':
            admin_documents();
            break;
        case $path === '/documents/new':
            $method === 'POST' ? admin_document_create() : admin_document_form();
            break;
        case (bool) preg_match('#^/documents/(\d+)/print$#', $path, $m):
            admin_document_print((int) $m[1]);
            break;
        case (bool) preg_match('#^/documents/(\d+)/revoke$#', $path, $m) && $method === 'POST':
            db()->prepare('UPDATE documents SET revoked = 1 - revoked WHERE id = ?')->execute([(int) $m[1]]);
            flash('Document status updated.');
            redirect(url('admin/documents'));
        case $path === '/content':
            $method === 'POST' ? admin_content_save() : admin_content();
            break;
        case $path === '/settings':
            $method === 'POST' ? admin_settings_save() : admin_view('settings', [], 'Settings');
            break;
        case $path === '/media':
            $method === 'POST' ? admin_media_save() : admin_view('media', [], 'Photos');
            break;
        default:
            http_response_code(404);
            admin_view('message', ['message' => 'This admin page does not exist.'], 'Not found');
    }
}

/* ---------- Session, auth, helpers ---------- */

function admin_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    session_name('argentum_admin');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => base_path() . '/admin',
        'secure'   => is_https(),
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_start();
    $_SESSION['csrf'] ??= bin2hex(random_bytes(16));
    // Sign out after 8 hours of inactivity
    if (isset($_SESSION['seen']) && time() - $_SESSION['seen'] > 8 * 3600) {
        $_SESSION = ['csrf' => bin2hex(random_bytes(16))];
    }
    $_SESSION['seen'] = time();
}

function admin_user(): bool
{
    return !empty($_SESSION['admin']);
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e($_SESSION['csrf'] ?? '') . '">';
}

function flash(?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['flash'] = $message;
        return null;
    }
    $m = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $m;
}

function admin_view(string $view, array $vars = [], string $title = 'Admin'): void
{
    header('Content-Type: text/html; charset=utf-8');
    $content = render('admin/' . $view, $vars);
    echo render('admin/layout', ['content' => $content, 'title' => $title, 'view' => $view]);
}

function admin_login_view(string $error = ''): void
{
    header('Content-Type: text/html; charset=utf-8');
    echo render('admin/login', ['error' => $error]);
}

function admin_login_post(): void
{
    $hash = (string) config('admin.password_hash', '');
    // Hash generator while no password is configured (prints a hash, stores nothing)
    if ($hash === '' && isset($_POST['new_password'])) {
        $pw = (string) $_POST['new_password'];
        if (mb_strlen($pw) < 10) {
            admin_login_view('Use at least 10 characters.');
            return;
        }
        header('Content-Type: text/html; charset=utf-8');
        echo render('admin/login', ['error' => '', 'generated' => password_hash($pw, PASSWORD_DEFAULT)]);
        return;
    }
    if (!rate_limit('login:' . client_hash(), 6, 900)) {
        admin_login_view('Too many attempts. Wait 15 minutes and try again.');
        return;
    }
    $userOk = hash_equals((string) config('admin.user', 'admin'), (string) ($_POST['user'] ?? ''));
    if ($hash !== '' && $userOk && password_verify((string) ($_POST['password'] ?? ''), $hash)) {
        session_regenerate_id(true);
        $_SESSION['admin'] = true;
        redirect(url('admin/'));
    }
    admin_login_view('The user name or password is not correct.');
}

/* ---------- Dashboard ---------- */

function admin_dashboard(): void
{
    $pdo = db();
    $count = function (string $sql, array $p = []) use ($pdo): int {
        $s = $pdo->prepare($sql);
        $s->execute($p);
        return (int) $s->fetchColumn();
    };
    $stats = [
        'new'        => $count("SELECT COUNT(*) FROM leads WHERE status = 'new'"),
        'priority'   => $count("SELECT COUNT(*) FROM leads WHERE priority = 'high' AND status IN ('new','contacted')"),
        'month'      => $count('SELECT COUNT(*) FROM leads WHERE created_at >= ?', [date('Y-m-01 00:00:00')]),
        'collection' => $count("SELECT COUNT(*) FROM leads WHERE type = 'collection'"),
        'silver'     => $count("SELECT COUNT(*) FROM leads WHERE type = 'silver'"),
        'documents'  => $count('SELECT COUNT(*) FROM documents WHERE revoked = 0'),
    ];
    $recent = $pdo->query('SELECT * FROM leads ORDER BY id DESC LIMIT 8')->fetchAll();
    admin_view('dashboard', ['stats' => $stats, 'recent' => $recent, 'checklist' => launch_checklist()], 'Dashboard');
}

/** What is still missing before the site can go live. */
function launch_checklist(): array
{
    return [
        ['Server configuration file (app/config.php) created', !config('_is_sample')],
        ['Admin password set', (string) config('admin.password_hash') !== ''],
        ['Lead notification e-mail set', (bool) setting('notifications.lead_email') || (bool) setting('notifications.collection_emails')],
        ['SMTP e-mail account configured (recommended)', (bool) config('smtp.host')],
        ['WhatsApp number for the click-to-chat button', (bool) setting('contact.whatsapp')],
        ['Public phone, e-mail and address', setting('contact.phone') && setting('contact.email') && setting('contact.address_en')],
        ['WhatsApp lead alerts (Premium)', (bool) config('whatsapp_alerts.provider') && (bool) setting('notifications.whatsapp_numbers')],
        ['Spam protection keys (reCAPTCHA / Turnstile)', (bool) config('captcha.secret_key')],
        ['Google Analytics 4 measurement ID', (bool) setting('analytics.ga4_id')],
        ['Google Search Console verification', (bool) setting('analytics.gsc_verification')],
        ['Real photos uploaded', count(array_filter(array_keys(MEDIA_SLOTS), fn ($s) => media($s) !== null)) > 0],
    ];
}

/* ---------- Leads ---------- */

function lead_filters(): array
{
    $f = [
        'type'     => in_array($_GET['type'] ?? '', LEAD_TYPES, true) ? $_GET['type'] : '',
        'status'   => in_array($_GET['status'] ?? '', LEAD_STATUSES, true) ? $_GET['status'] : '',
        'priority' => in_array($_GET['priority'] ?? '', ['high', 'normal'], true) ? $_GET['priority'] : '',
        'q'        => trim((string) ($_GET['q'] ?? '')),
        'from'     => preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['from'] ?? '') ? $_GET['from'] : '',
        'to'       => preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['to'] ?? '') ? $_GET['to'] : '',
    ];
    $where = [];
    $params = [];
    foreach (['type', 'status', 'priority'] as $k) {
        if ($f[$k] !== '') {
            $where[] = "$k = ?";
            $params[] = $f[$k];
        }
    }
    if ($f['q'] !== '') {
        $where[] = '(organisation LIKE ? OR contact_person LIKE ? OR email LIKE ? OR phone LIKE ?)';
        array_push($params, ...array_fill(0, 4, '%' . $f['q'] . '%'));
    }
    if ($f['from'] !== '') {
        $where[] = 'created_at >= ?';
        $params[] = $f['from'] . ' 00:00:00';
    }
    if ($f['to'] !== '') {
        $where[] = 'created_at <= ?';
        $params[] = $f['to'] . ' 23:59:59';
    }
    return [$f, $where ? 'WHERE ' . implode(' AND ', $where) : '', $params];
}

function admin_leads(): void
{
    [$f, $where, $params] = lead_filters();
    $stmt = db()->prepare("SELECT * FROM leads $where ORDER BY id DESC LIMIT 500");
    $stmt->execute($params);
    admin_view('leads', ['leads' => $stmt->fetchAll(), 'f' => $f], 'Leads');
}

function find_lead(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM leads WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

function admin_lead_view(int $id): void
{
    $lead = find_lead($id);
    if (!$lead) {
        http_response_code(404);
        admin_view('message', ['message' => 'This lead does not exist.'], 'Not found');
        return;
    }
    $docs = db()->prepare('SELECT * FROM documents WHERE lead_id = ? ORDER BY id DESC');
    $docs->execute([$id]);
    admin_view('lead', ['lead' => $lead, 'data' => json_decode($lead['data'], true) ?: [], 'docs' => $docs->fetchAll()], 'Lead #' . $id);
}

function admin_lead_update(int $id): void
{
    if (!find_lead($id)) {
        redirect(url('admin/leads'));
    }
    if (isset($_POST['delete'])) {
        db()->prepare('DELETE FROM leads WHERE id = ?')->execute([$id]);
        flash("Lead #$id deleted.");
        redirect(url('admin/leads'));
    }
    $status = in_array($_POST['status'] ?? '', LEAD_STATUSES, true) ? $_POST['status'] : 'new';
    $priority = ($_POST['priority'] ?? '') === 'high' ? 'high' : 'normal';
    $notes = mb_substr(trim((string) ($_POST['internal_notes'] ?? '')), 0, 5000);
    db()->prepare('UPDATE leads SET status = ?, priority = ?, internal_notes = ?, updated_at = ? WHERE id = ?')
        ->execute([$status, $priority, $notes, now(), $id]);
    flash('Lead updated.');
    redirect(url('admin/leads/' . $id));
}

/* ---------- Export (CSV / Excel) ---------- */

function admin_export(): never
{
    [$f, $where, $params] = lead_filters();
    $stmt = db()->prepare("SELECT * FROM leads $where ORDER BY id DESC");
    $stmt->execute($params);
    $leads = $stmt->fetchAll();

    $type = $f['type'];
    $fields = $type ? array_keys(form_schema($type)) : [];
    $head = ['ID', 'Received', 'Form', 'Status', 'Priority'];
    foreach ($fields as $name) {
        $head[] = t("forms.fields.$name.label") ?: $name;
    }
    if (!$type) {
        array_push($head, 'Organisation', 'Contact person', 'E-mail', 'Phone', 'Details');
    }
    array_push($head, 'Page language', 'Internal notes');

    $rows = [];
    foreach ($leads as $l) {
        $data = json_decode($l['data'], true) ?: [];
        $row = [$l['id'], $l['created_at'], $l['type'] === 'collection' ? 'Collection request' : 'Silver purchase', ucfirst($l['status']), ucfirst($l['priority'])];
        if ($type) {
            $readable = lead_rows($type, $data);
            foreach ($fields as $name) {
                $label = t("forms.fields.$name.label") ?: $name;
                $row[] = $readable[$label] ?? '';
            }
        } else {
            $details = [];
            foreach (lead_rows($l['type'], $data) as $k => $v) {
                $details[] = "$k: $v";
            }
            array_push($row, $l['organisation'], $l['contact_person'], $l['email'], $l['phone'], implode(' | ', $details));
        }
        array_push($row, strtoupper($l['lang']), $l['internal_notes']);
        $rows[] = $row;
    }

    $name = 'argentum-leads' . ($type ? '-' . $type : '') . '-' . date('Y-m-d');
    if (($_GET['format'] ?? 'csv') === 'xlsx' && class_exists('ZipArchive')) {
        $file = xlsx_build($head, $rows);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $name . '.xlsx"');
        header('Content-Length: ' . filesize($file));
        readfile($file);
        unlink($file);
        exit;
    }
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $name . '.csv"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel shows Arabic correctly
    fputcsv($out, $head);
    foreach ($rows as $r) {
        // Neutralise spreadsheet formulas typed into form fields
        fputcsv($out, array_map(fn ($v) => is_string($v) && preg_match('/^[=+\-@]/', $v) ? "'" . $v : $v, $r));
    }
    fclose($out);
    exit;
}

/** Minimal .xlsx writer (one sheet, bold header row, inline strings). */
function xlsx_build(array $head, array $rows): string
{
    $col = function (int $i): string {
        $s = '';
        for ($i++; $i > 0; $i = intdiv($i - 1, 26)) {
            $s = chr(65 + ($i - 1) % 26) . $s;
        }
        return $s;
    };
    $cell = function ($v, string $ref, bool $bold = false): string {
        $style = $bold ? ' s="1"' : '';
        if (is_int($v) || (is_string($v) && preg_match('/^\d{1,9}$/', $v))) {
            return '<c r="' . $ref . '"' . $style . '><v>' . $v . '</v></c>';
        }
        $v = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', (string) $v);
        return '<c r="' . $ref . '" t="inlineStr"' . $style . '><is><t xml:space="preserve">' . htmlspecialchars($v, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</t></is></c>';
    };
    $xmlRows = '';
    foreach (array_merge([$head], $rows) as $r => $row) {
        $xmlRows .= '<row r="' . ($r + 1) . '">';
        foreach (array_values($row) as $c => $v) {
            $xmlRows .= $cell($v, $col($c) . ($r + 1), $r === 0);
        }
        $xmlRows .= '</row>';
    }
    $cols = '';
    foreach ($head as $c => $h) {
        $w = $c === 0 ? 8 : (in_array($h, ['Details', 'Notes', 'Internal notes'], true) ? 60 : 22);
        $cols .= '<col min="' . ($c + 1) . '" max="' . ($c + 1) . '" width="' . $w . '" customWidth="1"/>';
    }
    $sheet = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
        . '<sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>'
        . '<cols>' . $cols . '</cols><sheetData>' . $xmlRows . '</sheetData></worksheet>';

    $file = tempnam(sys_get_temp_dir(), 'xlsx');
    $zip = new ZipArchive();
    $zip->open($file, ZipArchive::OVERWRITE);
    $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/></Types>');
    $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
    $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Leads" sheetId="1" r:id="rId1"/></sheets></workbook>');
    $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>');
    $zip->addFromString('xl/styles.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="11"/><name val="Calibri"/></font></fonts><fills count="2"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill></fills><borders count="1"><border/></borders><cellStyleXfs count="1"><xf/></cellStyleXfs><cellXfs count="2"><xf xfId="0"/><xf xfId="0" fontId="1" applyFont="1"/></cellXfs></styleSheet>');
    $zip->addFromString('xl/worksheets/sheet1.xml', $sheet);
    $zip->close();
    return $file;
}

/* ---------- Documents (certificates of destruction, assay reports) ---------- */

function admin_documents(): void
{
    $docs = db()->query('SELECT * FROM documents ORDER BY id DESC LIMIT 500')->fetchAll();
    admin_view('documents', ['docs' => $docs], 'Documents');
}

function admin_document_form(array $errors = [], array $old = []): void
{
    $lead = isset($_GET['lead']) ? find_lead((int) $_GET['lead']) : null;
    if ($lead && !$old) {
        $data = json_decode($lead['data'], true) ?: [];
        $old = [
            'kind'         => $lead['type'] === 'silver' ? 'assay' : 'destruction',
            'organisation' => $data['certificate_name'] ?? $lead['organisation'],
            'lead_id'      => $lead['id'],
        ];
    }
    admin_view('document-form', ['errors' => $errors, 'old' => $old + ['kind' => 'destruction', 'issued_on' => date('Y-m-d')]], 'Issue a document');
}

function admin_document_create(): void
{
    $in = [
        'kind'         => array_key_exists($_POST['kind'] ?? '', DOC_KINDS) ? $_POST['kind'] : '',
        'organisation' => trim((string) ($_POST['organisation'] ?? '')),
        'issued_on'    => (string) ($_POST['issued_on'] ?? ''),
        'weight_kg'    => trim((string) ($_POST['weight_kg'] ?? '')),
        'purity'       => trim((string) ($_POST['purity'] ?? '')),
        'reference'    => trim((string) ($_POST['reference'] ?? '')),
        'notes'        => trim((string) ($_POST['notes'] ?? '')),
        'lead_id'      => (int) ($_POST['lead_id'] ?? 0),
    ];
    $errors = [];
    if ($in['kind'] === '') {
        $errors['kind'] = 'Choose the document type.';
    }
    if ($in['organisation'] === '' || mb_strlen($in['organisation']) > 160) {
        $errors['organisation'] = 'Enter who the document is issued to (160 characters max).';
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $in['issued_on'])) {
        $errors['issued_on'] = 'Enter the issue date.';
    }
    foreach (['weight_kg' => [0, 1000000], 'purity' => [0, 100]] as $k => [$min, $max]) {
        if ($in[$k] !== '' && (!is_numeric($in[$k]) || $in[$k] < $min || $in[$k] > $max)) {
            $errors[$k] = "Enter a number between $min and $max.";
        }
    }
    if ($errors) {
        admin_document_form($errors, $in);
        return;
    }
    $code = new_document_code($in['kind']);
    db()->prepare('INSERT INTO documents (code, kind, organisation, issued_on, weight_kg, purity, reference, notes, lead_id, created_at) VALUES (?,?,?,?,?,?,?,?,?,?)')
        ->execute([
            $code, $in['kind'], $in['organisation'], $in['issued_on'],
            $in['weight_kg'] === '' ? null : (float) $in['weight_kg'],
            $in['purity'] === '' ? null : (float) $in['purity'],
            mb_substr($in['reference'], 0, 80), mb_substr($in['notes'], 0, 1000),
            $in['lead_id'] && find_lead($in['lead_id']) ? $in['lead_id'] : null, now(),
        ]);
    flash("Document $code issued. Print it, sign it and hand it over.");
    redirect(url('admin/documents/' . db()->lastInsertId() . '/print'));
}

function admin_document_print(int $id): void
{
    $stmt = db()->prepare('SELECT * FROM documents WHERE id = ?');
    $stmt->execute([$id]);
    $doc = $stmt->fetch();
    if (!$doc) {
        redirect(url('admin/documents'));
    }
    set_lang(($_GET['lang'] ?? 'en') === 'ar' ? 'ar' : 'en');
    header('Content-Type: text/html; charset=utf-8');
    echo render('admin/document-print', ['doc' => $doc, 'flash' => flash()]);
}

/* ---------- Site text ---------- */

/** Flattens nested copy into 'a.b.0.c' => 'text' pairs. */
function flatten(array $data, string $prefix = ''): array
{
    $out = [];
    foreach ($data as $k => $v) {
        $key = $prefix === '' ? (string) $k : $prefix . '.' . $k;
        if (is_array($v)) {
            $out += flatten($v, $key);
        } else {
            $out[$key] = (string) $v;
        }
    }
    return $out;
}

function set_path(array &$data, string $path, string $value): void
{
    $ref = &$data;
    foreach (explode('.', $path) as $part) {
        if (!is_array($ref) || !array_key_exists($part, $ref)) {
            return; // only existing keys can be edited
        }
        $ref = &$ref[$part];
    }
    if (is_string($ref)) {
        $ref = $value;
    }
}

function admin_content(): void
{
    $en = load_json(CONTENT_DIR . '/en.json');
    $sections = array_keys($en);
    $section = in_array($_GET['section'] ?? '', $sections, true) ? $_GET['section'] : 'home';
    admin_view('content', [
        'sections' => $sections,
        'section'  => $section,
        'en'       => flatten($en[$section] ?? [], $section),
        'ar'       => flatten(load_json(CONTENT_DIR . '/ar.json')[$section] ?? [], $section),
    ], 'Website text');
}

function admin_content_save(): void
{
    $section = (string) ($_POST['section'] ?? 'home');
    foreach (LANGS as $lang) {
        $file = CONTENT_DIR . '/' . $lang . '.json';
        $data = load_json($file);
        foreach ((array) ($_POST[$lang] ?? []) as $path => $value) {
            if (!str_starts_with((string) $path, $section . '.') && $path !== $section) {
                continue;
            }
            $value = str_replace("\r\n", "\n", mb_substr(trim((string) $value), 0, 5000));
            set_path($data, (string) $path, $value);
        }
        @copy($file, STORAGE_DIR . '/backup-' . $lang . '-' . date('Ymd-His') . '.json');
        save_json($file, $data);
    }
    flash('Text saved. Changes are live on the website.');
    redirect(url('admin/content?section=' . rawurlencode($section)));
}

/* ---------- Settings ---------- */

function admin_settings_save(): void
{
    $s = settings();
    $str = fn (string $k, int $max = 300) => mb_substr(trim((string) ($_POST[$k] ?? '')), 0, $max);
    $errors = [];

    foreach (['email', 'phone', 'whatsapp', 'address_en', 'address_ar', 'hours_en', 'hours_ar', 'maps_url', 'maps_embed', 'linkedin', 'facebook', 'instagram'] as $k) {
        $s['contact'][$k] = $str('contact_' . $k, 1000);
    }
    foreach (['registration_en', 'registration_ar', 'permits_en', 'permits_ar'] as $k) {
        $s['company'][$k] = $str('company_' . $k, 300);
    }
    if ($s['contact']['email'] !== '' && !filter_var($s['contact']['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'The public e-mail address is not valid.';
    }
    foreach (['maps_url', 'linkedin', 'facebook', 'instagram'] as $k) {
        if ($s['contact'][$k] !== '' && !preg_match('#^https://#', $s['contact'][$k])) {
            $errors[] = "The $k link must start with https://";
        }
    }
    if ($s['contact']['maps_embed'] !== '' && !str_starts_with($s['contact']['maps_embed'], 'https://www.google.com/maps/embed')) {
        $errors[] = 'The map embed link must start with https://www.google.com/maps/embed (Google Maps → Share → Embed a map → copy the src).';
    }

    $s['counters']['mode'] = ($_POST['counters_mode'] ?? '') === 'totals' ? 'totals' : 'promise';
    foreach (['promise', 'totals'] as $set) {
        for ($i = 0; $i < 4; $i++) {
            $v = str_replace(',', '', (string) ($_POST["counters_{$set}_$i"] ?? '0'));
            $s['counters'][$set][$i] = is_numeric($v) ? 0 + $v : 0;
        }
    }

    foreach (['lead_email', 'collection_emails', 'silver_emails'] as $k) {
        $list = array_filter(array_map('trim', explode(',', $str('notifications_' . $k, 500))));
        foreach ($list as $addr) {
            if (!filter_var($addr, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "“$addr” is not a valid e-mail address.";
            }
        }
        $s['notifications'][$k] = implode(', ', $list);
    }
    $s['notifications']['whatsapp_numbers'] = $str('notifications_whatsapp_numbers', 200);
    $s['notifications']['auto_reply'] = !empty($_POST['notifications_auto_reply']);

    $s['analytics']['ga4_id'] = strtoupper($str('analytics_ga4_id', 30));
    if ($s['analytics']['ga4_id'] !== '' && !preg_match('/^G-[A-Z0-9]{4,}$/', $s['analytics']['ga4_id'])) {
        $errors[] = 'The Google Analytics ID looks like G-XXXXXXXXXX.';
    }
    $gsc = $str('analytics_gsc_verification', 300);
    if (preg_match('/content="([^"]+)"/', $gsc, $m)) {
        $gsc = $m[1]; // accept the full <meta> tag Google gives
    }
    $s['analytics']['gsc_verification'] = preg_replace('/[^A-Za-z0-9_\-]/', '', $gsc);

    if ($errors) {
        admin_view('settings', ['errors' => $errors, 'draft' => $s], 'Settings');
        return;
    }
    save_json(CONTENT_DIR . '/site.json', $s);
    flash('Settings saved.');
    redirect(url('admin/settings'));
}

/* ---------- Photos ---------- */

function admin_media_save(): void
{
    $slot = (string) ($_POST['slot'] ?? '');
    if (!array_key_exists($slot, MEDIA_SLOTS)) {
        redirect(url('admin/media'));
    }
    $s = settings();
    $current = $s['media'][$slot] ?? null;

    if (isset($_POST['remove'])) {
        foreach (['file', 'webp'] as $k) {
            if (!empty($current[$k]) && is_file(UPLOADS_DIR . '/' . $current[$k])) {
                unlink(UPLOADS_DIR . '/' . $current[$k]);
            }
        }
        unset($s['media'][$slot]);
        save_json(CONTENT_DIR . '/site.json', $s);
        flash('Photo removed. The illustration is shown again.');
        redirect(url('admin/media'));
    }

    $alt = ['alt_en' => mb_substr(trim((string) ($_POST['alt_en'] ?? '')), 0, 200), 'alt_ar' => mb_substr(trim((string) ($_POST['alt_ar'] ?? '')), 0, 200)];
    $upload = $_FILES['photo'] ?? null;
    if ($upload && $upload['error'] === UPLOAD_ERR_OK) {
        try {
            $saved = store_photo($upload['tmp_name'], $slot);
        } catch (Throwable $e) {
            flash('Upload failed: ' . $e->getMessage());
            redirect(url('admin/media'));
        }
        foreach (['file', 'webp'] as $k) {
            if (!empty($current[$k]) && is_file(UPLOADS_DIR . '/' . $current[$k])) {
                unlink(UPLOADS_DIR . '/' . $current[$k]);
            }
        }
        $s['media'][$slot] = $saved + $alt;
    } elseif ($current) {
        $s['media'][$slot] = $alt + $current;
    } else {
        flash('Choose a photo to upload.');
        redirect(url('admin/media'));
    }
    save_json(CONTENT_DIR . '/site.json', $s);
    flash('Photo saved.');
    redirect(url('admin/media'));
}

/**
 * Re-encodes an uploaded image (strips metadata such as GPS), resizes it to
 * 2000 px max and writes a WebP copy for faster loading.
 */
function store_photo(string $tmp, string $slot): array
{
    if (filesize($tmp) > 8 * 1024 * 1024) {
        throw new RuntimeException('the file is larger than 8 MB.');
    }
    $info = @getimagesize($tmp);
    $types = [IMAGETYPE_JPEG => 'imagecreatefromjpeg', IMAGETYPE_PNG => 'imagecreatefrompng', IMAGETYPE_WEBP => 'imagecreatefromwebp'];
    if (!$info || !isset($types[$info[2]]) || !function_exists($types[$info[2]])) {
        throw new RuntimeException('use a JPG, PNG or WebP image.');
    }
    $src = $types[$info[2]]($tmp);
    if (!$src) {
        throw new RuntimeException('the image could not be read.');
    }
    if ($info[2] === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
        $o = (int) (@exif_read_data($tmp)['Orientation'] ?? 1);
        $src = match ($o) { 3 => imagerotate($src, 180, 0), 6 => imagerotate($src, -90, 0), 8 => imagerotate($src, 90, 0), default => $src };
    }
    $w = imagesx($src);
    $h = imagesy($src);
    $scale = min(1, 2000 / max($w, $h));
    $nw = (int) round($w * $scale);
    $nh = (int) round($h * $scale);
    $dst = imagecreatetruecolor($nw, $nh);
    imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255));
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
    $base = $slot . '-' . bin2hex(random_bytes(5));
    imagejpeg($dst, UPLOADS_DIR . '/' . $base . '.jpg', 82);
    $webp = function_exists('imagewebp') && imagewebp($dst, UPLOADS_DIR . '/' . $base . '.webp', 78) ? $base . '.webp' : '';
    return ['file' => $base . '.jpg', 'webp' => $webp, 'w' => $nw, 'h' => $nh];
}
