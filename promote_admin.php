<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

// This script promotes the first user to admin and activates them
try {
    $user = User::orderBy('id', 'asc')->first();
    if ($user) {
        $user->role = 'admin';
        $user->is_active = true;
        $user->save();
        echo "User {$user->email} promoted to admin and activated successfully.\n";
    }
    else {
        echo "No users found in the database.\n";
    }
}
catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
