<?php

namespace Database\Factories;

use App\Models\Board;
use Illuminate\Database\Eloquent\Factories\Factory;

class ListModelFactory extends Factory
{
    public function definition(): array
    {
        return [
            'board_id' => Board::factory(),
            'name' => fake()->words(2, true),
            'position' => fake()->numberBetween(0, 100),
            'is_archived' => false,
        ];
    }
}
