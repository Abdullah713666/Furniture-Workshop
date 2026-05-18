<?php
/**
 * Admin Settings — Antique Furniture Workshop
 * Change admin username and password
 */
require_once 'auth.php';
requireLogin();

$current_admin_page = 'settings';
$db = getDB();
$message = '';
$message_type = '';

// Get current admin info
$stmt = $db->prepare("SELECT * FROM admin_users WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$admin = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_username = trim($_POST['new_username'] ?? '');
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Verify current password
    if (!password_verify($current_password, $admin['password_hash'])) {
        $message = 'Current password is incorrect.';
        $message_type = 'error';
    } elseif (empty($new_username)) {
        $message = 'Username cannot be empty.';
        $message_type = 'error';
    } elseif (!empty($new_password) && strlen($new_password) < 6) {
        $message = 'New password must be at least 6 characters.';
        $message_type = 'error';
    } elseif (!empty($new_password) && $new_password !== $confirm_password) {
        $message = 'New passwords do not match.';
        $message_type = 'error';
    } else {
        try {
            // Check if new username is taken by another admin
            if ($new_username !== $admin['username']) {
                $check = $db->prepare("SELECT id FROM admin_users WHERE username = ? AND id != ?");
                $check->execute([$new_username, $admin['id']]);
                if ($check->fetch()) {
                    $message = 'That username is already taken.';
                    $message_type = 'error';
                }
            }

            if (empty($message)) {
                // Update username
                $stmt = $db->prepare("UPDATE admin_users SET username = ? WHERE id = ?");
                $stmt->execute([$new_username, $admin['id']]);
                $_SESSION['admin_user'] = $new_username;

                // Update password if provided
                if (!empty($new_password)) {
                    $hash = password_hash($new_password, PASSWORD_BCRYPT);
                    $stmt = $db->prepare("UPDATE admin_users SET password_hash = ? WHERE id = ?");
                    $stmt->execute([$hash, $admin['id']]);
                }

                $message = 'Settings updated successfully!';
                $message_type = 'success';

                // Refresh admin data
                $stmt = $db->prepare("SELECT * FROM admin_users WHERE id = ?");
                $stmt->execute([$_SESSION['admin_id']]);
                $admin = $stmt->fetch();
            }
        } catch (Exception $e) {
            $message = 'Error updating settings. Please try again.';
            $message_type = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings — Admin</title>
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
    <div class="admin-layout">
        <?php require_once 'includes/sidebar.php'; ?>

        <main class="admin-main">
            <div class="page-header">
                <h1>Settings</h1>
                <p>Update your admin username and password.</p>
            </div>

            <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <div class="form-card">
                <h2>Account Settings</h2>
                <form method="POST" action="settings.php">
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
                                <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Leave blank to keep current">
                                <button type="button" class="pw-toggle" onclick="togglePw(this)" aria-label="Toggle password visibility">
                                    <svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                </button>
                            </div>
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
    </script>
</body>
</html>
