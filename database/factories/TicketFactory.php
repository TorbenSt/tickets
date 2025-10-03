<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ticket>
 */
class TicketFactory extends Factory
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
            'description' => fake()->paragraphs(3, true),
            'status' => fake()->randomElement(['todo', 'in_progress', 'review', 'done']),
            'priority' => fake()->randomElement(['überprüfung', 'normal', 'asap', 'notfall']),
            'estimated_hours' => fake()->optional()->randomFloat(2, 0.5, 40),
            'actual_hours' => fake()->optional()->randomFloat(2, 0.5, 50),
            'project_id' => \App\Models\Project::factory(),
            'created_by' => \App\Models\User::factory(),
            'assigned_to' => fake()->optional()->randomElement([\App\Models\User::factory()]),
        ];
    }
}
