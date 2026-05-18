<?php
/**
 * Admin Sales & Transactions — Antique Furniture Workshop
 */
require_once 'auth.php';
requireLogin();

$current_admin_page = 'transactions';
$db = getDB();
$message = '';
$message_type = '';

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Create transactions table
try {
    $db->exec("CREATE TABLE IF NOT EXISTS `transactions` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `item_id` INT DEFAULT NULL,
        `item_title` VARCHAR(255) NOT NULL,
        `buyer_name` VARCHAR(255) NOT NULL,
        `buyer_email` VARCHAR(255) DEFAULT '',
        `amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        `payment_method` VARCHAR(50) DEFAULT 'cash',
        `status` VARCHAR(20) DEFAULT 'completed',
        `notes` TEXT,
        `transaction_date` DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");
} catch (Exception $e) { error_log('Transactions setup: ' . $e->getMessage()); }

// Delete (POST only with CSRF)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    if (($_POST['csrf_token'] ?? '') !== $csrf_token) { die('Invalid request.'); }
    $stmt = $db->prepare("DELETE FROM transactions WHERE id = ?");
    $stmt->execute([intval($_POST['id'])]);
    header('Location: transactions.php?msg=deleted');
    exit;
}

// Add transaction
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action'])) {
    if (($_POST['csrf_token'] ?? '') !== $csrf_token) { die('Invalid request.'); }
    $item_id = intval($_POST['item_id'] ?? 0);
    $item_title = trim($_POST['item_title'] ?? '');
    $buyer_name = trim($_POST['buyer_name'] ?? '');
    $buyer_email = trim($_POST['buyer_email'] ?? '');
    $amount = floatval($_POST['amount'] ?? 0);
    $payment_method = trim($_POST['payment_method'] ?? 'cash');
    $status = trim($_POST['status'] ?? 'completed');
    $notes = trim($_POST['notes'] ?? '');
    $transaction_date = $_POST['transaction_date'] ?? date('Y-m-d H:i:s');

    if (empty($buyer_name) || empty($item_title)) {
        $message = 'Buyer name and item title are required.';
        $message_type = 'error';
    } else {
        $stmt = $db->prepare("INSERT INTO transactions (item_id, item_title, buyer_name, buyer_email, amount, payment_method, status, notes, transaction_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$item_id ?: null, $item_title, $buyer_name, $buyer_email, $amount, $payment_method, $status, $notes, $transaction_date]);

        // Update item status to sold if item_id provided
        if ($item_id > 0 && $status === 'completed') {
            $db->prepare("UPDATE gallery_items SET status = 'sold', quantity = GREATEST(quantity - 1, 0) WHERE id = ?")->execute([$item_id]);
        }

        header('Location: transactions.php?msg=saved');
        exit;
    }
}

if (isset($_GET['msg'])) {
    $msgs = ['saved' => 'Transaction recorded!', 'deleted' => 'Transaction deleted.'];
    $message = $msgs[$_GET['msg']] ?? '';
    $message_type = 'success';
}

// Fetch transactions
$transactions = $db->query("SELECT * FROM transactions ORDER BY transaction_date DESC LIMIT 100")->fetchAll();

// Fetch gallery items for dropdown
$gallery_items = $db->query("SELECT id, title, price FROM gallery_items ORDER BY title ASC")->fetchAll();

// Stats
$total_sales = 0;
$monthly_sales = 0;
$current_month = date('Y-m');
foreach ($transactions as $t) {
    if ($t['status'] === 'completed') {
        $total_sales += $t['amount'];
        if (substr($t['transaction_date'], 0, 7) === $current_month) {
            $monthly_sales += $t['amount'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales — Admin</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="admin-layout">
        <?php require_once 'includes/sidebar.php'; ?>

        <main class="admin-main">
            <div class="page-header">
                <h1>Sales & Transactions</h1>
                <p>Record and track all sales and transactions.</p>
            </div>

            <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <!-- Stats -->
            <div class="stats-grid" style="margin-bottom: 24px;">
                <div class="stat-card">
                    <div class="stat-label">Total Revenue</div>
                    <div class="stat-value">$<?php echo number_format($total_sales, 0); ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">This Month</div>
                    <div class="stat-value">$<?php echo number_format($monthly_sales, 0); ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Total Transactions</div>
                    <div class="stat-value"><?php echo count($transactions); ?></div>
                </div>
            </div>

            <!-- Add Transaction -->
            <div class="form-card">
                <h2>Record New Sale</h2>
                <form method="POST" action="transactions.php">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="item_id">Item (from gallery)</label>
                            <select class="form-control" id="item_id" name="item_id" onchange="fillItemDetails(this)">
                                <option value="0">— Select or type manually —</option>
                                <?php foreach ($gallery_items as $gi): ?>
                                <option value="<?php echo $gi['id']; ?>" data-title="<?php echo htmlspecialchars($gi['title']); ?>" data-price="<?php echo $gi['price'] ?? 0; ?>">
                                    <?php echo htmlspecialchars($gi['title']); ?> ($<?php echo number_format($gi['price'] ?? 0, 2); ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="item_title">Item Title *</label>
                            <input type="text" class="form-control" id="item_title" name="item_title" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="buyer_name">Buyer Name *</label>
                            <input type="text" class="form-control" id="buyer_name" name="buyer_name" required>
                        </div>
                        <div class="form-group">
                            <label for="buyer_email">Buyer Email</label>
                            <input type="email" class="form-control" id="buyer_email" name="buyer_email">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="amount">Amount ($) *</label>
                            <input type="number" class="form-control" id="amount" name="amount" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label for="payment_method">Payment Method</label>
                            <select class="form-control" id="payment_method" name="payment_method">
                                <option value="cash">Cash</option>
                                <option value="card">Credit/Debit Card</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="check">Check</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select class="form-control" id="status" name="status">
                                <option value="completed">Completed</option>
                                <option value="pending">Pending</option>
                                <option value="refunded">Refunded</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="transaction_date">Date</label>
                            <input type="datetime-local" class="form-control" id="transaction_date" name="transaction_date" value="<?php echo date('Y-m-d\TH:i'); ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="notes">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="2" placeholder="Optional notes..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">Record Sale</button>
                </form>
            </div>

            <!-- Transactions Table -->
            <?php if (!empty($transactions)): ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Item</th>
                        <th>Buyer</th>
                        <th>Amount</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $t): ?>
                    <tr>
                        <td><?php echo date('M j, Y', strtotime($t['transaction_date'])); ?></td>
                        <td><?php echo htmlspecialchars($t['item_title']); ?></td>
                        <td><?php echo htmlspecialchars($t['buyer_name']); ?></td>
                        <td>$<?php echo number_format($t['amount'], 2); ?></td>
                        <td><?php echo ucfirst(str_replace('_', ' ', $t['payment_method'])); ?></td>
                        <td>
                            <span class="badge <?php echo $t['status'] === 'completed' ? 'badge-featured' : ($t['status'] === 'refunded' ? 'badge-read' : 'badge-unread'); ?>">
                                <?php echo ucfirst($t['status']); ?>
                            </span>
                        </td>
                        <td>
                            <form method="POST" action="transactions.php" style="display:inline;" onsubmit="return confirm('Delete this transaction?')">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $t['id']; ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="form-card"><p style="color: var(--admin-text-muted);">No transactions recorded yet.</p></div>
            <?php endif; ?>
        </main>
    </div>
    <script>
    function fillItemDetails(select) {
        var opt = select.options[select.selectedIndex];
        if (opt.value !== '0') {
            document.getElementById('item_title').value = opt.getAttribute('data-title') || '';
            document.getElementById('amount').value = opt.getAttribute('data-price') || '';
        }
    }
    </script>
</body>
</html>
