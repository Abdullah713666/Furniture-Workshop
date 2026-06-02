<?php
/**
 * Admin Users Management — Antique Furniture Workshop
 */
require_once 'auth.php';
requireLogin();

$current_admin_page = 'users';
$db = getDB();
$message = '';
$message_type = '';

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Toggle active status (POST with CSRF)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle') {
    if (($_POST['csrf_token'] ?? '') !== $csrf_token) {
        die('Invalid CSRF token.');
    }
    $id = intval($_POST['id'] ?? 0);
    $stmt = $db->prepare("UPDATE users SET is_active = NOT is_active WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: users.php?msg=updated');
    exit;
}

// Delete user (POST with CSRF)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    if (($_POST['csrf_token'] ?? '') !== $csrf_token) {
        die('Invalid CSRF token.');
    }
    $id = intval($_POST['id'] ?? 0);
    $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: users.php?msg=deleted');
    exit;
}

// Status messages
if (isset($_GET['msg'])) {
    $messages = ['updated' => 'User status updated.', 'deleted' => 'User account deleted.'];
    $message = $messages[$_GET['msg']] ?? '';
    $message_type = 'success';
}

// Fetch all users
$users = $db->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
$total_users = count($users);
$active_users = count(array_filter($users, function($u) { return $u['is_active']; }));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users — Admin</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="admin-layout">
        <?php require_once 'includes/sidebar.php'; ?>

        <main class="admin-main">
            <div class="page-header">
                <h1>Registered Users</h1>
                <p>Manage users who have signed up on the website.</p>
            </div>

            <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <!-- Stats -->
            <div class="stats-grid" style="margin-bottom: 24px;">
                <div class="stat-card">
                    <div class="stat-label">Total Users</div>
                    <div class="stat-value"><?php echo $total_users; ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Active Users</div>
                    <div class="stat-value"><?php echo $active_users; ?></div>
                </div>
            </div>

            <!-- Users Table -->
            <?php if (empty($users)): ?>
                <div class="form-card">
                    <p style="color: var(--admin-text-muted);">No users have registered yet.</p>
                </div>
            <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Gender</th>
                        <th>Country</th>
                        <th>Registered</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><code><?php echo htmlspecialchars($user['username'] ?? 'N/A'); ?></code></td>
                        <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['gender'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($user['country'] ?? 'N/A'); ?></td>
                        <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                        <td>
                            <form method="POST" action="users.php" style="display:inline;">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                <input type="hidden" name="action" value="toggle">
                                <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                <button type="submit" class="badge <?php echo $user['is_active'] ? 'badge-featured' : 'badge-read'; ?>" style="cursor:pointer; border:none; background:none; font-family:inherit;">
                                    <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                                </button>
                            </form>
                        </td>
                        <td>
                            <div class="actions" style="display:flex; gap: 8px;">
                                <form method="POST" action="users.php" style="display:inline;">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                    <input type="hidden" name="action" value="toggle">
                                    <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                    <button type="submit" class="btn btn-outline btn-sm"><?php echo $user['is_active'] ? 'Deactivate' : 'Activate'; ?></button>
                                </form>
                                <form method="POST" action="users.php" style="display:inline;" onsubmit="return confirm('Delete this user?')">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
