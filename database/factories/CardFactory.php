<?php

namespace Database\Factories;

use App\Models\ListModel;
use Illuminate\Database\Eloquent\Factories\Factory;

class CardFactory extends Factory
{
    public function definition(): array
    {
        return [
            'list_id' => ListModel::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'position' => fake()->numberBetween(0, 100),
            'is_archived' => false,
        ];
    }
}
