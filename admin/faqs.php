<?php
/**
 * Admin FAQ CRUD  Antique Furniture Workshop
 */
require_once 'auth.php';
requireLogin();

$current_admin_page = 'faqs';
$db = getDB();
$message = '';
$message_type = '';

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Delete (POST only with CSRF)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    if (($_POST['csrf_token'] ?? '') !== $csrf_token) {
        http_response_code(403); die('Forbidden');
    }
    $id = intval($_POST['id'] ?? 0);
    $stmt = $db->prepare("DELETE FROM faqs WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: faqs.php?msg=deleted');
    exit;
}

// Add / Edit (POST with CSRF)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action'])) {
    if (($_POST['csrf_token'] ?? '') !== $csrf_token) {
        http_response_code(403); die('Forbidden');
    }

    $id = $_POST['id'] ?? '';
    $question = trim($_POST['question'] ?? '');
    $answer = trim($_POST['answer'] ?? '');
    $display_order = intval($_POST['display_order'] ?? 0);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if (empty($question) || empty($answer)) {
        $message = 'Question and Answer are required.';
        $message_type = 'error';
    } else {
        $len_err = validate_length($question, 500, 'Question')
                ?: validate_length($answer, 10000, 'Answer');
        if ($len_err) {
            $message = $len_err;
            $message_type = 'error';
        }
    }

    if (empty($message)) {
        if ($id) {
            $stmt = $db->prepare("UPDATE faqs SET question=?, answer=?, display_order=?, is_active=? WHERE id=?");
            $stmt->execute([$question, $answer, $display_order, $is_active, $id]);
        } else {
            $stmt = $db->prepare("INSERT INTO faqs (question, answer, display_order, is_active) VALUES (?, ?, ?, ?)");
            $stmt->execute([$question, $answer, $display_order, $is_active]);
        }
        header('Location: faqs.php?msg=saved');
        exit;
    }
}

// Status messages
if (isset($_GET['msg'])) {
    $messages = ['saved' => 'FAQ saved!', 'deleted' => 'FAQ deleted.'];
    $message = $messages[$_GET['msg']] ?? '';
    $message_type = 'success';
}

// Edit item
$edit_item = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM faqs WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_item = $stmt->fetch();
}

// Fetch all
$faqs = $db->query("SELECT * FROM faqs ORDER BY display_order ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQs  Admin</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="admin-layout">
        <?php require_once 'includes/sidebar.php'; ?>

        <main class="admin-main">
            <div class="page-header">
                <h1>Frequently Asked Questions</h1>
                <p>Manage customer questions and answers displayed on the FAQ page.</p>
            </div>

            <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <!-- Add/Edit Form -->
            <div class="form-card">
                <h2><?php echo $edit_item ? ' Edit FAQ' : ' Add New FAQ'; ?></h2>
                <form method="POST" action="faqs.php">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <?php if ($edit_item): ?>
                    <input type="hidden" name="id" value="<?php echo $edit_item['id']; ?>">
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="question">Question *</label>
                        <input type="text" class="form-control" id="question" name="question" value="<?php echo htmlspecialchars($edit_item['question'] ?? ''); ?>" placeholder="e.g. How long does restoration take?" required>
                    </div>

                    <div class="form-group">
                        <label for="answer">Answer *</label>
                        <textarea class="form-control" id="answer" name="answer" rows="4" required><?php echo htmlspecialchars($edit_item['answer'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="display_order">Display Order</label>
                            <input type="number" class="form-control" id="display_order" name="display_order" value="<?php echo $edit_item['display_order'] ?? 0; ?>">
                        </div>
                        <div class="form-group" style="display: flex; align-items: flex-end; padding-bottom: 10px;">
                            <label class="checkbox-container" style="display: flex; align-items: center; cursor: pointer; gap: 8px;">
                                <input type="checkbox" name="is_active" <?php echo (!isset($edit_item) || $edit_item['is_active']) ? 'checked' : ''; ?>>
                                <span style="font-size: 0.9rem; color: #fff;">Visible on Website</span>
                            </label>
                        </div>
                    </div>

                    <div style="display: flex; gap: 8px;">
                        <button type="submit" class="btn btn-primary"><?php echo $edit_item ? 'Update FAQ' : 'Add FAQ'; ?></button>
                        <?php if ($edit_item): ?>
                        <a href="faqs.php" class="btn btn-outline">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- FAQs Table -->
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Question</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($faqs)): ?>
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 40px; color: #666;">No FAQs found. Add your first one above!</td>
                    </tr>
                    <?php endif; ?>
                    <?php foreach ($faqs as $faq): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($faq['display_order']); ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($faq['question']); ?></strong>
                            <div style="font-size: 0.8rem; color: #888; margin-top: 4px;">
                                <?php echo htmlspecialchars(substr($faq['answer'], 0, 100)) . (strlen($faq['answer']) > 100 ? '...' : ''); ?>
                            </div>
                        </td>
                        <td>
                            <?php if ($faq['is_active']): ?>
                                <span class="badge badge-success">Active</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="actions" style="display:flex; gap:8px;">
                                <a href="faqs.php?edit=<?php echo $faq['id']; ?>" class="btn btn-outline btn-sm">Edit</a>
                                <form method="POST" action="faqs.php" style="display:inline;" onsubmit="return confirm('Delete this FAQ?')">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $faq['id']; ?>">
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
