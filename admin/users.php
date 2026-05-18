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

// Toggle active status
if (isset($_GET['toggle_active'])) {
    $stmt = $db->prepare("UPDATE users SET is_active = NOT is_active WHERE id = ?");
    $stmt->execute([$_GET['toggle_active']]);
    header('Location: users.php?msg=updated');
    exit;
}

// Delete user
if (isset($_GET['delete'])) {
    $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header('Location: users.php?msg=deleted');
    exit;
}

// Status messages
if (isset($_GET['msg'])) {
    $messages = ['updated' => 'User updated.', 'deleted' => 'User deleted.'];
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
                        <th>Name</th>
                        <th>Email</th>
                        <th>Registered</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                        <td>
                            <a href="users.php?toggle_active=<?php echo $user['id']; ?>"
                               class="badge <?php echo $user['is_active'] ? 'badge-featured' : 'badge-read'; ?>">
                                <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                            </a>
                        </td>
                        <td>
                            <div class="actions">
                                <a href="users.php?toggle_active=<?php echo $user['id']; ?>" 
                                   class="btn btn-outline btn-sm"><?php echo $user['is_active'] ? 'Deactivate' : 'Activate'; ?></a>
                                <a href="users.php?delete=<?php echo $user['id']; ?>"
                                   class="btn btn-danger btn-sm"
                                   onclick="return confirm('Delete this user?')">Delete</a>
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
