<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Workspace;
use App\Models\User;

class WorkspaceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please run UserSeeder first.');
            return;
        }

        // Create workspaces for each user
        foreach ($users as $user) {
            // Create personal workspace
            $workspace = Workspace::create([
                'name' => $user->name . "'s Workspace",
                'description' => 'Personal workspace for ' . $user->name,
                'color' => 'blue',
                'icon' => strtoupper(substr($user->name, 0, 1)),
            ]);

            // Attach user as owner
            $workspace->users()->attach($user->id, ['role' => 'owner']);
        }

        // Create shared workspace
        if ($users->count() >= 2) {
            $sharedWorkspace = Workspace::create([
                'name' => 'Trello Workspace',
                'description' => 'Shared workspace for team collaboration',
                'color' => 'green',
                'icon' => 'T',
            ]);

            // Attach all users to shared workspace
            foreach ($users as $index => $user) {
                $role = $index === 0 ? 'owner' : 'member';
                $sharedWorkspace->users()->attach($user->id, ['role' => $role]);
            }
        }

        // Create another workspace for first user
        if ($users->count() > 0) {
            $workspace2 = Workspace::create([
                'name' => strtolower($users->first()->name),
                'description' => 'Secondary workspace',
                'color' => 'blue',
                'icon' => strtoupper(substr($users->first()->name, 0, 1)),
            ]);

            $workspace2->users()->attach($users->first()->id, ['role' => 'owner']);
        }
    }
}
