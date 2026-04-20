<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

try {
    $columns = Schema::getColumnListing('users');
    echo "Columns in 'users' table:\n";
    print_r($columns);

    $users = DB::table('users')->get(['id', 'email', 'role', 'is_active']);
    echo "\nUsers data:\n";
    print_r($users->toArray());
}
catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
