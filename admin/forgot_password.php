<?php
/**
 * Forgot Password — Antique Furniture Workshop
 *
 * GET  → shows the "enter your username" form
 * POST → looks up the admin, generates a 1-hour reset token,
 *        emails a link to admin/reset_password.php?token=…
 */
require_once __DIR__ . '/auth.php';

// Already logged in? Bounce.
if (isLoggedIn()) {
    header('Location: dashboard.php?token=' . ($_SESSION['admin_tab_token'] ?? ''));
    exit;
}

require_once __DIR__ . '/includes/mailer.php';

afw_ensure_auth_schema();

$message = '';
$message_type = '';

// Detect base URL for the reset link
function afw_base_url(): string {
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    return $scheme . '://' . $host;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    if (($_POST['csrf_token'] ?? '') !== $_SESSION['csrf_token']) {
        die('Invalid CSRF token.');
    }

    $username = trim($_POST['username'] ?? '');

    if ($username === '') {
        $message = 'Please enter your username.';
        $message_type = 'error';
    } else {
        try {
            $db = getDB();
            $stmt = $db->prepare("SELECT id, username, email, email_verified FROM admin_users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            // Always show the same generic message to prevent user enumeration
            $message = "If that account exists and has a verified email, a reset link has been sent.";
            $message_type = 'success';

            if ($user && !empty($user['email']) && intval($user['email_verified']) === 1) {
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hour

                $upd = $db->prepare("UPDATE admin_users SET reset_token = ?, reset_expires = ? WHERE id = ?");
                $upd->execute([$token, $expires, $user['id']]);

                $link = afw_base_url() . '/admin/reset_password.php?token=' . urlencode($token);

                $html = '<div style="font-family: Georgia, serif; max-width: 560px; margin: 0 auto; color: #1a1410;">'
                      . '<h2 style="color: #6b5020;">Password Reset — Antique Workshop Admin</h2>'
                      . '<p>Hello ' . htmlspecialchars($user['username']) . ',</p>'
                      . '<p>Someone (hopefully you) requested a password reset for the Antique Workshop admin panel.</p>'
                      . '<p>Click the button below within the next <strong>1 hour</strong> to set a new password:</p>'
                      . '<p style="margin: 28px 0;"><a href="' . htmlspecialchars($link) . '" '
                      . 'style="background:#d4a843;color:#1a1410;padding:14px 28px;border-radius:6px;'
                      . 'text-decoration:none;font-weight:600;display:inline-block;">Reset My Password</a></p>'
                      . '<p>Or paste this link into your browser:<br><span style="color:#6b5020;">'
                      . htmlspecialchars($link) . '</span></p>'
                      . '<hr style="border:none;border-top:1px solid #d4a843;margin:24px 0;">'
                      . '<p style="color:#8a7e6e;font-size:0.85em;">If you did not request this, you can safely ignore this email — your password will remain unchanged.</p>'
                      . '</div>';

                afw_send_email($user['email'], 'Reset your admin password', $html);
            }
        } catch (Exception $e) {
            error_log('Forgot password error: ' . $e->getMessage());
        }
    }
}

// Generate fresh CSRF for the form
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password — Antique Workshop</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <h1>Forgot Password</h1>
            <p>Enter your admin username. If the account has a verified email, we'll send a reset link.</p>

            <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type === 'success' ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="forgot_password.php">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" class="form-control" id="username" name="username" required autofocus>
                </div>

                <button type="submit" class="btn btn-primary btn-block" style="margin-top: 8px;">Send Reset Link</button>
            </form>

            <p style="text-align: center; margin-top: 20px; font-size: 0.8rem;">
                <a href="login.php">← Back to Sign In</a>
            </p>
        </div>
    </div>
<?php require_once __DIR__ . '/includes/particles.php'; ?>
