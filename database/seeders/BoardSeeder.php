<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Board;
use App\Models\Workspace;
use Carbon\Carbon;

class BoardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $workspaces = Workspace::all();

        if ($workspaces->isEmpty()) {
            $this->command->warn('No workspaces found. Please run WorkspaceSeeder first.');
            return;
        }

        foreach ($workspaces as $workspace) {
            // Create sample boards for each workspace
            $boards = [
                [
                    'name' => 'EASY PEASY CRM',
                    'description' => 'A Lead Management Pipeline by Crmble',
                    'background_type' => 'gradient',
                    'background_value' => 'blue',
                    'is_starred' => true,
                    'last_viewed_at' => Carbon::now()->subHours(2),
                ],
                [
                    'name' => 'Project Management',
                    'description' => null,
                    'background_type' => 'gradient',
                    'background_value' => 'purple',
                    'is_starred' => false,
                    'last_viewed_at' => Carbon::now()->subDays(1),
                ],
                [
                    'name' => 'My First Board',
                    'description' => null,
                    'background_type' => 'image',
                    'background_value' => 'https://images.unsplash.com/photo-1519681393784-d120267933ba?w=400&h=200&fit=crop',
                    'is_starred' => false,
                    'last_viewed_at' => Carbon::now()->subHours(5),
                ],
                [
                    'name' => 'Team Board',
                    'description' => 'Collaboration board',
                    'background_type' => 'image',
                    'background_value' => 'https://images.unsplash.com/photo-1519681393784-d120267933ba?w=400&h=200&fit=crop',
                    'is_starred' => true,
                    'last_viewed_at' => Carbon::now()->subMinutes(30),
                ],
                [
                    'name' => 'Public Board',
                    'description' => null,
                    'background_type' => 'image',
                    'background_value' => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=400&h=200&fit=crop',
                    'is_starred' => false,
                    'last_viewed_at' => Carbon::now()->subDays(2),
                ],
            ];

            foreach ($boards as $boardData) {
                Board::create(array_merge($boardData, [
                    'workspace_id' => $workspace->id,
                ]));
            }
        }
    }
}
