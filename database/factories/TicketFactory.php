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
        $project = \App\Models\Project::factory()->create();
        $creator = $project->creator; // Use the project creator as ticket creator
        
        return [
            'title' => fake()->sentence(),
            'description' => fake()->paragraphs(3, true),
            'status' => 'todo', // Default status, can be overridden
            'priority' => fake()->randomElement(['überprüfung', 'normal', 'asap', 'notfall']),
            'estimated_hours' => fake()->optional()->randomFloat(2, 0.5, 40),
            'actual_hours' => fake()->optional()->randomFloat(2, 0.5, 50),
            'project_id' => $project->id,
            'created_by' => $creator->id,
            'assigned_to' => fake()->optional()->randomElement([\App\Models\User::factory()]),
        ];
    }

    /**
     * Create a ticket with assigned developer.
     */
    public function assigned(): static
    {
        return $this->state(fn (array $attributes) => [
            'assigned_to' => \App\Models\User::factory()->developer(),
        ]);
    }

    /**
     * Create a ticket with emergency priority.
     */
    public function emergency(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'notfall',
        ]);
    }

    /**
     * Create a ticket with open status.
     */
    public function open(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'open',
        ]);
    }

    /**
     * Create a ticket with todo status.
     */
    public function todo(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'todo',
        ]);
    }

    /**
     * Create a ticket with done status.
     */
    public function done(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'done',
        ]);
    }

    /**
     * Create a ticket with asap priority.
     */
    public function asap(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'asap',
        ]);
    }

    /**
     * Create a ticket with in_progress status.
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_progress',
        ]);
    }

    /**
     * Create a ticket with review status.
     */
    public function review(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'review',
        ]);
    }

    /**
     * Create a ticket with normal priority.
     */
    public function normal(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'normal',
        ]);
    }

    /**
     * Create a ticket created by a developer (starts as OPEN).
     */
    public function byDeveloper(): static
    {
        return $this->state(function (array $attributes) {
            $developer = \App\Models\User::factory()->developer()->create();
            return [
                'created_by' => $developer->id,
                'status' => 'open',
            ];
        });
    }

    /**
     * Create a ticket created by a customer (starts as TODO).
     */
    public function byCustomer(): static
    {
        return $this->state(function (array $attributes) {
            $customer = \App\Models\User::factory()->customer()->create();
            return [
                'created_by' => $customer->id,
                'status' => 'todo',
            ];
        });
    }


}
