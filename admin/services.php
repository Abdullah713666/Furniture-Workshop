<?php
/**
 * Admin Services CRUD — Antique Furniture Workshop
 */
require_once 'auth.php';
requireLogin();

$current_admin_page = 'services';
$db = getDB();
$message = '';
$message_type = '';

// Delete
if (isset($_GET['delete'])) {
    $stmt = $db->prepare("DELETE FROM services WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header('Location: services.php?msg=deleted');
    exit;
}

// Toggle active
if (isset($_GET['toggle_active'])) {
    $stmt = $db->prepare("UPDATE services SET is_active = NOT is_active WHERE id = ?");
    $stmt->execute([$_GET['toggle_active']]);
    header('Location: services.php?msg=updated');
    exit;
}

// Add / Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $image_path = trim($_POST['image_path'] ?? '');
    $alt_text = trim($_POST['alt_text'] ?? '');
    $cta_text = trim($_POST['cta_text'] ?? 'Learn More →');
    $cta_link = trim($_POST['cta_link'] ?? 'contact.php');
    $display_order = intval($_POST['display_order'] ?? 0);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // Handle file upload
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $file_type = mime_content_type($_FILES['image_file']['tmp_name']);
        $max_size = 5 * 1024 * 1024; // 5MB

        if (!in_array($file_type, $allowed)) {
            $message = 'Invalid file type. Allowed: JPG, PNG, WebP, GIF.';
            $message_type = 'error';
        } elseif ($_FILES['image_file']['size'] > $max_size) {
            $message = 'File too large. Maximum size is 5MB.';
            $message_type = 'error';
        } else {
            $ext = pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION);
            $safe_name = preg_replace('/[^a-z0-9-]/', '-', strtolower(pathinfo($_FILES['image_file']['name'], PATHINFO_FILENAME)));
            $filename = 'service-' . $safe_name . '-' . time() . '.' . $ext;
            $dest = __DIR__ . '/../images/' . $filename;

            if (move_uploaded_file($_FILES['image_file']['tmp_name'], $dest)) {
                $image_path = 'images/' . $filename;
            } else {
                $message = 'Failed to upload file. Check folder permissions.';
                $message_type = 'error';
            }
        }
    }

    if (empty($message)) {
        if (empty($title) || empty($description)) {
            $message = 'Title and description are required.';
            $message_type = 'error';
        } else {
            if ($id) {
                $stmt = $db->prepare("UPDATE services SET title=?, description=?, image_path=?, alt_text=?, cta_text=?, cta_link=?, display_order=?, is_active=? WHERE id=?");
                $stmt->execute([$title, $description, $image_path, $alt_text, $cta_text, $cta_link, $display_order, $is_active, $id]);
            } else {
                $stmt = $db->prepare("INSERT INTO services (title, description, image_path, alt_text, cta_text, cta_link, display_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$title, $description, $image_path, $alt_text, $cta_text, $cta_link, $display_order, $is_active]);
            }
            header('Location: services.php?msg=saved');
            exit;
        }
    }
}

// Status messages
if (isset($_GET['msg'])) {
    $messages = ['saved' => 'Service saved!', 'deleted' => 'Service deleted.', 'updated' => 'Service updated.'];
    $message = $messages[$_GET['msg']] ?? '';
    $message_type = 'success';
}

// Edit item
$edit_item = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM services WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_item = $stmt->fetch();
}

