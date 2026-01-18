<?php
// Database connection helper (uses test DB when TESTING=1).

$config = require __DIR__ . '/config.php';

function db(): PDO
{
    static $pdo = null;
    global $config;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    // During tests, use an in-memory SQLite DB for fast, isolated tests.
    if (getenv('TESTING') === '1') {
        $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        // Create minimal tables needed for unit tests
        if (method_exists($pdo, 'sqliteCreateFunction')) {
            $pdo->sqliteCreateFunction('NOW', fn () => date('Y-m-d H:i:s'), 0);
        }
        $pdo->exec("CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL, email TEXT NOT NULL, password_hash TEXT NOT NULL, created_at DATETIME);");
        $pdo->exec("CREATE TABLE auth_tokens (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER NOT NULL, token TEXT NOT NULL, expires_at DATETIME, created_at DATETIME);");
        $pdo->exec("CREATE TABLE clients (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER NOT NULL, full_name TEXT NOT NULL, email TEXT, phone TEXT, company TEXT, position TEXT, created_at DATETIME);");
        $pdo->exec("CREATE TABLE leads (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER NOT NULL, owner_id INTEGER, property_for TEXT, payment_option TEXT, interested_property TEXT, area TEXT, name TEXT NOT NULL, email TEXT, phone TEXT, status TEXT NOT NULL DEFAULT 'new', source TEXT, budget REAL, currency TEXT, notes TEXT, last_contact_at DATE, created_at DATETIME, archived_at DATETIME);");
        return $pdo;
    }

    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=%s',
        $config['db']['host'],
        $config['db']['port'],
        $config['db']['name'],
        $config['db']['charset']
    );

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_PERSISTENT => false,
    ];

    $pdo = new PDO($dsn, $config['db']['user'], $config['db']['pass'], $options);
    return $pdo;
}
