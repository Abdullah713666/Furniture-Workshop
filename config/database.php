<?php
/**
 * Database Configuration — Antique Furniture Workshop
 * 
 * Uses PDO for secure database connections.
 * Update the credentials below to match your MySQL setup.
 */

// Detect environment: Railway / InfinityFree / local XAMPP
if (getenv('MYSQLHOST')) {
    // Railway — uses built-in MySQL service env vars
    define('DB_HOST', getenv('MYSQLHOST'));
    define('DB_NAME', getenv('MYSQLDATABASE') ?: 'antique_workshop');
    define('DB_USER', getenv('MYSQLUSER') ?: 'root');
    define('DB_PASS', getenv('MYSQLPASSWORD') ?: getenv('MYSQL_ROOT_PASSWORD') ?: '');
    define('DB_PORT', getenv('MYSQLPORT') ?: '3306');
} elseif (strpos($_SERVER['HTTP_HOST'] ?? '', 'infinityfree.me') !== false) {
    // Live (InfinityFree)
    define('DB_HOST', 'sql307.infinityfree.com');
    define('DB_NAME', 'if0_41826537_antique_workshop');
    define('DB_USER', 'if0_41826537');
    define('DB_PASS', 'nuv9mkHqFvceKMR');
    define('DB_PORT', '3306');
} else {
    // Local (XAMPP)
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'antique_workshop');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_PORT', '3306');
}
define('DB_CHARSET', 'utf8mb4');

/**
 * Get a PDO database connection
 * @return PDO
 */
function getDB() {
    static $pdo = null;
    
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("Antique Workshop DB Error: " . $e->getMessage());
            die("Database connection failed. Please make sure MySQL is running and the database has been set up.");
        }
    }
    
    return $pdo;
}

/**
 * Get a site setting value by key
 * @param string $key
 * @param string $default
 * @return string
 */
function getSetting($key, $default = '') {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT setting_value FROM site_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch();
        return $result ? $result['setting_value'] : $default;
    } catch (Exception $e) {
        return $default;
    }
}

