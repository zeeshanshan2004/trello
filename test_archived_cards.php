<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Get board ID (assuming board 2 from your screenshots)
$boardId = 2;

$board = \App\Models\Board::find($boardId);

if (!$board) {
    echo "Board not found!\n";
    exit;
}

echo "Board: {$board->name}\n\n";

// Get archived cards
$archivedCards = \App\Models\Card::whereHas('list', function($query) use ($board) {
    $query->where('board_id', $board->id);
})
->where('is_archived', true)
->with(['list'])
->orderBy('updated_at', 'desc')
->get();

echo "Archived Cards Count: " . $archivedCards->count() . "\n\n";

foreach ($archivedCards as $card) {
    echo "Card ID: {$card->id}\n";
    echo "Title: {$card->title}\n";
    echo "List: {$card->list->name}\n";
    echo "Archived: " . ($card->is_archived ? 'Yes' : 'No') . "\n";
    echo "Updated: {$card->updated_at->diffForHumans()}\n";
    echo "---\n";
}
