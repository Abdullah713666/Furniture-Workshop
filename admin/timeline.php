<?php
/**
 * Admin Timeline CRUD — Antique Furniture Workshop
 */
require_once 'auth.php';
requireLogin();

$current_admin_page = 'timeline';
$db = getDB();
$message = '';
$message_type = '';

// Delete
if (isset($_GET['delete'])) {
    $stmt = $db->prepare("DELETE FROM timeline_events WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header('Location: timeline.php?msg=deleted');
    exit;
}

// Add / Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $year = trim($_POST['year'] ?? '');
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $display_order = intval($_POST['display_order'] ?? 0);

    if (empty($year) || empty($title) || empty($description)) {
        $message = 'All fields are required.';
        $message_type = 'error';
    } else {
        if ($id) {
            $stmt = $db->prepare("UPDATE timeline_events SET year=?, title=?, description=?, display_order=? WHERE id=?");
            $stmt->execute([$year, $title, $description, $display_order, $id]);
        } else {
            $stmt = $db->prepare("INSERT INTO timeline_events (year, title, description, display_order) VALUES (?, ?, ?, ?)");
            $stmt->execute([$year, $title, $description, $display_order]);
        }
        header('Location: timeline.php?msg=saved');
        exit;
    }
}

// Status messages
if (isset($_GET['msg'])) {
    $messages = ['saved' => 'Event saved!', 'deleted' => 'Event deleted.'];
    $message = $messages[$_GET['msg']] ?? '';
    $message_type = 'success';
}

// Edit item
$edit_item = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM timeline_events WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_item = $stmt->fetch();
}

// Fetch all
$events = $db->query("SELECT * FROM timeline_events ORDER BY display_order ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Timeline — Admin</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="admin-layout">
        <?php require_once 'includes/sidebar.php'; ?>

        <main class="admin-main">
            <div class="page-header">
                <h1>Timeline Events</h1>
                <p>Manage the heritage milestones on the About page.</p>
            </div>

            <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <!-- Add/Edit Form -->
            <div class="form-card">
                <h2><?php echo $edit_item ? 'Edit Event' : 'Add New Event'; ?></h2>
                <form method="POST" action="timeline.php">
                    <?php if ($edit_item): ?>
                    <input type="hidden" name="id" value="<?php echo $edit_item['id']; ?>">
                    <?php endif; ?>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="year">Year *</label>
                            <input type="text" class="form-control" id="year" name="year" value="<?php echo htmlspecialchars($edit_item['year'] ?? ''); ?>" placeholder="e.g. 2024" required>
                        </div>
                        <div class="form-group">
                            <label for="title">Title *</label>
                            <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($edit_item['title'] ?? ''); ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description">Description *</label>
                        <textarea class="form-control" id="description" name="description" rows="2" required><?php echo htmlspecialchars($edit_item['description'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="display_order">Display Order</label>
                        <input type="number" class="form-control" id="display_order" name="display_order" value="<?php echo $edit_item['display_order'] ?? 0; ?>" style="max-width: 200px;">
                    </div>

                    <div style="display: flex; gap: 8px;">
                        <button type="submit" class="btn btn-primary"><?php echo $edit_item ? 'Update Event' : 'Add Event'; ?></button>
                        <?php if ($edit_item): ?>
                        <a href="timeline.php" class="btn btn-outline">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Events Table -->
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Year</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Order</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($events as $event): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($event['year']); ?></strong></td>
                        <td><?php echo htmlspecialchars($event['title']); ?></td>
                        <td><?php echo htmlspecialchars(substr($event['description'], 0, 60)); ?>...</td>
                        <td><?php echo $event['display_order']; ?></td>
                        <td>
                            <div class="actions">
                                <a href="timeline.php?edit=<?php echo $event['id']; ?>" class="btn btn-outline btn-sm">Edit</a>
                                <a href="timeline.php?delete=<?php echo $event['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this event?')">Delete</a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </main>
    </div>
</body>
</html>
