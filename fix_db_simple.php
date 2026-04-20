<?php
// Simple database fix script - Run this: php fix_db_simple.php

$host = '127.0.0.1';
$database = 'trello_new';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database...\n";
    
    $pdo->exec('ALTER TABLE card_comments MODIFY COLUMN content LONGTEXT');
    
    echo "✓ SUCCESS! Column 'content' changed to LONGTEXT\n";
    echo "Now refresh your page and try saving comment with image.\n";
    
} catch (PDOException $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
}
