<?php
/**
 * User Auth Helper — Antique Furniture Workshop
 * Include this in public pages that require user login.
 */
require_once __DIR__ . '/../config/database.php';

/**
 * Redirect to login if user is not logged in
 */
function requireUserLogin() {
    if (!isUserLoggedIn()) {
        header('Location: user_login.php');
        exit;
    }
}

/**
 * Check if a user is logged in
 */
function isUserLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Get the current logged-in user's data
 */
function getCurrentUser() {
    if (!isUserLoggedIn()) return null;
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT id, full_name, email, is_active, created_at FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    } catch (Exception $e) {
        return null;
    }
}
