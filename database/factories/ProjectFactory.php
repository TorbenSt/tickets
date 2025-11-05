<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $firma = \App\Models\Firma::factory()->create();
        
        return [
            'name' => fake()->words(3, true),
            'description' => fake()->paragraph(),
            'firma_id' => $firma->id,
            'created_by' => \App\Models\User::factory()->customer()->create([
                'firma_id' => $firma->id,
            ])->id,
        ];
    }



    /**
     * Create a project with additional users.
     */
    public function withUsers(int $count = 3): static
    {
        return $this->afterCreating(function (\App\Models\Project $project) use ($count) {
            $users = \App\Models\User::factory($count)->customer()->create([
                'firma_id' => $project->firma_id,
            ]);
            
            $project->users()->attach($users->pluck('id'));
        });
    }
}
