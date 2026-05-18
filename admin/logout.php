<?php
/**
 * Admin Logout
 */
session_start();
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
