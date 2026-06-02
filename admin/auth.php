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
