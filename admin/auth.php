<?php
/**
 * Admin Auth Helper
 * Include this at the top of every admin page (except login.php)
 */
require_once __DIR__ . '/../config/init.php';

function requireLogin() {
    if (!isset($_SESSION['admin_id'])) {
        header('Location: login.php');
        exit;
    }
    // Session inactivity timeout (30 minutes)
    $timeout = 1800;
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
        $_SESSION = [];
        session_destroy();
        header('Location: login.php?timeout=1');
        exit;
    }
    $_SESSION['last_activity'] = time();
}

function isLoggedIn() {
    return isset($_SESSION['admin_id']);
}

/**
 * Self-heal: ensure email/verification/reset columns exist on admin_users.
 * setup.php is blocked on Railway, so this guarantees the schema on first hit.
 * Result is cached per-request so the SHOW COLUMNS check runs at most once.
 */
function afw_ensure_auth_schema(): void {
    static $checked = false;
    if ($checked) return;
    $checked = true;
    try {
        $db = getDB();
        $col = $db->query("SHOW COLUMNS FROM admin_users LIKE 'email'")->fetch();
        if (!$col) {
            $db->exec("ALTER TABLE admin_users ADD COLUMN email              VARCHAR(255) DEFAULT NULL  AFTER username");
            $db->exec("ALTER TABLE admin_users ADD COLUMN email_verified     TINYINT(1)   NOT NULL DEFAULT 0 AFTER email");
            $db->exec("ALTER TABLE admin_users ADD COLUMN verification_token VARCHAR(64)  DEFAULT NULL  AFTER email_verified");
            $db->exec("ALTER TABLE admin_users ADD COLUMN reset_token        VARCHAR(64)  DEFAULT NULL  AFTER verification_token");
            $db->exec("ALTER TABLE admin_users ADD COLUMN reset_expires      DATETIME     DEFAULT NULL  AFTER reset_token");
        }
    } catch (Exception $e) {
        // ignore â€” if columns truly missing the downstream query will surface a clearer error
    }
}
