<?php
/**
 * Database Configuration — Antique Furniture Workshop
 * Uses environment variables in hosted environments and safe local defaults for XAMPP.
 */

if (getenv('MYSQLHOST')) {
    define('DB_HOST', getenv('MYSQLHOST'));
    define('DB_NAME', getenv('MYSQLDATABASE') ?: 'antique_workshop');
    define('DB_USER', getenv('MYSQLUSER') ?: 'root');
    define('DB_PASS', getenv('MYSQLPASSWORD') ?: getenv('MYSQL_ROOT_PASSWORD') ?: '');
    define('DB_PORT', getenv('MYSQLPORT') ?: '3306');
} elseif (getenv('DB_HOST')) {
    define('DB_HOST', getenv('DB_HOST'));
    define('DB_NAME', getenv('DB_NAME') ?: 'antique_workshop');
    define('DB_USER', getenv('DB_USER') ?: 'root');
    define('DB_PASS', getenv('DB_PASS') ?: '');
    define('DB_PORT', getenv('DB_PORT') ?: '3306');
} else {
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'antique_workshop');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_PORT', '3306');
}

define('DB_CHARSET', 'utf8mb4');

function getDB() {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log('Antique Workshop DB Error: ' . $e->getMessage());
            die('Database connection failed. Please make sure MySQL is running and the database has been set up.');
        }

        // Keep data migrations out of the connection layer. Schema/data changes belong in
        // database.sql or an explicitly run migration so opening the site has no side effects.
    }

    return $pdo;
}

function getSetting($key, $default = '') {
    try {
        $db = getDB();
        $stmt = $db->prepare('SELECT setting_value FROM site_settings WHERE setting_key = ?');
        $stmt->execute([$key]);
        $result = $stmt->fetch();
        return $result ? $result['setting_value'] : $default;
    } catch (Exception $e) {
        return $default;
    }
}
