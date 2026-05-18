<?php
/**
 * Admin Gallery CRUD — Antique Furniture Workshop
 */
require_once 'auth.php';
requireLogin();

$current_admin_page = 'gallery';
$db = getDB();
$message = '';
$message_type = '';

// --- Handle Actions ---
// Delete
if (isset($_GET['delete'])) {
    $stmt = $db->prepare("DELETE FROM gallery_items WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header('Location: gallery.php?msg=deleted');
    exit;
}

// Toggle featured
if (isset($_GET['toggle_featured'])) {
    $stmt = $db->prepare("UPDATE gallery_items SET is_featured = NOT is_featured WHERE id = ?");
    $stmt->execute([$_GET['toggle_featured']]);
    header('Location: gallery.php?msg=updated');
    exit;
}

// Add / Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = trim($_POST['category'] ?? 'restoration');
    $image_path = trim($_POST['image_path'] ?? '');
    $alt_text = trim($_POST['alt_text'] ?? '');
    $tag = trim($_POST['tag'] ?? '');
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $display_order = intval($_POST['display_order'] ?? 0);

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
            $filename = $safe_name . '-' . time() . '.' . $ext;
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
        if (empty($title) || empty($image_path)) {
            $message = 'Title and image path are required (upload a file or type a path).';
            $message_type = 'error';
        } else {
            if ($id) {
                // Update
                $stmt = $db->prepare("UPDATE gallery_items SET title=?, description=?, category=?, image_path=?, alt_text=?, tag=?, is_featured=?, display_order=? WHERE id=?");
                $stmt->execute([$title, $description, $category, $image_path, $alt_text, $tag, $is_featured, $display_order, $id]);
            } else {
                // Insert
                $stmt = $db->prepare("INSERT INTO gallery_items (title, description, category, image_path, alt_text, tag, is_featured, display_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$title, $description, $category, $image_path, $alt_text, $tag, $is_featured, $display_order]);
            }
            header('Location: gallery.php?msg=saved');
            exit;
        }
    }
}

// Status messages
if (isset($_GET['msg'])) {
    $messages = ['saved' => 'Item saved successfully!', 'deleted' => 'Item deleted.', 'updated' => 'Item updated.'];
    $message = $messages[$_GET['msg']] ?? '';
    $message_type = 'success';
}

// Get edit item if editing
$edit_item = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM gallery_items WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_item = $stmt->fetch();
}

// Fetch all items
$items = $db->query("SELECT * FROM gallery_items ORDER BY display_order ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery — Admin</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="admin-layout">
        <?php require_once 'includes/sidebar.php'; ?>

        <main class="admin-main">
            <div class="page-header">
                <h1>Gallery Items</h1>
                <p>Manage your collection pieces and featured items.</p>
            </div>

            <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <!-- Add/Edit Form -->
            <div class="form-card">
                <h2><?php echo $edit_item ? 'Edit Item' : 'Add New Item'; ?></h2>
                <form method="POST" action="gallery.php" enctype="multipart/form-data">
                    <?php if ($edit_item): ?>
                    <input type="hidden" name="id" value="<?php echo $edit_item['id']; ?>">
                    <?php endif; ?>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="title">Title *</label>
                            <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($edit_item['title'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="category">Category</label>
                            <select class="form-control" id="category" name="category">
                                <option value="restoration" <?php if (($edit_item['category'] ?? '') === 'restoration') echo 'selected'; ?>>Restoration</option>
                                <option value="handcrafted" <?php if (($edit_item['category'] ?? '') === 'handcrafted') echo 'selected'; ?>>Handcrafted</option>
                                <option value="baroque" <?php if (($edit_item['category'] ?? '') === 'baroque') echo 'selected'; ?>>Baroque</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <input type="text" class="form-control" id="description" name="description" value="<?php echo htmlspecialchars($edit_item['description'] ?? ''); ?>" placeholder="e.g. c. 1870 — Full French polish restoration">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="image_path">Image Path</label>
                            <input type="text" class="form-control" id="image_path" name="image_path" value="<?php echo htmlspecialchars($edit_item['image_path'] ?? ''); ?>" placeholder="images/my-piece.jpg">
                            <p style="font-size: 0.7rem; color: var(--admin-text-muted); margin-top: 4px;">All images are stored in the <code>images/</code> folder. Shown on Homepage (featured) and Gallery page.</p>
                        </div>
                        <div class="form-group">
                            <label for="image_file">Or Upload Image</label>
                            <input type="file" class="form-control" id="image_file" name="image_file" accept="image/jpeg,image/png,image/webp,image/gif" style="padding: 8px;">
                            <p style="font-size: 0.7rem; color: var(--admin-text-muted); margin-top: 4px;">Max 5MB. JPG, PNG, WebP, GIF. Overrides path above if uploaded.</p>
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
                            <label for="tag">Tag</label>
                            <input type="text" class="form-control" id="tag" name="tag" value="<?php echo htmlspecialchars($edit_item['tag'] ?? ''); ?>" placeholder="e.g. Restored, Handcrafted">
                        </div>
                        <div class="form-group">
                            <label for="display_order">Display Order</label>
                            <input type="number" class="form-control" id="display_order" name="display_order" value="<?php echo $edit_item['display_order'] ?? 0; ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="is_featured" <?php if ($edit_item['is_featured'] ?? false) echo 'checked'; ?>>
                            Featured on homepage
                        </label>
                    </div>

                    <div style="display: flex; gap: 8px;">
                        <button type="submit" class="btn btn-primary"><?php echo $edit_item ? 'Update Item' : 'Add Item'; ?></button>
                        <?php if ($edit_item): ?>
                        <a href="gallery.php" class="btn btn-outline">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Items Table -->
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Featured</th>
                        <th>Order</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td><img src="../<?php echo htmlspecialchars($item['image_path']); ?>" alt=""></td>
                        <td><?php echo htmlspecialchars($item['title']); ?></td>
                        <td><?php echo htmlspecialchars(ucfirst($item['category'])); ?></td>
                        <td>
                            <a href="gallery.php?toggle_featured=<?php echo $item['id']; ?>" class="badge <?php echo $item['is_featured'] ? 'badge-featured' : 'badge-read'; ?>">
                                <?php echo $item['is_featured'] ? '★ Featured' : 'No'; ?>
                            </a>
                        </td>
                        <td><?php echo $item['display_order']; ?></td>
                        <td>
                            <div class="actions">
                                <a href="gallery.php?edit=<?php echo $item['id']; ?>" class="btn btn-outline btn-sm">Edit</a>
                                <a href="gallery.php?delete=<?php echo $item['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this item?')">Delete</a>
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
