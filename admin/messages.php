<?php
/**
 * Admin Messages — Antique Furniture Workshop
 */
require_once 'auth.php';
requireLogin();

$current_admin_page = 'messages';
$db = getDB();
$message = '';
$message_type = '';

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// --- Handle Actions (POST with CSRF) ---

// Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    if (($_POST['csrf_token'] ?? '') !== $csrf_token) {
        die('Invalid CSRF token.');
    }
    $id = intval($_POST['id'] ?? 0);
    $stmt = $db->prepare("DELETE FROM contact_submissions WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: messages.php?msg=deleted');
    exit;
}

// Mark as read
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'mark_read') {
    if (($_POST['csrf_token'] ?? '') !== $csrf_token) {
        die('Invalid CSRF token.');
    }
    $id = intval($_POST['id'] ?? 0);
    $stmt = $db->prepare("UPDATE contact_submissions SET is_read = 1 WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: messages.php?msg=updated');
    exit;
}

// Mark all as read
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'mark_all_read') {
    if (($_POST['csrf_token'] ?? '') !== $csrf_token) {
        die('Invalid CSRF token.');
    }
    $db->exec("UPDATE contact_submissions SET is_read = 1 WHERE is_read = 0");
    header('Location: messages.php?msg=updated');
    exit;
}

// Status messages
if (isset($_GET['msg'])) {
    $messages = ['deleted' => 'Message deleted.', 'updated' => 'Updated.'];
    $message = $messages[$_GET['msg']] ?? '';
    $message_type = 'success';
}

// View single message
$view_message = null;
if (isset($_GET['view'])) {
    $stmt = $db->prepare("SELECT * FROM contact_submissions WHERE id = ?");
    $stmt->execute([$_GET['view']]);
    $view_message = $stmt->fetch();
    
    // Automatically mark as read when viewed
    if ($view_message && !$view_message['is_read']) {
        $stmt = $db->prepare("UPDATE contact_submissions SET is_read = 1 WHERE id = ?");
        $stmt->execute([$view_message['id']]);
    }
}

// Fetch messages
$submissions = $db->query("SELECT * FROM contact_submissions ORDER BY submitted_at DESC")->fetchAll();
$unread_count = count(array_filter($submissions, function($s) { return !$s['is_read']; }));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages — Admin</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="admin-layout">
        <?php require_once 'includes/sidebar.php'; ?>

        <main class="admin-main">
            <div class="page-header">
                <h1>Contact Inquiries</h1>
                <p>Manage the messages sent by visitors through the contact form.</p>
            </div>

            <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <!-- View Single Message Card -->
            <?php if ($view_message): ?>
            <div class="message-view-card" style="background: var(--admin-card-bg, #222); border: 1px solid var(--admin-border-color, #333); border-radius: 8px; padding: 24px; margin-bottom: 24px;">
                <div class="header" style="border-bottom: 1px solid var(--admin-border-color, #333); padding-bottom: 16px; margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center;">
                    <h2>From: <?php echo htmlspecialchars($view_message['name']); ?></h2>
                    <span class="badge badge-featured">Inquiry</span>
                </div>
                <div class="meta" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 20px;">
                    <div class="meta-item">
                        <span style="display:block; font-size:0.75rem; text-transform:uppercase; color:var(--admin-text-muted, #777); font-weight:600;">Email</span>
                        <a href="mailto:<?php echo htmlspecialchars($view_message['email']); ?>"><?php echo htmlspecialchars($view_message['email']); ?></a>
                    </div>
                    <?php if ($view_message['phone']): ?>
                    <div class="meta-item">
                        <span style="display:block; font-size:0.75rem; text-transform:uppercase; color:var(--admin-text-muted, #777); font-weight:600;">Phone</span>
                        <?php echo htmlspecialchars($view_message['phone']); ?>
                    </div>
                    <?php endif; ?>
                    <div class="meta-item">
                        <span style="display:block; font-size:0.75rem; text-transform:uppercase; color:var(--admin-text-muted, #777); font-weight:600;">Service</span>
                        <?php echo htmlspecialchars(ucfirst($view_message['service_interest'])); ?>
                    </div>
                    <div class="meta-item">
                        <span style="display:block; font-size:0.75rem; text-transform:uppercase; color:var(--admin-text-muted, #777); font-weight:600;">Date</span>
                        <?php echo date('M j, Y \a\t g:i A', strtotime($view_message['submitted_at'])); ?>
                    </div>
                </div>
                <div class="body" style="background: rgba(0,0,0,0.1); border-radius: 4px; padding: 16px; font-size: 0.95rem; line-height: 1.6; white-space: pre-wrap; word-break: break-all; margin-bottom: 24px; color: var(--admin-text, #ccc);">
                    <?php echo nl2br(htmlspecialchars($view_message['message'])); ?>
                </div>
            </div>
            <div style="display: flex; gap: 8px; margin-bottom: 30px;">
                <a href="messages.php" class="btn btn-outline">← Back to all messages</a>
                <a href="mailto:<?php echo htmlspecialchars($view_message['email']); ?>" class="btn btn-primary">Reply via Email</a>
                <form method="POST" action="messages.php" style="display:inline;" onsubmit="return confirm('Delete this message?')">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?php echo $view_message['id']; ?>">
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
            <?php endif; ?>

            <!-- Actions Bar -->
            <?php if ($unread_count > 0 && !$view_message): ?>
            <div style="margin-bottom: 16px;">
                <form method="POST" action="messages.php" style="display:inline;">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="action" value="mark_all_read">
                    <button type="submit" class="btn btn-outline btn-sm">Mark all as read</button>
                </form>
            </div>
            <?php endif; ?>

            <?php if (!$view_message): ?>
            <!-- Messages Table -->
            <?php if (empty($submissions)): ?>
                <div class="form-card">
                    <p style="color: var(--admin-text-muted); text-align: center; padding: 20px;">No messages yet. They will appear here when visitors submit the contact form.</p>
                </div>
            <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Status</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Service</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($submissions as $sub): ?>
                    <tr>
                        <td>
                            <span class="badge <?php echo $sub['is_read'] ? 'badge-read' : 'badge-unread'; ?>">
                                <?php echo $sub['is_read'] ? 'Read' : 'New'; ?>
                            </span>
                        </td>
                        <td><a href="messages.php?view=<?php echo $sub['id']; ?>"><?php echo htmlspecialchars($sub['name']); ?></a></td>
                        <td><?php echo htmlspecialchars($sub['email']); ?></td>
                        <td><?php echo htmlspecialchars(ucfirst($sub['service_interest'])); ?></td>
                        <td><?php echo date('M j, Y', strtotime($sub['submitted_at'])); ?></td>
                        <td>
                            <div class="actions" style="display:flex; gap:8px;">
                                <a href="messages.php?view=<?php echo $sub['id']; ?>" class="btn btn-outline btn-sm">View</a>
                                <form method="POST" action="messages.php" style="display:inline;" onsubmit="return confirm('Delete message?')">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $sub['id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
