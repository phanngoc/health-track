<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Insight>
 */
class InsightFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'type' => fake()->randomElement(['TREND', 'PATTERN', 'COMPARISON', 'CONTEXTUAL', 'REASSURANCE']),
            'code' => 'INS_'.fake()->word(),
            'message' => fake()->sentence(),
            'priority' => fake()->randomElement(['low', 'medium', 'high']),
            'metadata' => [],
            'explanation_data' => [],
            'generated_at' => now(),
            'expires_at' => now()->addDay(),
        ];
    }
}
