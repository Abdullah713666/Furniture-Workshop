<?php
/**
 * Application Initialization — Antique Furniture Workshop
 *
 * Loads database config, sets security headers, and starts the session.
 * Include this instead of config/database.php when you need session/headers.
 */

// Start session FIRST — before any output or includes
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

require_once __DIR__ . '/database.php';

// Set global security headers to protect against clickjacking, MIME sniffing, and XSS
if (!headers_sent()) {
    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://www.google.com https://www.gstatic.com https://cdnjs.cloudflare.com https://unpkg.com; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' https://fonts.gstatic.com; connect-src 'self' https://www.google.com; frame-src https://www.google.com https://www.openstreetmap.org; frame-ancestors 'none'");
}

// --- Brute-force lockout helpers (IP-based, file-backed) ---
define('BF_MAX_ATTEMPTS', 5);
define('BF_LOCKOUT_SECONDS', 900); // 15 minutes
define('BF_DIR', __DIR__ . '/../data');

function bf_lockout_file(string $ip): string {
    return BF_DIR . '/bf_' . md5($ip) . '.json';
}

function bf_record_failed(string $ip): int {
    if (!is_dir(BF_DIR)) @mkdir(BF_DIR, 0750, true);
    $file = bf_lockout_file($ip);
    $data = ['attempts' => 0, 'lockout_until' => 0];
    if (is_file($file)) {
        $raw = @file_get_contents($file);
        $data = json_decode($raw, true) ?: $data;
    }
    if (isset($data['lockout_until']) && $data['lockout_until'] > time()) {
        return 0;
    }
    $data['attempts'] = ($data['attempts'] ?? 0) + 1;
    if ($data['attempts'] >= BF_MAX_ATTEMPTS) {
        $data['lockout_until'] = time() + BF_LOCKOUT_SECONDS;
    }
    @file_put_contents($file, json_encode($data), LOCK_EX);
    return max(0, BF_MAX_ATTEMPTS - $data['attempts']);
}

function bf_is_locked(string $ip): bool {
    $file = bf_lockout_file($ip);
    if (!is_file($file)) return false;
    $data = json_decode(@file_get_contents($file), true);
    return is_array($data) && ($data['lockout_until'] ?? 0) > time();
}

function bf_reset(string $ip): void {
    $file = bf_lockout_file($ip);
    if (is_file($file)) @unlink($file);
}

function bf_remaining_minutes(string $ip): int {
    $file = bf_lockout_file($ip);
    if (!is_file($file)) return 0;
    $data = json_decode(@file_get_contents($file), true);
    if (!is_array($data) || ($data['lockout_until'] ?? 0) <= time()) return 0;
    return (int) ceil(($data['lockout_until'] - time()) / 60);
}

function bf_get_ip(): string {
    return $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
}

// --- Input validation helper ---
function validate_length(string $value, int $max, string $field_name): ?string {
    if (mb_strlen($value) > $max) {
        return htmlspecialchars($field_name) . ' must be ' . $max . ' characters or fewer.';
    }
    return null;
}
