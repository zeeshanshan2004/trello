<?php
$host = '127.0.0.1';
$db = 'trello_new';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "Connected to database successfully.\n";

    // 1. Add columns if they don't exist
    echo "Checking for columns...\n";
    $result = $pdo->query("SHOW COLUMNS FROM users LIKE 'is_active'");
    if ($result->rowCount() == 0) {
        echo "Adding 'is_active' column...\n";
        $pdo->exec("ALTER TABLE users ADD COLUMN is_active BOOLEAN DEFAULT 0 AFTER password");
    }
    else {
        echo "'is_active' column already exists.\n";
    }

    $result = $pdo->query("SHOW COLUMNS FROM users LIKE 'role'");
    if ($result->rowCount() == 0) {
        echo "Adding 'role' column...\n";
        $pdo->exec("ALTER TABLE users ADD COLUMN role VARCHAR(20) DEFAULT 'user' AFTER is_active");
    }
    else {
        echo "'role' column already exists.\n";
    }

    // 2. Promote admin@example.com
    echo "Promoting admin@example.com...\n";
    $stmt = $pdo->prepare("UPDATE users SET role = 'admin', is_active = 1 WHERE email = 'admin@example.com'");
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo "Admin user promoted successfully!\n";
    }
    else {
        echo "Admin user not found or already activated.\n";
    }

    echo "All operations completed successfully.\n";

}
catch (\PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
