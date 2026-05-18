<?php
/**
 * Database Configuration — Antique Furniture Workshop
 * 
 * Uses PDO for secure database connections.
 * Update the credentials below to match your MySQL setup.
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'antique_workshop');
define('DB_USER', 'root');        // Default XAMPP user
define('DB_PASS', '');            // Default XAMPP password (empty)
define('DB_CHARSET', 'utf8mb4');

/**
 * Get a PDO database connection
 * @return PDO
 */
function getDB() {
    static $pdo = null;
    
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
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

// Start session for admin auth (only if not already started)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
