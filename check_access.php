<?php
use App\Models\User;
use App\Models\Workspace;
use App\Models\Board;
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$maaz = User::find(1);
$zeeshan = User::find(6);

echo "MAAZ: " . ($maaz ? $maaz->name : 'NOT FOUND') . "\n";
echo "ZEESHAN: " . ($zeeshan ? $zeeshan->name : 'NOT FOUND') . "\n\n";

if ($zeeshan) {
    echo "Zeeshan's Workspaces:\n";
    $workspaces = Workspace::whereHas('users', function ($q) use ($zeeshan) {
        $q->where('user_id', $zeeshan->id);
    })->get();

    foreach ($workspaces as $ws) {
        echo "WS ID: {$ws->id}, NAME: {$ws->name}\n";
        foreach ($ws->boards as $b) {
            $isShared = $b->sharedUsers()->where('user_id', 1)->exists();
            echo "  - BOARD ID: {$b->id}, NAME: {$b->name}, SHARED WITH MAAZ: " . ($isShared ? "YES" : "NO") . "\n";
        }
    }
}
