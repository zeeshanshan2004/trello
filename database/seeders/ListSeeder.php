<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ListModel;
use App\Models\Board;
use App\Models\Card;

class ListSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $boards = Board::all();

        if ($boards->isEmpty()) {
            $this->command->warn('No boards found. Please run BoardSeeder first.');
            return;
        }

        foreach ($boards as $board) {
            // Create default lists
            $lists = [
                ['name' => 'To Do', 'position' => 0],
                ['name' => 'Doing', 'position' => 1],
                ['name' => 'Done', 'position' => 2],
            ];

            foreach ($lists as $listData) {
                $list = ListModel::create(array_merge($listData, [
                    'board_id' => $board->id,
                ]));

                // Create sample cards for each list
                if ($listData['name'] === 'To Do') {
                    Card::create([
                        'list_id' => $list->id,
                        'title' => 'Task 1',
                        'position' => 0,
                    ]);
                    Card::create([
                        'list_id' => $list->id,
                        'title' => 'Task 2',
                        'position' => 1,
                    ]);
                } elseif ($listData['name'] === 'Doing') {
                    Card::create([
                        'list_id' => $list->id,
                        'title' => 'In Progress Task',
                        'position' => 0,
                    ]);
                } elseif ($listData['name'] === 'Done') {
                    Card::create([
                        'list_id' => $list->id,
                        'title' => 'Completed Task',
                        'position' => 0,
                    ]);
                }
            }
        }
    }
}
