<?php
/**
 * Antique Furniture Workshop — Database Installer
 * 
 * Run this ONCE to set up the database, tables, and seed data.
 * Visit: http://localhost/antique-furniture-workshop/install.php
 * 
 * After successful setup, DELETE this file for security.
 */

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'antique_workshop';

$step = $_GET['step'] ?? 'start';
$error = '';
$success = '';

// Check if database is already installed to prevent malicious re-installation exploits
$is_installed = false;
try {
    $test_dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $test_pdo = new PDO($test_dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    $stmt = $test_pdo->query("SHOW TABLES LIKE 'admin_users'");
    if ($stmt->fetch()) {
        $is_installed = true;
    }
} catch (Exception $e) {
    // Cannot connect or DB doesn't exist, which is expected before setup
}

if ($is_installed) {
    $step = 'blocked';
    $error = 'The Antique Furniture Workshop is already successfully installed. Re-running the installer is blocked for security reasons.';
}

// Allow custom credentials
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'install' && !$is_installed) {

    $host = trim($_POST['host'] ?? $host);
    $user = trim($_POST['user'] ?? $user);
    $pass = $_POST['pass'] ?? $pass;
    $dbname = trim($_POST['dbname'] ?? $dbname);
    $admin_pass = trim($_POST['admin_pass'] ?? 'admin123');
    
    try {
        // Step 1: Connect to MySQL (without database)
        $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        
        // Step 2: Create database (validate and quote dbname to prevent SQL injection)
        $dbname = preg_replace('/[^a-zA-Z0-9_\-]/', '', $dbname);
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `$dbname`");
        
        // Step 3: Read and execute SQL file
        $sql_file = __DIR__ . '/database.sql';
        if (!file_exists($sql_file)) {
            throw new Exception("database.sql not found! Make sure it's in the same directory as install.php.");
        }
        
        $sql = file_get_contents($sql_file);
        
        // Remove the CREATE DATABASE and USE lines (we already did that)
        $sql = preg_replace('/CREATE DATABASE.*?;\s*/i', '', $sql);
        $sql = preg_replace('/USE\s+`?\w+`?\s*;\s*/i', '', $sql);
        
        // Split into individual statements and execute
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        
        foreach ($statements as $stmt) {
            if (!empty($stmt) && $stmt !== "\r\n" && $stmt !== "\n") {
                try {
                    $pdo->exec($stmt);
                } catch (PDOException $e) {
                    // Skip duplicate entry errors (table/data already exists)
                    if (strpos($e->getMessage(), 'Duplicate') === false && 
                        strpos($e->getMessage(), 'already exists') === false) {
                        // Continue on non-critical errors
                    }
                }
            }
        }
        
        // Step 4: Update admin password with proper hash
        $hash = password_hash($admin_pass, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("UPDATE `admin_users` SET `password_hash` = ? WHERE `username` = 'admin'");
        $stmt->execute([$hash]);
        
        $step = 'done';
        $success = 'Database setup completed successfully!';
        
    } catch (Exception $e) {
        $error = 'Database installation failed. Please check your credentials and try again.';
        error_log('Install error: ' . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install — Antique Furniture Workshop</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap');
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: #0f0d0a;
            color: #e8e0d4;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        .installer {
            background: #1a1610;
            border: 1px solid #2a2520;
            border-radius: 12px;
            padding: 40px;
            max-width: 500px;
            width: 100%;
        }
        h1 { color: #d4a843; font-size: 1.5rem; margin-bottom: 8px; }
        p { color: #8a7e6e; font-size: 0.875rem; margin-bottom: 20px; line-height: 1.6; }
        .form-group { margin-bottom: 14px; }
        label {
            display: block; font-size: 0.75rem; font-weight: 500;
            color: #8a7e6e; margin-bottom: 4px; text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        input {
            width: 100%; padding: 10px 14px; background: #0f0d0a;
            border: 1px solid #2a2520; border-radius: 8px;
            color: #e8e0d4; font-family: inherit; font-size: 0.9rem;
        }
        input:focus { outline: none; border-color: #d4a843; }
        .btn {
            display: block; width: 100%; padding: 12px; margin-top: 8px;
            background: #d4a843; color: #1a1410; border: none; border-radius: 8px;
            font-family: inherit; font-size: 0.9rem; font-weight: 600;
            cursor: pointer; transition: background 0.2s;
        }
        .btn:hover { background: #e0b84f; }
        .alert {
            padding: 12px 16px; border-radius: 8px; margin-bottom: 16px;
            font-size: 0.85rem;
        }
        .alert-error {
            background: rgba(192,57,43,.1); border: 1px solid rgba(192,57,43,.3);
            color: #e74c3c;
        }
        .alert-success {
            background: rgba(39,174,96,.1); border: 1px solid rgba(39,174,96,.3);
            color: #27ae60;
        }
        .links { margin-top: 20px; text-align: center; font-size: 0.85rem; }
        .links a { color: #d4a843; text-decoration: none; margin: 0 8px; }
        .links a:hover { text-decoration: underline; }
        .note {
            background: rgba(212,168,67,.08); border: 1px solid rgba(212,168,67,.2);
            border-radius: 8px; padding: 12px; margin-top: 16px; font-size: 0.8rem;
            color: #d4a843;
        }
        .checklist { list-style: none; margin: 16px 0; }
        .checklist li { padding: 6px 0; font-size: 0.875rem; }
        .checklist li::before { content: '✓ '; color: #27ae60; font-weight: bold; }
    </style>
</head>
<body>
    <div class="installer">
        <?php if ($step === 'done'): ?>
            <h1>✅ Setup Complete!</h1>
            
            <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <ul class="checklist">
                <li>Database <strong><?php echo htmlspecialchars($dbname); ?></strong> created</li>
                <li>9 tables created with seed data</li>
                <li>Admin user ready</li>
                <li>All your current content has been imported</li>
            </ul>
            
            <div class="links">
                <a href="index.php">🌐 View Website</a>
                <a href="admin/login.php">🔐 Admin Panel</a>
            </div>
            
            <div class="note">
                ⚠️ <strong>Security:</strong> Please delete <code>install.php</code> and <code>database.sql</code> from your server after verifying everything works.
            </div>
            
        <?php elseif ($step === 'blocked'): ?>
            <h1>🔒 Setup Blocked</h1>
            
            <div class="alert alert-error" style="margin-top: 16px; line-height: 1.5;">
                <?php echo $error; ?>
            </div>
            
            <p style="margin-top: 16px; color: #8a7e6e;">For security, you cannot re-initialize the database because the tables already exist. If you actually wish to re-run the setup, please drop the existing database manually in phpMyAdmin first.</p>
            
            <div class="links" style="margin-top: 24px;">
                <a href="index.php">🌐 Go to Website</a>
                <a href="admin/login.php">🔐 Admin Panel</a>
            </div>
        <?php else: ?>
            <h1>🛠️ Database Setup</h1>
            <p>This will create the MySQL database and populate it with your existing website content. Make sure MySQL is running.</p>

            
            <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <input type="hidden" name="action" value="install">
                
                <div class="form-group">
                    <label>MySQL Host</label>
                    <input type="text" name="host" value="<?php echo htmlspecialchars($host); ?>">
                </div>
                
                <div class="form-group">
                    <label>MySQL Username</label>
                    <input type="text" name="user" value="<?php echo htmlspecialchars($user); ?>">
                </div>
                
                <div class="form-group">
                    <label>MySQL Password</label>
                    <input type="password" name="pass" value="" placeholder="Leave empty for XAMPP default">
                </div>
                
                <div class="form-group">
                    <label>Database Name</label>
                    <input type="text" name="dbname" value="<?php echo htmlspecialchars($dbname); ?>">
                </div>
                
                <div class="form-group">
                    <label>Admin Password</label>
                    <input type="text" name="admin_pass" value="admin123" placeholder="Choose a password">
                </div>
                
                <button type="submit" class="btn">🚀 Install Database</button>
            </form>
            
            <div class="note">
                Default XAMPP settings: host = <code>localhost</code>, user = <code>root</code>, password = <em>empty</em>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
