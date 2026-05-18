<?php
/**
 * Admin Auth Helper
 * Include this at the top of every admin page (except login.php)
 */
require_once __DIR__ . '/../config/database.php';

function requireLogin() {
    if (!isset($_SESSION['admin_id'])) {
        header('Location: login.php');
        exit;
    }
}

function isLoggedIn() {
    return isset($_SESSION['admin_id']);
}
