<?php
// Simple database check without Laravel bootstrap
// This bypasses the PHP version issue

$host = 'localhost';
$dbname = 'trello_new'; // Updated from .env
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== CHECKING LABELS FOR BOARD 53 ===\n\n";
    
    // Check if board exists
    $stmt = $pdo->prepare("SELECT id, name FROM boards WHERE id = 53");
    $stmt->execute();
    $board = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$board) {
        echo "ERROR: Board 53 not found!\n";
        exit;
    }
    
    echo "Board Found: {$board['name']} (ID: {$board['id']})\n\n";
    
    // Get labels
    $stmt = $pdo->prepare("SELECT id, board_id, name, color, created_at FROM labels WHERE board_id = 53 ORDER BY id");
    $stmt->execute();
    $labels = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Total Labels: " . count($labels) . "\n\n";
    
    if (count($labels) === 0) {
        echo "NO LABELS FOUND FOR THIS BOARD!\n";
        echo "\nTo create default labels, run this SQL:\n\n";
        echo "INSERT INTO labels (board_id, name, color, created_at, updated_at) VALUES\n";
        echo "(53, 'Green', '#61bd4f', NOW(), NOW()),\n";
        echo "(53, 'Yellow', '#f2d600', NOW(), NOW()),\n";
        echo "(53, 'Orange', '#ff9f1a', NOW(), NOW()),\n";
        echo "(53, 'Red', '#eb5a46', NOW(), NOW()),\n";
        echo "(53, 'Purple', '#c377e0', NOW(), NOW()),\n";
        echo "(53, 'Blue', '#0079bf', NOW(), NOW());\n";
    } else {
        echo "Labels Found:\n";
        echo str_repeat("-", 80) . "\n";
        printf("%-5s %-10s %-20s %-10s %-20s\n", "ID", "Board ID", "Name", "Color", "Created At");
        echo str_repeat("-", 80) . "\n";
        
        foreach ($labels as $label) {
            printf("%-5s %-10s %-20s %-10s %-20s\n", 
                $label['id'], 
                $label['board_id'], 
                $label['name'], 
                $label['color'], 
                $label['created_at']
            );
        }
        
        // Check for duplicates
        $names = array_column($labels, 'name');
        $duplicates = array_diff_assoc($names, array_unique($names));
        
        if (!empty($duplicates)) {
            echo "\n⚠️  WARNING: Duplicate label names found!\n";
            $nameCount = array_count_values($names);
            foreach ($nameCount as $name => $count) {
                if ($count > 1) {
                    echo "  - '$name' appears $count times\n";
                }
            }
            echo "\nYou may want to delete duplicates.\n";
        }
    }
    
} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
    echo "\nPlease update the database credentials at the top of this file.\n";
}
