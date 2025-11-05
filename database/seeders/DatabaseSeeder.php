<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create test users
        User::factory()->customer()->create([
            'name' => 'Test Customer',
            'email' => 'customer@example.com',
        ]);

        User::factory()->developer()->create([
            'name' => 'Test Developer',
            'email' => 'testdev@example.com',
        ]);

        // Run the ticket system seeder
        $this->call(TicketSystemSeeder::class);
    }
}
