<?php
/**
 * Admin Categories CRUD â€” Antique Furniture Workshop
 */
require_once 'auth.php';
requireLogin();

$current_admin_page = 'categories';
$db = getDB();
$message = '';
$message_type = '';

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Delete (POST only with CSRF)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    if (($_POST['csrf_token'] ?? '') !== $csrf_token) { die('Invalid request.'); }
    $stmt = $db->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([intval($_POST['id'])]);
    header('Location: categories.php?msg=deleted');
    exit;
}

// Toggle active (POST only with CSRF)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle') {
    if (($_POST['csrf_token'] ?? '') !== $csrf_token) { die('Invalid request.'); }
    $stmt = $db->prepare("UPDATE categories SET is_active = NOT is_active WHERE id = ?");
    $stmt->execute([intval($_POST['id'])]);
    header('Location: categories.php?msg=updated');
    exit;
}

// Add / Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action'])) {
    if (($_POST['csrf_token'] ?? '') !== $csrf_token) { die('Invalid request.'); }
    $id = $_POST['id'] ?? '';
    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $display_order = intval($_POST['display_order'] ?? 0);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if (empty($slug)) {
        $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $name));
    }

    if (empty($name)) {
        $message = 'Category name is required.';
        $message_type = 'error';
    } else {
        if ($id) {
            $stmt = $db->prepare("UPDATE categories SET name=?, slug=?, description=?, display_order=?, is_active=? WHERE id=?");
            $stmt->execute([$name, $slug, $description, $display_order, $is_active, $id]);
        } else {
            $stmt = $db->prepare("INSERT INTO categories (name, slug, description, display_order, is_active) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $slug, $description, $display_order, $is_active]);
        }
        header('Location: categories.php?msg=saved');
        exit;
    }
}

if (isset($_GET['msg'])) {
    $messages = ['saved' => 'Category saved!', 'deleted' => 'Category deleted.', 'updated' => 'Category updated.'];
    $message = $messages[$_GET['msg']] ?? '';
    $message_type = 'success';
}

$edit_item = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([intval($_GET['edit'])]);
    $edit_item = $stmt->fetch();
}

$categories = $db->query("SELECT c.*, (SELECT COUNT(*) FROM gallery_items g WHERE g.category = c.slug) as item_count FROM categories c ORDER BY display_order ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories â€” Admin</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="admin-layout">
        <?php require_once 'includes/sidebar.php'; ?>

        <main class="admin-main">
            <div class="page-header">
                <h1>Categories</h1>
                <p>Manage gallery categories for organizing your collection.</p>
            </div>

            <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <div class="form-card">
                <h2><?php echo $edit_item ? 'Edit Category' : 'Add New Category'; ?></h2>
                <form method="POST" action="categories.php">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <?php if ($edit_item): ?>
                    <input type="hidden" name="id" value="<?php echo $edit_item['id']; ?>">
                    <?php endif; ?>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Name *</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($edit_item['name'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="slug">Slug</label>
                            <input type="text" class="form-control" id="slug" name="slug" value="<?php echo htmlspecialchars($edit_item['slug'] ?? ''); ?>" placeholder="auto-generated">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <input type="text" class="form-control" id="description" name="description" value="<?php echo htmlspecialchars($edit_item['description'] ?? ''); ?>">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="display_order">Display Order</label>
                            <input type="number" class="form-control" id="display_order" name="display_order" value="<?php echo $edit_item['display_order'] ?? 0; ?>">
                        </div>
                        <div class="form-group" style="padding-top: 28px;">
                            <label><input type="checkbox" name="is_active" <?php if ($edit_item['is_active'] ?? true) echo 'checked'; ?>> Active</label>
                        </div>
                    </div>

                    <div style="display: flex; gap: 8px;">
                        <button type="submit" class="btn btn-primary"><?php echo $edit_item ? 'Update' : 'Add Category'; ?></button>
                        <?php if ($edit_item): ?>
                        <a href="categories.php" class="btn btn-outline">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Items</th>
                        <th>Order</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $cat): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($cat['name']); ?></td>
                        <td><code><?php echo htmlspecialchars($cat['slug']); ?></code></td>
                        <td><?php echo $cat['item_count']; ?></td>
                        <td><?php echo $cat['display_order']; ?></td>
                        <td>
                            <form method="POST" action="categories.php" style="display:inline;">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                <input type="hidden" name="action" value="toggle">
                                <input type="hidden" name="id" value="<?php echo $cat['id']; ?>">
                                <button type="submit" class="badge <?php echo $cat['is_active'] ? 'badge-featured' : 'badge-read'; ?>" style="cursor:pointer;border:none;">
                                    <?php echo $cat['is_active'] ? 'Active' : 'Inactive'; ?>
                                </button>
                            </form>
                        </td>
                        <td>
                            <div class="actions">
                                <a href="categories.php?edit=<?php echo $cat['id']; ?>" class="btn btn-outline btn-sm">Edit</a>
                                <form method="POST" action="categories.php" style="display:inline;" onsubmit="return confirm('Delete this category?')">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $cat['id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </main>
    </div>
<?php require_once __DIR__ . '/includes/particles.php'; ?>
