<?php

// Run this file once to add default labels to all existing boards
// Command: php add_default_labels.php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Board;
use App\Models\Label;

$defaultLabels = [
    ['name' => 'Green', 'color' => '#61bd4f'],
    ['name' => 'Yellow', 'color' => '#f2d600'],
    ['name' => 'Orange', 'color' => '#ff9f1a'],
    ['name' => 'Red', 'color' => '#eb5a46'],
    ['name' => 'Purple', 'color' => '#c377e0'],
    ['name' => 'Blue', 'color' => '#0079bf'],
];

$boards = Board::all();

foreach ($boards as $board) {
    echo "Processing board: {$board->name} (ID: {$board->id})\n";
    
    // Check if board already has these default labels
    $existingLabels = $board->labels()->pluck('name')->toArray();
    
    foreach ($defaultLabels as $labelData) {
        if (!in_array($labelData['name'], $existingLabels)) {
            $board->labels()->create($labelData);
            echo "  - Added label: {$labelData['name']}\n";
        } else {
            echo "  - Label already exists: {$labelData['name']}\n";
        }
    }
}

echo "\nDone! Default labels added to all boards.\n";
