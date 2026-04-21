<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Client;

$dss_id = 7;
$count = Client::where('workspace_id', '!=', $dss_id)->orWhereNull('workspace_id')->update(['workspace_id' => $dss_id]);
echo "Moved $count clients to DSS (ID: $dss_id)\n";
