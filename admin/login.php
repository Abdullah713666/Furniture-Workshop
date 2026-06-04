<?php
/**
 * Admin Login — Antique Furniture Workshop
 */
require_once __DIR__ . '/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php?token=' . ($_SESSION['admin_tab_token'] ?? ''));
    exit;
}

$error = '';
$max_attempts = 5;
$lockout_duration = 900; // 15 minutes

// reCAPTCHA v2 keys (env-overridable; test keys below work on localhost only)
$recaptcha_site   = getenv('RECAPTCHA_SITE_KEY_ADMIN')   ?: '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI';
$recaptcha_secret = getenv('RECAPTCHA_SECRET_KEY_ADMIN') ?: '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe';

// Initialize brute force session counters
if (!isset($_SESSION['admin_failed_attempts'])) {
    $_SESSION['admin_failed_attempts'] = 0;
}

// Check lockout
if (isset($_SESSION['admin_lockout_time']) && time() < $_SESSION['admin_lockout_time']) {
    $remaining = ceil(($_SESSION['admin_lockout_time'] - time()) / 60);
    $error = "Too many failed login attempts. Please try again in {$remaining} minutes.";
}

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($error)) {
    // Validate CSRF
    if (($_POST['csrf_token'] ?? '') !== $csrf_token) {
        die('Invalid CSRF token.');
    }

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $captcha_response = $_POST['g-recaptcha-response'] ?? '';

    // Verify reCAPTCHA (counts toward lockout on failure, same as bad creds)
    $captcha_ok = false;
    if (!empty($captcha_response)) {
        $verify_url = 'https://www.google.com/recaptcha/api/siteverify';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $verify_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'secret'   => $recaptcha_secret,
            'response' => $captcha_response,
            'remoteip' => $_SERVER['REMOTE_ADDR'] ?? null,
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $verify_body = curl_exec($ch);
        $verify_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $verify_data = $verify_body ? json_decode($verify_body, true) : null;
        $captcha_ok = is_array($verify_data) && !empty($verify_data['success']);
    }

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } elseif (!$captcha_ok) {
        $_SESSION['admin_failed_attempts']++;
        if ($_SESSION['admin_failed_attempts'] >= $max_attempts) {
            $_SESSION['admin_lockout_time'] = time() + $lockout_duration;
            $error = 'Too many failed login attempts. You are locked out for 15 minutes.';
        } else {
            $remaining_attempts = $max_attempts - $_SESSION['admin_failed_attempts'];
            $error = "CAPTCHA verification failed. {$remaining_attempts} attempts remaining.";
        }
    } else {
        try {
            $db = getDB();
            $stmt = $db->prepare("SELECT * FROM admin_users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                // Block login if email exists but isn't verified
                $hasEmail = !empty($user['email']);
                $verified = isset($user['email_verified']) ? intval($user['email_verified']) === 1 : true;
                if ($hasEmail && !$verified) {
                    $error = 'Please verify your email address before signing in. Check your inbox for the verification link, or use “Forgot password” to receive a new email.';
                } else {
                    // Reset failed attempts on success
                    $_SESSION['admin_failed_attempts'] = 0;
                    unset($_SESSION['admin_lockout_time']);

                    // Prevent Session Fixation by regenerating Session ID on login
                    session_regenerate_id(true);

                    $_SESSION['admin_id'] = $user['id'];
                    $_SESSION['admin_user'] = $user['username'];
                    $_SESSION['admin_tab_token'] = bin2hex(random_bytes(16));
                    $_SESSION['last_activity'] = time();
                    header('Location: dashboard.php?token=' . $_SESSION['admin_tab_token']);
                    exit;
                }
            } else {
                $_SESSION['admin_failed_attempts']++;
                if ($_SESSION['admin_failed_attempts'] >= $max_attempts) {
                    $_SESSION['admin_lockout_time'] = time() + $lockout_duration;
                    $error = 'Too many failed login attempts. You are locked out for 15 minutes.';
                } else {
                    $remaining_attempts = $max_attempts - $_SESSION['admin_failed_attempts'];
                    $error = "Invalid username or password. {$remaining_attempts} attempts remaining.";
                }
            }
        } catch (Exception $e) {
            $error = 'Database error. Please make sure the database has been set up.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — Antique Workshop</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .pw-wrapper { position: relative; }
        .pw-wrapper .form-control { padding-right: 44px; }
        .pw-toggle {
            position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
            background: none; border: none; cursor: pointer; color: var(--admin-text-muted, #666);
            padding: 4px; display: flex; align-items: center; justify-content: center;
        }
        .pw-toggle:hover { color: var(--admin-accent, #d4a843); }
        .pw-toggle svg { width: 20px; height: 20px; stroke: currentColor; fill: none; stroke-width: 2; }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <h1>🔐 Admin Panel</h1>
            <p>Antique Furniture Workshop — Content Management</p>

            <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php elseif (isset($_GET['reset']) && $_GET['reset'] === 'ok'): ?>
            <div class="alert alert-success">Password updated successfully. Please sign in.</div>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" class="form-control" id="username" name="username" required autofocus <?php echo !empty($error) && isset($_SESSION['admin_lockout_time']) && time() < $_SESSION['admin_lockout_time'] ? 'disabled' : ''; ?>>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="pw-wrapper">
                        <input type="password" class="form-control" id="password" name="password" required <?php echo !empty($error) && isset($_SESSION['admin_lockout_time']) && time() < $_SESSION['admin_lockout_time'] ? 'disabled' : ''; ?>>
                        <button type="button" class="pw-toggle" onclick="togglePw(this)" aria-label="Toggle password visibility">
                            <svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                </div>

                <?php $is_locked = isset($_SESSION['admin_lockout_time']) && time() < $_SESSION['admin_lockout_time']; ?>
                <?php if (!$is_locked): ?>
                <div class="form-group" style="display:flex; justify-content:center; margin: 16px 0;">
                    <div class="g-recaptcha" data-sitekey="<?php echo htmlspecialchars($recaptcha_site); ?>"></div>
                </div>
                <?php endif; ?>

                <button type="submit" class="btn btn-primary btn-block" style="margin-top: 8px;" <?php echo $is_locked ? 'disabled' : ''; ?>>Sign In</button>
            </form>

            <p style="text-align: center; margin-top: 16px; font-size: 0.8rem;">
                <a href="forgot_password.php">Forgot password?</a>
            </p>
            <p style="text-align: center; margin-top: 8px; font-size: 0.8rem;">
                <a href="../index.php">← Back to website</a>
            </p>
        </div>
    </div>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <script>
    function togglePw(btn) {
        var input = btn.previousElementSibling;
        var isHidden = input.type === 'password';
        input.type = isHidden ? 'text' : 'password';
        btn.innerHTML = isHidden
            ? '<svg viewBox="0 0 24 24"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>'
            : '<svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';
    }
    </script>
<?php require_once __DIR__ . '/includes/particles.php'; ?>
