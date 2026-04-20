<?php

namespace Database\Factories;

use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

class BoardFactory extends Factory
{
    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'background_type' => 'color',
            'background_value' => fake()->hexColor(),
            'is_archived' => false,
            'is_starred' => false,
        ];
    }
}
