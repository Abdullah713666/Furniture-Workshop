<?php
/**
 * Admin Dashboard â€” Antique Furniture Workshop
 */
require_once 'auth.php';
requireLogin();

$current_admin_page = 'dashboard';
$db = getDB();

// Get counts
$gallery_count = $db->query("SELECT COUNT(*) FROM gallery_items")->fetchColumn();
$services_count = $db->query("SELECT COUNT(*) FROM services")->fetchColumn();
$unread_count = $db->query("SELECT COUNT(*) FROM contact_submissions WHERE is_read = 0")->fetchColumn();
$total_messages = $db->query("SELECT COUNT(*) FROM contact_submissions")->fetchColumn();
$timeline_count = $db->query("SELECT COUNT(*) FROM timeline_events")->fetchColumn();

// Categories count
try {
    $categories_count = $db->query("SELECT COUNT(*) FROM categories")->fetchColumn();
} catch (Exception $e) {
    $categories_count = 0;
}

// Recent messages
$recent_messages = $db->query("SELECT * FROM contact_submissions ORDER BY submitted_at DESC LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard â€” Admin</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="admin-layout">
        <?php require_once 'includes/sidebar.php'; ?>

        <main class="admin-main">
            <div class="page-header">
                <h1>Dashboard</h1>
                <p>Welcome back, <?php echo htmlspecialchars($_SESSION['admin_user']); ?>!</p>
            </div>

            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-label">Gallery Items</div>
                    <div class="stat-value"><?php echo $gallery_count; ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Services</div>
                    <div class="stat-value"><?php echo $services_count; ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Unread Messages</div>
                    <div class="stat-value"><?php echo $unread_count; ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Timeline Events</div>
                    <div class="stat-value"><?php echo $timeline_count; ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Categories</div>
                    <div class="stat-value"><?php echo $categories_count; ?></div>
                </div>
            </div>

            <!-- Recent Messages -->
            <div class="form-card">
                <h2>Recent Messages</h2>
                <?php if (empty($recent_messages)): ?>
                    <p style="color: var(--admin-text-muted);">No messages yet.</p>
                <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Service</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_messages as $msg): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($msg['name']); ?></td>
                            <td><?php echo htmlspecialchars($msg['email']); ?></td>
                            <td><?php echo htmlspecialchars(ucfirst($msg['service_interest'])); ?></td>
                            <td><?php echo date('M j, Y', strtotime($msg['submitted_at'])); ?></td>
                            <td>
                                <span class="badge <?php echo $msg['is_read'] ? 'badge-read' : 'badge-unread'; ?>">
                                    <?php echo $msg['is_read'] ? 'Read' : 'New'; ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <p style="margin-top: 12px;"><a href="messages.php">View all messages â†’</a></p>
                <?php endif; ?>
            </div>
        </main>
    </div>
    <script>
        // Session check â€” redirect to login if not authenticated
        (function() {
            // Server-side session handles auth; no client-side token needed
        })();
    </script>
<?php require_once __DIR__ . '/includes/particles.php'; ?>
