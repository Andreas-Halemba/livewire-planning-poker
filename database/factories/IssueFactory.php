<?php

namespace Database\Factories;

use App\Models\Session;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Issue>
 */
class IssueFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(),
            'description' => fake()->optional()->paragraph(),
            'status' => fake()->randomElement(['open', 'voting', 'finished']),
            'session_id' => Session::factory(),
            'storypoints' => fake()->optional()->numberBetween(1, 21),
            'estimate_unit' => 'sp',
            'issue_type' => fake()->randomElement(['Story', 'Task', 'Spike', 'Bug']),
        ];
    }
}
