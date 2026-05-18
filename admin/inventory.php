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

// Add inventory columns if they don't exist
try {
    $cols = $db->query("SHOW COLUMNS FROM gallery_items")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('price', $cols)) {
        $db->exec("ALTER TABLE gallery_items ADD COLUMN `price` DECIMAL(10,2) DEFAULT 0.00");
    }
    if (!in_array('quantity', $cols)) {
        $db->exec("ALTER TABLE gallery_items ADD COLUMN `quantity` INT DEFAULT 1");
    }
    if (!in_array('sku', $cols)) {
        $db->exec("ALTER TABLE gallery_items ADD COLUMN `sku` VARCHAR(50) DEFAULT ''");
    }
    if (!in_array('status', $cols)) {
        $db->exec("ALTER TABLE gallery_items ADD COLUMN `status` VARCHAR(20) DEFAULT 'available'");
    }
    if (!in_array('item_condition', $cols)) {
        $db->exec("ALTER TABLE gallery_items ADD COLUMN `item_condition` VARCHAR(50) DEFAULT 'Restored'");
    }
} catch (Exception $e) { error_log('Inventory setup: ' . $e->getMessage()); }

// Update inventory
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['item_id'])) {
    if (($_POST['csrf_token'] ?? '') !== $csrf_token) { die('Invalid request.'); }
    $item_id = intval($_POST['item_id']);
    $price = floatval($_POST['price'] ?? 0);
    $quantity = intval($_POST['quantity'] ?? 1);
    $sku = trim($_POST['sku'] ?? '');
    $status = trim($_POST['status'] ?? 'available');
    $condition = trim($_POST['item_condition'] ?? 'Restored');

    $stmt = $db->prepare("UPDATE gallery_items SET price=?, quantity=?, sku=?, status=?, item_condition=? WHERE id=?");
    $stmt->execute([$price, $quantity, $sku, $status, $condition, $item_id]);
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
$available = count(array_filter($items, fn($i) => ($i['status'] ?? 'available') === 'available'));
$sold = count(array_filter($items, fn($i) => ($i['status'] ?? '') === 'sold'));
$total_value = array_sum(array_map(fn($i) => floatval($i['price'] ?? 0) * intval($i['quantity'] ?? 1), $items));
$low_stock = count(array_filter($items, fn($i) => intval($i['quantity'] ?? 1) <= 1 && ($i['status'] ?? '') !== 'sold'));

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
                    <div class="stat-label">Available</div>
                    <div class="stat-value"><?php echo $available; ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Sold</div>
                    <div class="stat-value"><?php echo $sold; ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Total Value</div>
                    <div class="stat-value">$<?php echo number_format($total_value, 0); ?></div>
                </div>
                <div class="stat-card" <?php if ($low_stock > 0) echo 'style="border-color: #e67e22;"'; ?>>
                    <div class="stat-label">Low Stock</div>
                    <div class="stat-value"><?php echo $low_stock; ?></div>
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
                            <label for="price">Price ($)</label>
                            <input type="number" class="form-control" id="price" name="price" step="0.01" value="<?php echo $edit_item['price'] ?? 0; ?>">
                        </div>
                        <div class="form-group">
                            <label for="quantity">Quantity</label>
                            <input type="number" class="form-control" id="quantity" name="quantity" value="<?php echo $edit_item['quantity'] ?? 1; ?>">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="sku">SKU</label>
                            <input type="text" class="form-control" id="sku" name="sku" value="<?php echo htmlspecialchars($edit_item['sku'] ?? ''); ?>" placeholder="e.g. AFW-001">
                        </div>
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select class="form-control" id="status" name="status">
                                <option value="available" <?php if (($edit_item['status'] ?? '') === 'available') echo 'selected'; ?>>Available</option>
                                <option value="reserved" <?php if (($edit_item['status'] ?? '') === 'reserved') echo 'selected'; ?>>Reserved</option>
                                <option value="sold" <?php if (($edit_item['status'] ?? '') === 'sold') echo 'selected'; ?>>Sold</option>
                                <option value="restoration" <?php if (($edit_item['status'] ?? '') === 'restoration') echo 'selected'; ?>>In Restoration</option>
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
                        <th>SKU</th>
                        <th>Price</th>
                        <th>Qty</th>
                        <th>Status</th>
                        <th>Condition</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr <?php if (intval($item['quantity'] ?? 1) <= 1 && ($item['status'] ?? '') !== 'sold') echo 'style="background: rgba(230,126,34,0.05);"'; ?>>
                        <td><img src="../<?php echo htmlspecialchars($item['image_path']); ?>" alt="" style="width:50px;height:38px;object-fit:cover;border-radius:4px;"></td>
                        <td><?php echo htmlspecialchars($item['title']); ?></td>
                        <td><code><?php echo htmlspecialchars($item['sku'] ?? '—'); ?></code></td>
                        <td>$<?php echo number_format($item['price'] ?? 0, 2); ?></td>
                        <td><?php echo $item['quantity'] ?? 1; ?></td>
                        <td>
                            <span class="badge <?php 
                                $s = $item['status'] ?? 'available';
                                echo $s === 'available' ? 'badge-featured' : ($s === 'sold' ? 'badge-read' : 'badge-unread');
                            ?>"><?php echo ucfirst($item['status'] ?? 'Available'); ?></span>
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
</body>
</html>
