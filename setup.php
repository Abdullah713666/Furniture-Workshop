<?php
$host = getenv('MYSQLHOST') ?: 'localhost';
$user = getenv('MYSQLUSER') ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: getenv('MYSQL_ROOT_PASSWORD') ?: '';
$dbname = getenv('MYSQLDATABASE') ?: 'railway';
$dbport = getenv('MYSQLPORT') ?: '3306';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = new PDO("mysql:host=$host;port=$dbport;dbname=$dbname;charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        $sql = file_get_contents(__DIR__ . '/database.sql');
        $sql = preg_replace('/CREATE DATABASE.*?;\s*/i', '', $sql);
        $sql = preg_replace('/USE\s+`?\w+`?\s*;\s*/i', '', $sql);
        $sql = str_replace('TRUNCATE TABLE', 'DELETE FROM', $sql);

        $statements = array_filter(array_map('trim', explode(';', $sql)));
        $done = 0;
        $errors = [];
        foreach ($statements as $stmt) {
            if (!empty(trim($stmt))) {
                try {
                    $pdo->exec($stmt);
                    $done++;
                } catch (PDOException $e) {
                    if (strpos($e->getMessage(), 'Duplicate') === false &&
                        strpos($e->getMessage(), 'already exists') === false) {
                        $errors[] = $e->getMessage();
                    }
                }
            }
        }

        echo "<h1 style='color:green'>Done! $done queries executed.</h1>";
        if ($errors) {
            echo "<pre>" . implode("\n", $errors) . "</pre>";
        }
        echo "<a href='index.php'>Go to Website</a>";
    } catch (Exception $e) {
        echo "<h1 style='color:red'>Error: " . htmlspecialchars($e->getMessage()) . "</h1>";
    }
    exit;
}
?>
<!DOCTYPE html>
<html>
<body style="font-family:sans-serif;max-width:600px;margin:50px auto;padding:20px">
<h1>Database Setup</h1>
<p>Click the button to create all tables and seed data.</p>
<form method="POST"><button type="submit" style="padding:12px 24px;font-size:16px;cursor:pointer">Run Setup</button></form>
</body>
</html>
