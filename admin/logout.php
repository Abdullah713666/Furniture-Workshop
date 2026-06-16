<?php
/**
 * Admin Logout â€” Antique Furniture Workshop
 */
require_once __DIR__ . '/../config/init.php';

// Unset all session variables
$_SESSION = [];

// Destroy the session cookie on the client browser
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy session
session_destroy();
?>
<!DOCTYPE html>
<html>
<head><title>Logging out...</title></head>
<body>
<script>
    // Clear the tab-scoped admin session token
    sessionStorage.removeItem('adminTabToken');
    window.location.href = 'login.php';
</script>
</body>
</html>
