<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TicketSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create developers
        $developers = \App\Models\User::factory()
            ->developer()
            ->count(3)
            ->create();

        // Create firmas
        $firmas = \App\Models\Firma::factory()->count(5)->create();

        // Create users for each firma
        foreach ($firmas as $firma) {
            $users = \App\Models\User::factory()
                ->customer()
                ->count(rand(2, 5))
                ->create(['firma_id' => $firma->id]);

            // Create projects for this firma
            foreach ($users->take(2) as $user) {
                $projects = \App\Models\Project::factory()
                    ->count(rand(1, 3))
                    ->create([
                        'firma_id' => $firma->id,
                        'created_by' => $user->id,
                    ]);

                // Give some users access to projects
                foreach ($projects as $project) {
                    $projectUsers = $users->random(rand(1, min(3, $users->count())));
                    $project->users()->attach($projectUsers);

                    // Create tickets for each project
                    \App\Models\Ticket::factory()
                        ->count(rand(5, 15))
                        ->create([
                            'project_id' => $project->id,
                            'created_by' => $projectUsers->random()->id,
                            'assigned_to' => rand(0, 1) ? $developers->random()->id : null,
                        ]);
                }
            }
        }
    }
}
