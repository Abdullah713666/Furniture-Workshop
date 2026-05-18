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

// Delete
if (isset($_GET['delete'])) {
    $stmt = $db->prepare("DELETE FROM contact_submissions WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header('Location: messages.php?msg=deleted');
    exit;
}

// Mark as read
if (isset($_GET['mark_read'])) {
    $stmt = $db->prepare("UPDATE contact_submissions SET is_read = 1 WHERE id = ?");
    $stmt->execute([$_GET['mark_read']]);
    header('Location: messages.php?msg=updated');
    exit;
}

// Mark all as read
if (isset($_GET['mark_all_read'])) {
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
    
    // Auto-mark as read
    if ($view_message && !$view_message['is_read']) {
        $stmt2 = $db->prepare("UPDATE contact_submissions SET is_read = 1 WHERE id = ?");
        $stmt2->execute([$_GET['view']]);
        $view_message['is_read'] = 1;
    }
}

// Fetch all
$submissions = $db->query("SELECT * FROM contact_submissions ORDER BY submitted_at DESC")->fetchAll();
$unread_count = $db->query("SELECT COUNT(*) FROM contact_submissions WHERE is_read = 0")->fetchColumn();
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
                <h1>Contact Messages</h1>
                <p><?php echo count($submissions); ?> total, <?php echo $unread_count; ?> unread</p>
            </div>

            <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <?php if ($view_message): ?>
            <!-- Single Message View -->
            <div class="message-detail">
                <div class="meta">
                    <div class="meta-item">
                        <span>From</span>
                        <?php echo htmlspecialchars($view_message['name']); ?>
                    </div>
                    <div class="meta-item">
                        <span>Email</span>
                        <a href="mailto:<?php echo htmlspecialchars($view_message['email']); ?>"><?php echo htmlspecialchars($view_message['email']); ?></a>
                    </div>
                    <?php if (!empty($view_message['phone'])): ?>
                    <div class="meta-item">
                        <span>Phone</span>
                        <?php echo htmlspecialchars($view_message['phone']); ?>
                    </div>
                    <?php endif; ?>
                    <div class="meta-item">
                        <span>Service</span>
                        <?php echo htmlspecialchars(ucfirst($view_message['service_interest'])); ?>
                    </div>
                    <div class="meta-item">
                        <span>Date</span>
                        <?php echo date('M j, Y \a\t g:i A', strtotime($view_message['submitted_at'])); ?>
                    </div>
                </div>
                <div class="body">
                    <?php echo nl2br(htmlspecialchars($view_message['message'])); ?>
                </div>
            </div>
            <div style="display: flex; gap: 8px; margin-bottom: 30px;">
                <a href="messages.php" class="btn btn-outline">← Back to all messages</a>
                <a href="mailto:<?php echo htmlspecialchars($view_message['email']); ?>" class="btn btn-primary">Reply via Email</a>
                <a href="messages.php?delete=<?php echo $view_message['id']; ?>" class="btn btn-danger" onclick="return confirm('Delete this message?')">Delete</a>
            </div>
            <?php endif; ?>

            <!-- Actions Bar -->
            <?php if ($unread_count > 0 && !$view_message): ?>
            <div style="margin-bottom: 16px;">
                <a href="messages.php?mark_all_read=1" class="btn btn-outline btn-sm">Mark all as read</a>
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
                            <div class="actions">
                                <a href="messages.php?view=<?php echo $sub['id']; ?>" class="btn btn-outline btn-sm">View</a>
                                <a href="messages.php?delete=<?php echo $sub['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete?')">Delete</a>
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
