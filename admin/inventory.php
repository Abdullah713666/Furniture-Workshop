<?php
/**
 * Admin Inventory Management — Antique Furniture Workshop
 */
require_once 'auth.php';
requireLogin();

$current_admin_page = 'inventory';
$db = getDB();
$message = '';
$message_type = '';

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Update inventory
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['item_id'])) {
    if (($_POST['csrf_token'] ?? '') !== $csrf_token) { die('Invalid request.'); }
    $item_id = intval($_POST['item_id']);
    $price = floatval($_POST['price'] ?? 0);
    $status = trim($_POST['status'] ?? 'on_display');
    $condition = trim($_POST['item_condition'] ?? 'Restored');

    $stmt = $db->prepare("UPDATE gallery_items SET price=?, status=?, item_condition=? WHERE id=?");
    $stmt->execute([$price, $status, $condition, $item_id]);
    header('Location: inventory.php?msg=updated');
    exit;
}

if (isset($_GET['msg'])) {
    $message = 'Inventory updated successfully!';
    $message_type = 'success';
}

// Fetch all items with inventory data
$items = $db->query("SELECT * FROM gallery_items ORDER BY display_order ASC")->fetchAll();

// Stats
$total_items = count($items);
$on_display = count(array_filter($items, fn($i) => ($i['status'] ?? 'on_display') === 'on_display'));
$private_collection = count(array_filter($items, fn($i) => ($i['status'] ?? '') === 'private_collection'));
$commission_only = count(array_filter($items, fn($i) => ($i['status'] ?? '') === 'commission_only'));

$edit_item = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM gallery_items WHERE id = ?");
    $stmt->execute([intval($_GET['edit'])]);
    $edit_item = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory — Admin</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="admin-layout">
        <?php require_once 'includes/sidebar.php'; ?>

        <main class="admin-main">
            <div class="page-header">
                <h1>Inventory Management</h1>
                <p>Track stock levels, pricing, and item status.</p>
            </div>

            <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <!-- Stats -->
            <div class="stats-grid" style="margin-bottom: 24px;">
                <div class="stat-card">
                    <div class="stat-label">Total Items</div>
                    <div class="stat-value"><?php echo $total_items; ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">On Display</div>
                    <div class="stat-value"><?php echo $on_display; ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Private Collection</div>
                    <div class="stat-value"><?php echo $private_collection; ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Commission Only</div>
                    <div class="stat-value"><?php echo $commission_only; ?></div>
                </div>
            </div>

            <?php if ($edit_item): ?>
            <div class="form-card">
                <h2>Update Inventory — <?php echo htmlspecialchars($edit_item['title']); ?></h2>
                <form method="POST" action="inventory.php">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="item_id" value="<?php echo $edit_item['id']; ?>">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="price">Price (USD)</label>
                            <input type="number" class="form-control" id="price" name="price" step="0.01" value="<?php echo $edit_item['price'] ?? 0; ?>" placeholder="Leave 0 for 'Price on Request'">
                        </div>
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select class="form-control" id="status" name="status">
                                <option value="on_display"         <?php if (($edit_item['status'] ?? 'on_display') === 'on_display') echo 'selected'; ?>>On Display</option>
                                <option value="private_collection" <?php if (($edit_item['status'] ?? '') === 'private_collection') echo 'selected'; ?>>Private Collection</option>
                                <option value="commission_only"   <?php if (($edit_item['status'] ?? '') === 'commission_only') echo 'selected'; ?>>Commission Only</option>
                                <option value="sold"               <?php if (($edit_item['status'] ?? '') === 'sold') echo 'selected'; ?>>Sold / Disposed</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="item_condition">Condition</label>
                        <input type="text" class="form-control" id="item_condition" name="item_condition" value="<?php echo htmlspecialchars($edit_item['item_condition'] ?? 'Restored'); ?>" placeholder="e.g. Restored, Original, Fair">
                    </div>
                    <div style="display: flex; gap: 8px;">
                        <button type="submit" class="btn btn-primary">Update Inventory</button>
                        <a href="inventory.php" class="btn btn-outline">Cancel</a>
                    </div>
                </form>
            </div>
            <?php endif; ?>

            <!-- Inventory Table -->
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Title</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Condition</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td><img src="../<?php echo htmlspecialchars($item['image_path']); ?>" alt="" style="width:50px;height:38px;object-fit:cover;border-radius:4px;"></td>
                        <td><?php echo htmlspecialchars($item['title']); ?></td>
                        <td>
                            <?php if (floatval($item['price'] ?? 0) > 0): ?>
                                $<?php echo number_format($item['price'] ?? 0, 2); ?>
                            <?php else: ?>
                                <span style="color: var(--admin-text-muted);">Price on Request</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge <?php
                                $s = $item['status'] ?? 'on_display';
                                echo $s === 'on_display' ? 'badge-featured' : ($s === 'sold' ? 'badge-read' : 'badge-unread');
                            ?>"><?php echo ucfirst(str_replace('_', ' ', $s)); ?></span>
                        </td>
                        <td><?php echo htmlspecialchars($item['item_condition'] ?? 'Restored'); ?></td>
                        <td>
                            <a href="inventory.php?edit=<?php echo $item['id']; ?>" class="btn btn-outline btn-sm">Edit</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </main>
    </div>
<?php require_once __DIR__ . '/includes/particles.php'; ?>
