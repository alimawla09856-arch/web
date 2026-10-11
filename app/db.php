<?php
/**
 * SQLite storage for leads, issued documents and rate limits.
 * The database file lives in /storage (blocked from the web) and is created
 * automatically on first use — nothing to set up in cPanel.
 */
defined('ARGENTUM') || exit;

function db(): PDO
{
    static $pdo = null;
    if ($pdo) {
        return $pdo;
    }
    $pdo = new PDO('sqlite:' . STORAGE_DIR . '/argentum.sqlite', null, null, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec('PRAGMA journal_mode = WAL; PRAGMA foreign_keys = ON; PRAGMA busy_timeout = 4000;');
    migrate($pdo);
    return $pdo;
}

function migrate(PDO $pdo): void
{
    $version = (int) $pdo->query('PRAGMA user_version')->fetchColumn();
    if ($version < 1) {
        $pdo->exec(<<<SQL
            CREATE TABLE IF NOT EXISTS leads (
                id             INTEGER PRIMARY KEY AUTOINCREMENT,
                type           TEXT NOT NULL,              -- collection | silver
                status         TEXT NOT NULL DEFAULT 'new',
                priority       TEXT NOT NULL DEFAULT 'normal',
                lang           TEXT NOT NULL DEFAULT 'en',
                organisation   TEXT,
                contact_person TEXT,
                email          TEXT,
                phone          TEXT,
                region         TEXT,
                quantity       TEXT,
                data           TEXT NOT NULL,              -- every submitted field, JSON
                internal_notes TEXT NOT NULL DEFAULT '',
                source         TEXT,
                notified       TEXT NOT NULL DEFAULT '',
                created_at     TEXT NOT NULL,
                updated_at     TEXT NOT NULL
            );
            CREATE INDEX IF NOT EXISTS leads_type_status ON leads(type, status);
            CREATE INDEX IF NOT EXISTS leads_created ON leads(created_at);

            CREATE TABLE IF NOT EXISTS documents (
                id           INTEGER PRIMARY KEY AUTOINCREMENT,
                code         TEXT NOT NULL UNIQUE,
                kind         TEXT NOT NULL,                -- destruction | assay
                organisation TEXT NOT NULL DEFAULT '',
                issued_on    TEXT NOT NULL,
                weight_kg    REAL,
                purity       REAL,
                reference    TEXT NOT NULL DEFAULT '',
                notes        TEXT NOT NULL DEFAULT '',
                lead_id      INTEGER REFERENCES leads(id) ON DELETE SET NULL,
                revoked      INTEGER NOT NULL DEFAULT 0,
                created_at   TEXT NOT NULL
            );

            CREATE TABLE IF NOT EXISTS hits (
                bucket TEXT NOT NULL,
                at     INTEGER NOT NULL
            );
            CREATE INDEX IF NOT EXISTS hits_bucket ON hits(bucket, at);
            PRAGMA user_version = 1;
        SQL);
    }
}

function now(): string
{
    return date('Y-m-d H:i:s');
}

/**
 * Simple sliding-window limiter. Returns false once $max hits were recorded
 * for $bucket during the last $seconds.
 */
function rate_limit(string $bucket, int $max, int $seconds): bool
{
    $pdo = db();
    $since = time() - $seconds;
    $pdo->prepare('DELETE FROM hits WHERE at < ?')->execute([time() - 86400]);
    $count = $pdo->prepare('SELECT COUNT(*) FROM hits WHERE bucket = ? AND at >= ?');
    $count->execute([$bucket, $since]);
    if ((int) $count->fetchColumn() >= $max) {
        return false;
    }
    $pdo->prepare('INSERT INTO hits (bucket, at) VALUES (?, ?)')->execute([$bucket, time()]);
    return true;
}
