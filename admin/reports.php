<?php
/**
 * Admin Reports Dashboard — Antique Furniture Workshop
 */
require_once 'auth.php';
requireLogin();

$current_admin_page = 'reports';
$db = getDB();

// Gather data
$gallery_count = $db->query("SELECT COUNT(*) FROM gallery_items")->fetchColumn();
$services_count = $db->query("SELECT COUNT(*) FROM services")->fetchColumn();
$messages_count = $db->query("SELECT COUNT(*) FROM contact_submissions")->fetchColumn();
$unread_count = $db->query("SELECT COUNT(*) FROM contact_submissions WHERE is_read = 0")->fetchColumn();

try { $users_count = $db->query("SELECT COUNT(*) FROM users")->fetchColumn(); } catch(Exception $e) { $users_count = 0; }

// Category breakdown
try {
    $cat_data = $db->query("SELECT category, COUNT(*) as cnt FROM gallery_items GROUP BY category ORDER BY cnt DESC")->fetchAll();
} catch(Exception $e) { $cat_data = []; }

// Monthly messages (last 6 months)
$msg_monthly = [];
for ($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $label = date('M Y', strtotime("-$i months"));
    try {
        $cnt = $db->prepare("SELECT COUNT(*) FROM contact_submissions WHERE DATE_FORMAT(submitted_at, '%Y-%m') = ?");
        $cnt->execute([$month]);
        $msg_monthly[] = ['label' => $label, 'count' => $cnt->fetchColumn()];
    } catch(Exception $e) {
        $msg_monthly[] = ['label' => $label, 'count' => 0];
    }
}

// Monthly sales (last 6 months)
$sales_monthly = [];
$total_revenue = 0;
try {
    $db->query("SELECT 1 FROM transactions LIMIT 1");
    for ($i = 5; $i >= 0; $i--) {
        $month = date('Y-m', strtotime("-$i months"));
        $label = date('M Y', strtotime("-$i months"));
        $cnt = $db->prepare("SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE status='completed' AND DATE_FORMAT(transaction_date, '%Y-%m') = ?");
        $cnt->execute([$month]);
        $val = $cnt->fetchColumn();
        $sales_monthly[] = ['label' => $label, 'amount' => floatval($val)];
    }
    $total_revenue = $db->query("SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE status='completed'")->fetchColumn();
} catch(Exception $e) {
    for ($i = 5; $i >= 0; $i--) {
        $sales_monthly[] = ['label' => date('M Y', strtotime("-$i months")), 'amount' => 0];
    }
}

// Inventory status breakdown
try {
    $inv_status = $db->query("SELECT COALESCE(status, 'available') as s, COUNT(*) as cnt FROM gallery_items GROUP BY s")->fetchAll();
} catch(Exception $e) { $inv_status = []; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports — Admin</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        .charts-grid { display: grid; grid-template-columns: 1fr; gap: 24px; margin-top: 24px; }
        @media (min-width: 768px) { .charts-grid { grid-template-columns: 1fr 1fr; } }
        .chart-card { background: var(--admin-bg-card, #1e1a15); border: 1px solid var(--admin-border, #3d3225); border-radius: 12px; padding: 24px; }
        .chart-card h3 { font-size: 0.95rem; margin-bottom: 16px; color: var(--admin-text, #f5f0e8); }
        .chart-card canvas { max-height: 280px; }
    </style>
</head>
<body>
    <div class="admin-layout">
        <?php require_once 'includes/sidebar.php'; ?>

        <main class="admin-main">
            <div class="page-header">
                <h1>Reports & Analytics</h1>
                <p>Overview of your business performance and site activity.</p>
            </div>

            <!-- Summary Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-label">Total Revenue</div>
                    <div class="stat-value">$<?php echo number_format($total_revenue, 0); ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Gallery Items</div>
                    <div class="stat-value"><?php echo $gallery_count; ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Messages</div>
                    <div class="stat-value"><?php echo $messages_count; ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Unread</div>
                    <div class="stat-value"><?php echo $unread_count; ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Registered Users</div>
                    <div class="stat-value"><?php echo $users_count; ?></div>
                </div>
            </div>

            <!-- Charts -->
            <div class="charts-grid">
                <div class="chart-card">
                    <h3>Monthly Sales Revenue</h3>
                    <canvas id="salesChart"></canvas>
                </div>
                <div class="chart-card">
                    <h3>Monthly Messages</h3>
                    <canvas id="messagesChart"></canvas>
                </div>
                <div class="chart-card">
                    <h3>Items by Category</h3>
                    <canvas id="categoryChart"></canvas>
                </div>
                <div class="chart-card">
                    <h3>Inventory Status</h3>
                    <canvas id="inventoryChart"></canvas>
                </div>
            </div>
        </main>
    </div>

    <script>
    const goldColor = '#d4a843';
    const goldAlpha = 'rgba(212,168,67,0.3)';
    const chartTextColor = '#a09080';
    const gridColor = 'rgba(61,50,37,0.5)';

    Chart.defaults.color = chartTextColor;
    Chart.defaults.borderColor = gridColor;

    // Sales Chart
    new Chart(document.getElementById('salesChart'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_column($sales_monthly, 'label')); ?>,
            datasets: [{
                label: 'Revenue ($)',
                data: <?php echo json_encode(array_column($sales_monthly, 'amount')); ?>,
                backgroundColor: goldAlpha,
                borderColor: goldColor,
                borderWidth: 1,
                borderRadius: 6
            }]
        },
        options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
    });

    // Messages Chart
    new Chart(document.getElementById('messagesChart'), {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_column($msg_monthly, 'label')); ?>,
            datasets: [{
                label: 'Messages',
                data: <?php echo json_encode(array_column($msg_monthly, 'count')); ?>,
                borderColor: goldColor,
                backgroundColor: goldAlpha,
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointBackgroundColor: goldColor
            }]
        },
        options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
    });

    // Category Pie Chart
    new Chart(document.getElementById('categoryChart'), {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode(array_map(fn($c) => ucfirst($c['category']), $cat_data)); ?>,
            datasets: [{
                data: <?php echo json_encode(array_column($cat_data, 'cnt')); ?>,
                backgroundColor: ['#d4a843', '#8b6914', '#c09030', '#e6bc5a', '#a08040', '#6b5020'],
                borderColor: '#1e1a15',
                borderWidth: 2
            }]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });

    // Inventory Status Chart
    new Chart(document.getElementById('inventoryChart'), {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode(array_map(fn($s) => ucfirst($s['s']), $inv_status)); ?>,
            datasets: [{
                data: <?php echo json_encode(array_column($inv_status, 'cnt')); ?>,
                backgroundColor: ['#27ae60', '#e67e22', '#e74c3c', '#3498db', '#9b59b6'],
                borderColor: '#1e1a15',
                borderWidth: 2
            }]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });
    </script>
</body>
</html>
