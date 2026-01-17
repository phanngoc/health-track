<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SymptomLog>
 */
class SymptomLogFactory extends Factory
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
            'symptom_code' => \App\Models\Symptom::factory(),
            'severity' => fake()->numberBetween(1, 10),
            'occurred_at' => now(),
            'source' => 'checkin',
        ];
    }
}