// Fetch all
$services = $db->query("SELECT * FROM services ORDER BY display_order ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Services — Admin</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="admin-layout">
        <?php require_once 'includes/sidebar.php'; ?>

        <main class="admin-main">
            <div class="page-header">
                <h1>Services</h1>
                <p>Manage the services displayed on your Services page.</p>
            </div>

            <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <!-- Add/Edit Form -->
            <div class="form-card">
                <h2><?php echo $edit_item ? 'Edit Service' : 'Add New Service'; ?></h2>
                <form method="POST" action="services.php" enctype="multipart/form-data">
                    <?php if ($edit_item): ?>
                    <input type="hidden" name="id" value="<?php echo $edit_item['id']; ?>">
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="title">Title *</label>
                        <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($edit_item['title'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Description *</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required><?php echo htmlspecialchars($edit_item['description'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="image_path">Image Path</label>
                            <input type="text" class="form-control" id="image_path" name="image_path" value="<?php echo htmlspecialchars($edit_item['image_path'] ?? ''); ?>" placeholder="images/service-name.jpg">
                            <p style="font-size: 0.7rem; color: var(--admin-text-muted); margin-top: 4px;">Images stored in <code>images/</code> folder. Shown on the Services page.</p>
                        </div>
                        <div class="form-group">
                            <label for="image_file">Or Upload Image</label>
                            <input type="file" class="form-control" id="image_file" name="image_file" accept="image/jpeg,image/png,image/webp,image/gif" style="padding: 8px;">
                            <p style="font-size: 0.7rem; color: var(--admin-text-muted); margin-top: 4px;">Max 5MB. JPG, PNG, WebP, GIF.</p>
                        </div>
                    </div>

                    <?php if ($edit_item && !empty($edit_item['image_path'])): ?>
                    <div class="form-group">
                        <label>Current Image</label>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <img src="../<?php echo htmlspecialchars($edit_item['image_path']); ?>" alt="" style="width: 80px; height: 60px; object-fit: cover; border-radius: 4px; border: 1px solid var(--admin-border);">
                            <span style="font-size: 0.8rem; color: var(--admin-text-muted);"><?php echo htmlspecialchars($edit_item['image_path']); ?></span>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="alt_text">Alt Text</label>
                            <input type="text" class="form-control" id="alt_text" name="alt_text" value="<?php echo htmlspecialchars($edit_item['alt_text'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="cta_text">Button Text</label>
                            <input type="text" class="form-control" id="cta_text" name="cta_text" value="<?php echo htmlspecialchars($edit_item['cta_text'] ?? 'Learn More →'); ?>">
                        </div>
                        <div class="form-group">
                            <label for="cta_link">Button Link</label>
                            <input type="text" class="form-control" id="cta_link" name="cta_link" value="<?php echo htmlspecialchars($edit_item['cta_link'] ?? 'contact.php'); ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="display_order">Display Order</label>
                            <input type="number" class="form-control" id="display_order" name="display_order" value="<?php echo $edit_item['display_order'] ?? 0; ?>">
                        </div>
                        <div class="form-group" style="display: flex; align-items: flex-end; padding-bottom: 10px;">
                            <label>
                                <input type="checkbox" name="is_active" <?php if ($edit_item['is_active'] ?? true) echo 'checked'; ?>>
                                Active (visible on site)
                            </label>
                        </div>
                    </div>

                    <div style="display: flex; gap: 8px;">
                        <button type="submit" class="btn btn-primary"><?php echo $edit_item ? 'Update Service' : 'Add Service'; ?></button>
                        <?php if ($edit_item): ?>
                        <a href="services.php" class="btn btn-outline">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Services Table -->
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Order</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($services as $svc): ?>
                    <tr>
                        <td><img src="../<?php echo htmlspecialchars($svc['image_path']); ?>" alt=""></td>
                        <td><?php echo htmlspecialchars($svc['title']); ?></td>
                        <td>
                            <a href="services.php?toggle_active=<?php echo $svc['id']; ?>" class="badge <?php echo $svc['is_active'] ? 'badge-featured' : 'badge-read'; ?>">
                                <?php echo $svc['is_active'] ? 'Active' : 'Hidden'; ?>
                            </a>
                        </td>
                        <td><?php echo $svc['display_order']; ?></td>
                        <td>
                            <div class="actions">
                                <a href="services.php?edit=<?php echo $svc['id']; ?>" class="btn btn-outline btn-sm">Edit</a>
                                <a href="services.php?delete=<?php echo $svc['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this service?')">Delete</a>
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
