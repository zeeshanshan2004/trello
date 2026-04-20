<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

echo "\n--- SQL QUERY FOR USER WHERE IN ---\n";
echo User::whereIn('id', [12])->toSql();
echo "\n--- BINDINGS ---\n";
print_r(User::whereIn('id', [12])->getBindings());

echo "\n--- RAW DB TABLE USERS WHERE IN ---\n";
$users = \Illuminate\Support\Facades\DB::table('users')->whereIn('id', [12])->get();
echo $users->toJson();

echo "\n--- ELOQUENT USER WHERE IN ---\n";
$eloquentUsers = User::whereIn('id', [12])->get();
echo $eloquentUsers->toJson();
