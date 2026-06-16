<?php
/**
 * Database Configuration â€” Antique Furniture Workshop
 * 
 * Uses PDO for secure database connections.
 * Update the credentials below to match your MySQL setup.
 */

// Detect environment: Railway / InfinityFree / local XAMPP
if (getenv('MYSQLHOST')) {
    // Railway â€” uses built-in MySQL service env vars
    define('DB_HOST', getenv('MYSQLHOST'));
    define('DB_NAME', getenv('MYSQLDATABASE') ?: 'antique_workshop');
    define('DB_USER', getenv('MYSQLUSER') ?: 'root');
    define('DB_PASS', getenv('MYSQLPASSWORD') ?: getenv('MYSQL_ROOT_PASSWORD') ?: '');
    define('DB_PORT', getenv('MYSQLPORT') ?: '3306');
} elseif (getenv('DB_HOST')) {
    // Production (env vars â€” set on InfinityFree, Railway, or any host)
    define('DB_HOST', getenv('DB_HOST'));
    define('DB_NAME', getenv('DB_NAME') ?: 'antique_workshop');
    define('DB_USER', getenv('DB_USER') ?: 'root');
    define('DB_PASS', getenv('DB_PASS') ?: '');
    define('DB_PORT', getenv('DB_PORT') ?: '3306');
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

        // Self-heal: migrate .png image paths to .jpg (images were converted)
        try {
            $pdo->exec("UPDATE `gallery_items` SET `image_path` = REPLACE(`image_path`, '.png', '.jpg') WHERE `image_path` LIKE '%.png'");
            $pdo->exec("UPDATE `services` SET `image_path` = REPLACE(`image_path`, '.png', '.jpg') WHERE `image_path` LIKE '%.png'");
        } catch (Exception $e) {
            // Ignore — table may not exist yet on fresh install
        }

        // Self-heal: update contact info defaults
        try {
            $pdo->exec("UPDATE `site_settings` SET `setting_value` = 'abdulla257893989@gmail.com' WHERE `setting_key` = 'email' AND `setting_value` = 'hello@antiqueworkshop.com'");
            $pdo->exec("UPDATE `site_settings` SET `setting_value` = 'Sargodha' WHERE `setting_key` = 'address_line1' AND `setting_value` = '123 Heritage Lane'");
            $pdo->exec("UPDATE `site_settings` SET `setting_value` = 'Punjab, Pakistan' WHERE `setting_key` = 'address_line2' AND `setting_value` = 'Craftsmanship City, CA 90210'");
            $pdo->exec("UPDATE `site_settings` SET `setting_value` = '' WHERE `setting_key` = 'phone' AND `setting_value` != ''");
            $pdo->exec("UPDATE `site_settings` SET `setting_value` = 'https://www.openstreetmap.org/export/embed.html?bbox=72.65%2C32.07%2C72.71%2C32.10&layer=mapnik&marker=32.0826%2C72.6796' WHERE `setting_key` = 'map_embed_url'");
            $pdo->exec("UPDATE `site_settings` SET `setting_value` = 'Mon-Sat, 9am - 6pm' WHERE `setting_key` = 'working_hours'");
        } catch (Exception $e) {
            // Ignore
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

