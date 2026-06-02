<?php
/**
 * Application Initialization — Antique Furniture Workshop
 * 
 * Loads database config, sets security headers, and starts the session.
 * Include this instead of config/database.php when you need session/headers.
 */
require_once __DIR__ . '/database.php';

// Set global security headers to protect against clickjacking, MIME sniffing, and XSS
if (!headers_sent()) {
    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
}

// Start session for admin/user auth with hardened cookie settings (HttpOnly, SameSite, Secure)
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    session_start();
}
