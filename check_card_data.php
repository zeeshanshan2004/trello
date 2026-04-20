<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Check board 3
$boardId = 3;
$board = \App\Models\Board::find($boardId);

if (!$board) {
    echo "Board 3 not found!\n";
    exit;
}

echo "Board {$board->id}: {$board->name}\n\n";

// Get archived cards for this board
$archivedCards = \App\Models\Card::whereHas('list', function($query) use ($board) {
    $query->where('board_id', $board->id);
})
->where('is_archived', true)
->with(['list'])
->orderBy('updated_at', 'desc')
->get();

echo "Archived Cards for Board {$boardId}: " . $archivedCards->count() . "\n\n";

foreach ($archivedCards as $card) {
    echo "Card ID: {$card->id}\n";
    echo "Title: {$card->title}\n";
    echo "List: {$card->list->name} (ID: {$card->list->id})\n";
    echo "Archived: YES\n";
    echo "Updated: {$card->updated_at->diffForHumans()}\n";
    echo "---\n";
}

// Also check all cards in board 3
$allCards = \App\Models\Card::whereHas('list', function($query) use ($board) {
    $query->where('board_id', $board->id);
})->with('list')->get();

echo "\nAll Cards in Board {$boardId}: " . $allCards->count() . "\n";
foreach ($allCards as $card) {
    echo "Card {$card->id}: {$card->title} - Archived: " . ($card->is_archived ? 'YES' : 'NO') . "\n";
}
