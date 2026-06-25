<?php

namespace Database\Factories;

use App\Models\Issue;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vote>
 */
class VoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'issue_id' => Issue::factory(),
            'value' => fake()->numberBetween(1, 13),
        ];
    }
}
