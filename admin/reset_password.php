<?php
/**
 * Reset Password  Antique Furniture Workshop
 *
 * GET  ?token=    shows the "set a new password" form
 * POST             validates the token, updates the password hash, redirects to login
 */
require_once __DIR__ . '/auth.php';

if (isLoggedIn()) {
    header('Location: dashboard.php?token=' . ($_SESSION['admin_tab_token'] ?? ''));
    exit;
}

$token = $_GET['token'] ?? '';
$message = '';
$message_type = '';
$valid_token = false;
$user_id = null;

afw_ensure_auth_schema();

if ($token !== '') {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT id, username, reset_expires FROM admin_users WHERE reset_token = ?");
        $stmt->execute([$token]);
        $user = $stmt->fetch();

        if ($user && $user['reset_expires'] !== null && strtotime($user['reset_expires']) > time()) {
            $valid_token = true;
            $user_id = $user['id'];
        } else {
            $message = 'This reset link is invalid or has expired. Please request a new one.';
            $message_type = 'error';
        }
    } catch (Exception $e) {
        $message = 'Database error. Please try again later.';
        $message_type = 'error';
    }
} else {
    $message = 'Missing reset token.';
    $message_type = 'error';
}

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Handle POST (set new password)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid_token) {
    if (($_POST['csrf_token'] ?? '') !== $csrf_token) {
        http_response_code(403); die('Forbidden');
    }

    $pw1 = $_POST['password'] ?? '';
    $pw2 = $_POST['password_confirm'] ?? '';

    if (strlen($pw1) < 8) {
        $message = 'Password must be at least 8 characters.';
        $message_type = 'error';
    } elseif ($pw1 !== $pw2) {
        $message = 'Passwords do not match.';
        $message_type = 'error';
    } else {
        $hash = password_hash($pw1, PASSWORD_BCRYPT);
        $upd = $db->prepare("UPDATE admin_users SET password_hash = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
        $upd->execute([$hash, $user_id]);

        // Destroy session to force re-login everywhere
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
        }
        session_destroy();

        header('Location: login.php?reset=ok');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password  Antique Workshop</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .pw-wrapper { position: relative; }
        .pw-wrapper .form-control { padding-right: 44px; }
        .pw-toggle {
            position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
            background: none; border: none; cursor: pointer; color: var(--admin-text-muted, #8a7e6e);
            padding: 4px; display: flex; align-items: center; justify-content: center;
        }
        .pw-toggle:hover { color: var(--admin-accent, #d4a843); }
        .pw-toggle svg { width: 20px; height: 20px; stroke: currentColor; fill: none; stroke-width: 2; }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <h1>Set New Password</h1>
            <p>Choose a strong password (at least 8 characters).</p>

            <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type === 'success' ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
            <?php endif; ?>

            <?php if ($valid_token): ?>
            <form method="POST" action="reset_password.php?token=<?php echo urlencode($token); ?>">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                <div class="form-group">
                    <label for="password">New Password</label>
                    <div class="pw-wrapper">
                        <input type="password" class="form-control" id="password" name="password" required minlength="8" autofocus>
                        <button type="button" class="pw-toggle" onclick="togglePw(this, 'password')" aria-label="Toggle password visibility">
                            <svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password_confirm">Confirm Password</label>
                    <div class="pw-wrapper">
                        <input type="password" class="form-control" id="password_confirm" name="password_confirm" required minlength="8">
                        <button type="button" class="pw-toggle" onclick="togglePw(this, 'password_confirm')" aria-label="Toggle password visibility">
                            <svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block" style="margin-top: 8px;">Set Password</button>
            </form>
            <?php else: ?>
            <p style="text-align: center; margin-top: 20px; font-size: 0.85rem;">
                <a href="forgot_password.php"> Request a new reset link</a>
            </p>
            <?php endif; ?>

            <p style="text-align: center; margin-top: 20px; font-size: 0.8rem;">
                <a href="login.php"> Back to Sign In</a>
            </p>
        </div>
    </div>
    <script>
    function togglePw(btn, id) {
        var input = document.getElementById(id);
        if (!input) return;
        var isHidden = input.type === 'password';
        input.type = isHidden ? 'text' : 'password';
    }
    </script>
<?php require_once __DIR__ . '/includes/particles.php'; ?>
