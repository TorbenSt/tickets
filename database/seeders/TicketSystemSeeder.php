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
        // Create main developer with iframe token (without firma_id)
        $mainDeveloper = \App\Models\User::factory()->developer()->create([
            'name' => 'Max Developer',
            'email' => 'developer@example.com',
            'iframe_user_token' => 'O3bAf7I79A3aplTCKW2v33kNrrlxdK3up8ZYIBjgiINbootbUjyZxbrPg5cCEPp2',
            'firma_id' => null, // Explizit null für Developer
        ]);

        // Create additional developers
        $developers = \App\Models\User::factory()
            ->developer()
            ->count(2)
            ->create();
            
        $developers->prepend($mainDeveloper);

        // Create firmas with unique names using sequences
        $firmaNames = ['TechCorp', 'InnovateSoft', 'DigitalWorks', 'FutureTech', 'SmartSolutions'];
        $firmas = collect();
        
        foreach ($firmaNames as $name) {
            $firmas->push(\App\Models\Firma::factory()->create(['name' => $name]));
        }

        // Create users and projects for each firma
        foreach ($firmas as $firma) {
            $users = \App\Models\User::factory()
                ->customer()
                ->count(rand(2, 5))
                ->create(['firma_id' => $firma->id]);

            // Create projects for this firma
            foreach ($users->take(2) as $user) {
                $projectCount = rand(1, 3);
                for ($i = 0; $i < $projectCount; $i++) {
                    // Create project with proper relationships
                    $project = \App\Models\Project::factory()->create([
                        'firma_id' => $firma->id,
                        'created_by' => $user->id,
                    ]);
                    
                    // Ensure creator is added to project users (Observer should handle this)
                    $project->users()->syncWithoutDetaching([$project->created_by]);
                    
                    // Add additional users to project
                    $additionalUsers = $users->whereNotIn('id', [$user->id])
                                           ->random(min(2, $users->count() - 1));
                    $project->users()->syncWithoutDetaching($additionalUsers->pluck('id'));

                    // Create tickets for this project
                    $ticketCount = rand(5, 15);
                    $projectUsers = $project->users;
                    
                    for ($j = 0; $j < $ticketCount; $j++) {
                        $creator = $projectUsers->random();
                        $isDeveloperTicket = rand(0, 3) === 0; // 25% developer tickets
                        
                        if ($isDeveloperTicket && $developers->count() > 0) {
                            // Developer-created ticket (starts as OPEN)
                            \App\Models\Ticket::factory()->byDeveloper()->create([
                                'project_id' => $project->id,
                                'assigned_to' => rand(0, 1) ? $developers->random()->id : null,
                            ]);
                        } else {
                            // Customer-created ticket (starts as TODO)
                            \App\Models\Ticket::factory()->byCustomer()->create([
                                'project_id' => $project->id,
                                'created_by' => $creator->id,
                                'assigned_to' => rand(0, 1) ? $developers->random()->id : null,
                            ]);
                        }
                    }
                }
            }
        }
    }
}
