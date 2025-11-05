<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Firma>
 */
class FirmaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'description' => fake()->catchPhrase(),
            'email' => fake()->companyEmail(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
        ];
    }

    /**
     * Create a firma with associated users.
     */
    public function withUsers(int $count = 3): static
    {
        return $this->afterCreating(function (\App\Models\Firma $firma) use ($count) {
            \App\Models\User::factory($count)->customer()->create([
                'firma_id' => $firma->id,
            ]);
        });
    }
}
