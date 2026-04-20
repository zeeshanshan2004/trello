<?php
// Run this file directly in browser: http://127.0.0.1:8000/fix_database.php
// Or via command line: php fix_database.php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    DB::statement('ALTER TABLE card_comments MODIFY COLUMN content LONGTEXT');
    echo "✓ Success! Database column 'content' changed to LONGTEXT\n";
    echo "Now you can save comments with images.\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
