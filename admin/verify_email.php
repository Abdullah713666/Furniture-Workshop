<?php
/**
 * Verify Email — Antique Furniture Workshop
 *
 * GET ?token=…   → consumes the verification token, flips email_verified=1, redirects to login
 */
require_once __DIR__ . '/auth.php';

$token = $_GET['token'] ?? '';
$message = '';
$message_type = '';
$verified_username = '';

afw_ensure_auth_schema();

if ($token === '') {
    $message = 'Missing verification token.';
    $message_type = 'error';
} else {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT id, username, email_verified FROM admin_users WHERE verification_token = ?");
        $stmt->execute([$token]);
        $user = $stmt->fetch();

        if (!$user) {
            $message = 'Invalid verification link.';
            $message_type = 'error';
        } elseif (intval($user['email_verified']) === 1) {
            $message = 'Your email is already verified. You can sign in.';
            $message_type = 'success';
            $verified_username = $user['username'];
        } else {
            $upd = $db->prepare("UPDATE admin_users SET email_verified = 1, verification_token = NULL WHERE id = ?");
            $upd->execute([$user['id']]);
            $message = 'Email verified! You can now sign in.';
            $message_type = 'success';
            $verified_username = $user['username'];
        }
    } catch (Exception $e) {
        $message = 'Database error. Please try again later.';
        $message_type = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email — Antique Workshop</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card" style="text-align: center;">
            <h1><?php echo $message_type === 'success' ? '✓' : '⚠'; ?></h1>
            <p style="margin-top: 12px;"><?php echo htmlspecialchars($message); ?></p>

            <?php if ($message_type === 'success'): ?>
            <p style="margin-top: 24px;">
                <a href="login.php" class="btn btn-primary">Sign In</a>
            </p>
            <?php else: ?>
            <p style="margin-top: 24px; font-size: 0.85rem;">
                <a href="forgot_password.php">Request a password reset</a>
            </p>
            <?php endif; ?>
        </div>
    </div>
<?php require_once __DIR__ . '/includes/particles.php'; ?>
