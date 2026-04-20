<?php
// Check workspace members - Run: php check_workspace_members.php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== WORKSPACE MEMBERS CHECK ===\n\n";

$workspaces = \App\Models\Workspace::with('users')->get();

foreach ($workspaces as $workspace) {
    echo "Workspace: {$workspace->name} (ID: {$workspace->id})\n";
    echo "Owner: " . \App\Models\User::find($workspace->user_id)->name . "\n";
    echo "Members:\n";
    
    foreach ($workspace->users as $user) {
        $role = $user->pivot->role ?? 'member';
        echo "  - {$user->name} ({$user->email}) - Role: {$role}\n";
    }
    echo "\n";
}

echo "\n=== USER WORKSPACES ===\n\n";

$users = \App\Models\User::where('role', '!=', 'admin')->get();

foreach ($users as $user) {
    echo "User: {$user->name} ({$user->email})\n";
    echo "Workspaces:\n";
    
    $userWorkspaces = $user->workspaces;
    if ($userWorkspaces->count() > 0) {
        foreach ($userWorkspaces as $ws) {
            echo "  - {$ws->name}\n";
        }
    } else {
        echo "  - No workspaces\n";
    }
    echo "\n";
}
