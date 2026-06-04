<?php
/**
 * ONE-TIME: Fix admin password hash to 'admin'
 * DELETE THIS FILE after running it once!
 */
require_once __DIR__ . '/auth.php';
$db = getDB();

$new_hash = password_hash('admin', PASSWORD_BCRYPT);
$stmt = $db->prepare("UPDATE admin_users SET password_hash = ? WHERE username = 'admin'");
$stmt->execute([$new_hash]);

echo "<h2>Done.</h2>";
echo "<p>Admin password hash updated to match <code>admin</code>.</p>";
echo "<p><strong>DELETE THIS FILE immediately!</strong></p>";
echo "<p><a href='login.php'>→ Go to login</a></p>";
