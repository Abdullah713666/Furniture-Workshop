<?php
/**
 * Admin Settings  Antique Furniture Workshop
 * Change admin username and password
 */
require_once 'auth.php';
requireLogin();

$current_admin_page = 'settings';
$db = getDB();
$message = '';
$message_type = '';

require_once __DIR__ . '/includes/mailer.php';

// Self-heal: ensure email/verification/reset columns exist on admin_users
afw_ensure_auth_schema();

// Get current admin info
$stmt = $db->prepare("SELECT * FROM admin_users WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$admin = $stmt->fetch();

// CSRF for the email-update sub-form
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

function afw_base_url(): string {
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    return $scheme . '://' . $host;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check for ALL POST actions
    if (($_POST['csrf_token'] ?? '') !== $csrf_token) {
        http_response_code(403);
        http_response_code(403); die('Forbidden');
    }

    $action = $_POST['action'] ?? 'account';

    if (($action ?? '') === '' || $action === 'account') {
        // ===== Account settings (username + password) =====
        $current_password = $_POST['current_password'] ?? '';
        $new_username = trim($_POST['new_username'] ?? '');
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (!password_verify($current_password, $admin['password_hash'])) {
            $message = 'Current password is incorrect.';
            $message_type = 'error';
        } elseif (empty($new_username)) {
            $message = 'Username cannot be empty.';
            $message_type = 'error';
        } elseif (!empty($new_password) && strlen($new_password) < 8) {
            $message = 'New password must be at least 8 characters.';
            $message_type = 'error';
        } elseif (!empty($new_password) && $new_password !== $confirm_password) {
            $message = 'New passwords do not match.';
            $message_type = 'error';
        } else {
            try {
                if ($new_username !== $admin['username']) {
                    $check = $db->prepare("SELECT id FROM admin_users WHERE username = ? AND id != ?");
                    $check->execute([$new_username, $admin['id']]);
                    if ($check->fetch()) {
                        $message = 'That username is already taken.';
                        $message_type = 'error';
                    }
                }

                if (empty($message)) {
                    $stmt = $db->prepare("UPDATE admin_users SET username = ? WHERE id = ?");
                    $stmt->execute([$new_username, $admin['id']]);
                    $_SESSION['admin_user'] = $new_username;

                    if (!empty($new_password)) {
                        $hash = password_hash($new_password, PASSWORD_BCRYPT);
                        $stmt = $db->prepare("UPDATE admin_users SET password_hash = ? WHERE id = ?");
                        $stmt->execute([$hash, $admin['id']]);
                    }

                    $message = 'Account settings updated successfully!';
                    $message_type = 'success';
                }
            } catch (Exception $e) {
                $message = 'Error updating settings. Please try again.';
                $message_type = 'error';
            }
        }
    } elseif ($action === 'set_email') {
        // ===== Update email + send verification link =====
        $new_email = trim($_POST['new_email'] ?? '');

        if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            $message = 'Please enter a valid email address.';
            $message_type = 'error';
        } else {
            $token = bin2hex(random_bytes(32));
            $upd = $db->prepare("UPDATE admin_users SET email = ?, email_verified = 0, verification_token = ? WHERE id = ?");
            $upd->execute([$new_email, $token, $admin['id']]);

            $link = afw_base_url() . '/admin/verify_email.php?token=' . urlencode($token);
            $html = '<div style="font-family: Georgia, serif; max-width: 560px; margin: 0 auto; color: #1a1410;">'
                  . '<h2 style="color: #6b5020;">Verify your admin email</h2>'
                  . '<p>Hello ' . htmlspecialchars($admin['username']) . ',</p>'
                  . '<p>Click the button below to verify this email address for the Antique Workshop admin panel:</p>'
                  . '<p style="margin: 28px 0;"><a href="' . htmlspecialchars($link) . '" '
                  . 'style="background:#d4a843;color:#1a1410;padding:14px 28px;border-radius:6px;'
                  . 'text-decoration:none;font-weight:600;display:inline-block;">Verify Email</a></p>'
                  . '<p>Or paste this link:<br><span style="color:#6b5020;">' . htmlspecialchars($link) . '</span></p>'
                  . '</div>';

            $result = afw_send_email($new_email, 'Verify your admin email', $html);
            if ($result['ok']) {
                $message = 'Email updated. A verification link has been sent to ' . htmlspecialchars($new_email) . '.';
                $message_type = 'success';
            } else {
                $message = 'Email was saved, but sending the verification email failed. Check that RESEND_API_KEY is set in your environment. (Resend status: ' . htmlspecialchars((string)$result['status']) . ')';
                $message_type = 'error';
                error_log('Resend send failed: ' . $result['error'] ?? '' . ' body=' . $result['body']);
            }
        }
    } elseif ($action === 'resend_verification') {
        // ===== Resend verification to current email =====
        if (empty($admin['email'])) {
            $message = 'No email on file. Please set an email first.';
            $message_type = 'error';
        } else {
            $token = bin2hex(random_bytes(32));
            $upd = $db->prepare("UPDATE admin_users SET verification_token = ? WHERE id = ?");
            $upd->execute([$token, $admin['id']]);

            $link = afw_base_url() . '/admin/verify_email.php?token=' . urlencode($token);
            $html = '<div style="font-family: Georgia, serif; max-width: 560px; margin: 0 auto; color: #1a1410;">'
                  . '<p>Hello ' . htmlspecialchars($admin['username']) . ',</p>'
                  . '<p>Click below to verify your email:</p>'
                  . '<p style="margin: 28px 0;"><a href="' . htmlspecialchars($link) . '" '
                  . 'style="background:#d4a843;color:#1a1410;padding:14px 28px;border-radius:6px;'
                  . 'text-decoration:none;font-weight:600;display:inline-block;">Verify Email</a></p>'
                  . '</div>';
            $result = afw_send_email($admin['email'], 'Verify your admin email', $html);
            if ($result['ok']) {
                $message = 'Verification email resent to ' . htmlspecialchars($admin['email']) . '.';
                $message_type = 'success';
            } else {
                $message = 'Could not resend email. Check RESEND_API_KEY. (status ' . (int)$result['status'] . ')';
                $message_type = 'error';
            }
        }
    }

    // Refresh admin data
    $stmt = $db->prepare("SELECT * FROM admin_users WHERE id = ?");
    $stmt->execute([$_SESSION['admin_id']]);
    $admin = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings  Admin</title>
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
        .pw-strength { display: none; margin-top: 6px; }
        .pw-strength-bar { height: 4px; border-radius: 2px; background: #e0d8cc; overflow: hidden; }
        .pw-strength-fill { height: 100%; width: 0; border-radius: 2px; transition: width 0.3s, background 0.3s; }
        .pw-strength-text { font-size: 0.72rem; margin-top: 3px; color: var(--admin-text-muted, #666); }
    </style>
</head>
<body>
    <div class="admin-layout">
        <?php require_once 'includes/sidebar.php'; ?>

        <main class="admin-main">
            <div class="page-header">
                <h1>Settings</h1>
                <p>Update your admin username, password, and email.</p>
            </div>

            <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <div class="form-card">
                <h2>Email Address <?php if (!empty($admin['email'])): ?>
                    <?php if (intval($admin['email_verified']) === 1): ?>
                        <span class="badge badge-featured" style="font-size:0.7rem; vertical-align: middle;"> Verified</span>
                    <?php else: ?>
                        <span class="badge badge-unread" style="font-size:0.7rem; vertical-align: middle;"> Unverified</span>
                    <?php endif; ?>
                <?php endif; ?></h2>
                <p style="font-size:0.85rem; color: var(--admin-text-muted);">
                    Used for password resets and security notifications. Must be verified to log in.
                </p>

                <form method="POST" action="settings.php">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="action" value="set_email">

                    <div class="form-group">
                        <label for="new_email">Email</label>
                        <input type="email" class="form-control" id="new_email" name="new_email"
                               value="<?php echo htmlspecialchars($admin['email'] ?? ''); ?>"
                               placeholder="you@example.com" required>
                    </div>

                    <div style="display:flex; gap:8px; flex-wrap:wrap;">
                        <button type="submit" class="btn btn-primary">Save & Send Verification</button>
                        <?php if (!empty($admin['email']) && intval($admin['email_verified']) === 0): ?>
                        <button type="submit" name="action" value="resend_verification" class="btn btn-outline">Resend Verification</button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <div class="form-card">
                <h2>Account Settings</h2>
                <form method="POST" action="settings.php">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <div class="form-group">
                        <label for="current_password">Current Password *</label>
                        <div class="pw-wrapper">
                            <input type="password" class="form-control" id="current_password" name="current_password" required placeholder="Enter your current password to make changes">
                            <button type="button" class="pw-toggle" onclick="togglePw(this)" aria-label="Toggle password visibility">
                                <svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="new_username">Username</label>
                        <input type="text" class="form-control" id="new_username" name="new_username" value="<?php echo htmlspecialchars($admin['username']); ?>" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <div class="pw-wrapper">
                                <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Leave blank to keep current" oninput="checkPwStrength(this, 'strength-new')">
                                <button type="button" class="pw-toggle" onclick="togglePw(this)" aria-label="Toggle password visibility">
                                    <svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                </button>
                            </div>
                            <div id="strength-new" class="pw-strength"><div class="pw-strength-bar"><div class="pw-strength-fill"></div></div><div class="pw-strength-text"></div></div>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <div class="pw-wrapper">
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Re-type new password">
                                <button type="button" class="pw-toggle" onclick="togglePw(this)" aria-label="Toggle password visibility">
                                    <svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <p style="font-size: 0.75rem; color: var(--admin-text-muted); margin-bottom: 16px;">Leave password fields blank if you only want to change the username.</p>

                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </main>
    </div>
    <script>
    function togglePw(btn) {
        var input = btn.previousElementSibling;
        var isHidden = input.type === 'password';
        input.type = isHidden ? 'text' : 'password';
        btn.innerHTML = isHidden
            ? '<svg viewBox="0 0 24 24"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>'
            : '<svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';
    }
    function checkPwStrength(input, containerId) {
        var container = document.getElementById(containerId);
        if (!container) return;
        var pw = input.value;
        var fill = container.querySelector('.pw-strength-fill');
        var text = container.querySelector('.pw-strength-text');
        if (!pw) { container.style.display = 'none'; return; }
        container.style.display = 'block';
        var score = 0;
        if (pw.length >= 8) score++;
        if (/[A-Z]/.test(pw)) score++;
        if (/[a-z]/.test(pw)) score++;
        if (/[0-9]/.test(pw)) score++;
        if (/[^A-Za-z0-9]/.test(pw)) score++;
        var levels = [
            { min: 0, width: '20%', color: '#dc3545', label: 'Very Weak' },
            { min: 2, width: '40%', color: '#fd7e14', label: 'Weak' },
            { min: 3, width: '60%', color: '#ffc107', label: 'Fair' },
            { min: 4, width: '80%', color: '#20c997', label: 'Good' },
            { min: 5, width: '100%', color: '#198754', label: 'Strong' }
        ];
        var level = levels[0];
        for (var i = levels.length - 1; i >= 0; i--) {
            if (score >= levels[i].min) { level = levels[i]; break; }
        }
        fill.style.width = level.width;
        fill.style.background = level.color;
        text.textContent = level.label;
        text.style.color = level.color;
    }
    </script>
<?php require_once __DIR__ . '/includes/particles.php'; ?>
