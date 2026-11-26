<?php
// PDO provides a safe, consistent way to communicate with MySQL/MariaDB.
function db(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    try {
        $port = defined('DB_PORT') ? DB_PORT : '3306';
        $dsn = 'mysql:host=' . DB_HOST . ';port=' . $port . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        return $pdo;
    } catch (PDOException $exception) {
        error_log('Database connection failed: ' . $exception->getMessage());
        if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
            http_response_code(503); exit('Database unavailable. Configure DB_HOST, DB_NAME, DB_USER, DB_PASS and DB_PORT in Vercel.');
        }
        $pdo = new PDO(
            'sqlite:' . sys_get_temp_dir() . '/traffic_safety_demo.sqlite',
            null,
            null,
            [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]
        );
        $pdo->exec("CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY AUTOINCREMENT, full_name TEXT NOT NULL, email TEXT NOT NULL UNIQUE, password_hash TEXT NOT NULL, role TEXT NOT NULL DEFAULT 'citizen', created_at TEXT DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS reports (id INTEGER PRIMARY KEY AUTOINCREMENT, reference_no TEXT NOT NULL UNIQUE, reporter_name TEXT NOT NULL, reporter_email TEXT NOT NULL, vehicle_number TEXT NOT NULL, violation_type TEXT NOT NULL, location_text TEXT NOT NULL, incident_at TEXT NULL, latitude REAL NULL, longitude REAL NULL, citizen_id INTEGER NULL, description TEXT NOT NULL, evidence_path TEXT NULL, status TEXT NOT NULL DEFAULT 'Submitted', officer_notes TEXT NULL, penalty_amount REAL NULL, created_at TEXT DEFAULT CURRENT_TIMESTAMP, updated_at TEXT DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS report_status_history (id INTEGER PRIMARY KEY AUTOINCREMENT, report_id INTEGER NOT NULL, old_status TEXT NULL, new_status TEXT NOT NULL, notes TEXT NULL, changed_by INTEGER NULL, changed_at TEXT DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS offence_rules (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL UNIQUE, description TEXT NULL, category TEXT NOT NULL DEFAULT 'Minor', default_penalty REAL NOT NULL DEFAULT 0, active INTEGER NOT NULL DEFAULT 1, created_at TEXT DEFAULT CURRENT_TIMESTAMP, updated_at TEXT DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS challans (id INTEGER PRIMARY KEY AUTOINCREMENT, challan_no TEXT NOT NULL UNIQUE, report_id INTEGER NOT NULL, rule_id INTEGER NULL, citizen_id INTEGER NULL, vehicle_number TEXT NOT NULL, offence_name TEXT NOT NULL, amount REAL NOT NULL DEFAULT 0, status TEXT NOT NULL DEFAULT 'Draft', issued_at TEXT NULL, due_at TEXT NULL, notice_text TEXT NULL, created_by INTEGER NULL, created_at TEXT DEFAULT CURRENT_TIMESTAMP, updated_at TEXT DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS notifications (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER NOT NULL, report_id INTEGER NULL, challan_id INTEGER NULL, title TEXT NOT NULL, message TEXT NOT NULL, link TEXT NULL, is_read INTEGER NOT NULL DEFAULT 0, created_at TEXT DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS audit_logs (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER NULL, actor_role TEXT NULL, action TEXT NOT NULL, entity_type TEXT NOT NULL, entity_id TEXT NULL, details TEXT NULL, ip_address TEXT NULL, created_at TEXT DEFAULT CURRENT_TIMESTAMP);");
        $count=(int)$pdo->query('SELECT COUNT(*) FROM offence_rules')->fetchColumn();
        if (!$count) $pdo->exec("INSERT INTO offence_rules(name,category,default_penalty,active) VALUES ('Speeding','Major',1500,1),('Red-light violation','Major',2000,1),('Wrong-side driving','Severe',2500,1),('Illegal parking','Minor',500,1),('Helmet / seat-belt violation','Major',1000,1),('Using a mobile phone while driving','Major',1500,1),('Blocking a pedestrian crossing','Minor',700,1),('Dangerous / reckless driving','Severe',3000,1),('Other','Minor',0,1);");
        return $pdo;
    }
}
