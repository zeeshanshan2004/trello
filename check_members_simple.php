<?php
// Simple check - Direct PDO connection

$host = '127.0.0.1';
$database = 'trello_new';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== USERS ===\n";
    $users = $pdo->query("SELECT id, name, email, role FROM users ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($users as $user) {
        echo "ID: {$user['id']} | Name: {$user['name']} | Email: {$user['email']} | Role: {$user['role']}\n";
    }
    
    echo "\n=== WORKSPACES ===\n";
    $workspaces = $pdo->query("SELECT id, name FROM workspaces ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($workspaces as $ws) {
        $owner = $pdo->query("
            SELECT u.name 
            FROM users u 
            JOIN workspace_user wu ON u.id = wu.user_id 
            WHERE wu.workspace_id = {$ws['id']} AND wu.role = 'owner'
            LIMIT 1
        ")->fetch(PDO::FETCH_ASSOC);
        $ownerName = $owner ? $owner['name'] : 'No owner';
        echo "ID: {$ws['id']} | Name: {$ws['name']} | Owner: {$ownerName}\n";
    }
    
    echo "\n=== WORKSPACE MEMBERS (workspace_user table) ===\n";
    $members = $pdo->query("
        SELECT wu.workspace_id, w.name as workspace_name, wu.user_id, u.name as user_name, wu.role
        FROM workspace_user wu
        JOIN workspaces w ON wu.workspace_id = w.id
        JOIN users u ON wu.user_id = u.id
        ORDER BY wu.workspace_id, wu.user_id
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($members as $member) {
        echo "Workspace: {$member['workspace_name']} (ID: {$member['workspace_id']}) | User: {$member['user_name']} (ID: {$member['user_id']}) | Role: {$member['role']}\n";
    }
    
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
