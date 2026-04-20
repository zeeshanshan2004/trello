<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Card;
use App\Models\Board;

echo "=== Checking Archived Cards ===\n\n";

// Get board 3
$board = Board::find(3);
if (!$board) {
    echo "Board 3 not found!\n";
    exit;
}

echo "Board: {$board->name} (ID: {$board->id})\n\n";

// Get all cards for this board
$allCards = Card::whereHas('list', function($query) use ($board) {
    $query->where('board_id', $board->id);
})->get();

echo "Total cards in board: " . $allCards->count() . "\n";
echo "Active cards: " . $allCards->where('is_archived', false)->count() . "\n";
echo "Archived cards: " . $allCards->where('is_archived', true)->count() . "\n\n";

// Show archived cards
$archivedCards = $allCards->where('is_archived', true);
if ($archivedCards->count() > 0) {
    echo "=== Archived Cards Details ===\n";
    foreach ($archivedCards as $card) {
        echo "ID: {$card->id}\n";
        echo "Title: {$card->title}\n";
        echo "List: {$card->list->name}\n";
        echo "Archived: " . ($card->is_archived ? 'YES' : 'NO') . "\n";
        echo "Updated: {$card->updated_at}\n";
        echo "---\n";
    }
} else {
    echo "No archived cards found.\n";
}

echo "\n=== Testing API Response ===\n";
// Simulate what the API returns
$archivedCardsApi = Card::whereHas('list', function($query) use ($board) {
    $query->where('board_id', $board->id);
})
->where('is_archived', true)
->with(['list'])
->orderBy('updated_at', 'desc')
->get()
->map(function($card) {
    return [
        'id' => $card->id,
        'title' => $card->title,
        'list_name' => $card->list->name,
        'list_id' => $card->list->id,
        'archived_at' => $card->updated_at->diffForHumans(),
    ];
});

echo "API would return " . $archivedCardsApi->count() . " cards:\n";
echo json_encode($archivedCardsApi, JSON_PRETTY_PRINT);
