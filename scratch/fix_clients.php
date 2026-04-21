<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Workspace;
use App\Models\Client;

$ws = Workspace::first();
if ($ws) {
    $count = Client::whereNull('workspace_id')->count();
    Client::whereNull('workspace_id')->update(['workspace_id' => $ws->id]);
    echo "Assigned $count clients to workspace: {$ws->name}\n";
} else {
    echo "No workspace found\n";
}
