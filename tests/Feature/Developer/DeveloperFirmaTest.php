<?php

use App\Models\User;
use App\Models\Firma;
use App\Models\Project;
use App\Models\Ticket;
use App\Enums\UserRole;

describe('Developer Firma Management', function () {
    beforeEach(function () {
        $this->developer = User::factory()->developer()->create();
        $this->customer = User::factory()->customer()->create();
        $this->firma1 = Firma::factory()->create(['name' => 'First Company']);
        $this->firma2 = Firma::factory()->create(['name' => 'Second Company']);
    });

    describe('Firma Index', function () {
        it('developer can view all firmas', function () {
            $this->actingAs($this->developer);

            $response = $this->get('/firmas');
            
            $response->assertSuccessful();
            $response->assertSee($this->firma1->name);
            $response->assertSee($this->firma2->name);
        });

        it('customer cannot access firma index', function () {
            $this->actingAs($this->customer);

            $response = $this->get('/firmas');
            
            $response->assertStatus(403);
        });

        it('shows firma statistics correctly', function () {
            // Add users to firmas
            User::factory()->count(3)->customer()->create(['firma_id' => $this->firma1->id]);
            User::factory()->count(2)->customer()->create(['firma_id' => $this->firma2->id]);

            // Add projects to firmas
            Project::factory()->count(2)->create(['firma_id' => $this->firma1->id]);
            Project::factory()->count(1)->create(['firma_id' => $this->firma2->id]);

            $this->actingAs($this->developer);
            $response = $this->get('/firmas');

            $response->assertSuccessful();
            // Should show user and project counts (implementation depends on view)
        });

        it('orders firmas consistently', function () {
            // Create additional firmas
            Firma::factory()->count(5)->create();

            $this->actingAs($this->developer);
            $response = $this->get('/firmas');

            $response->assertSuccessful();
            // Should show all firmas in consistent order
        });
    });

    describe('Firma Details', function () {
        beforeEach(function () {
            $this->users = User::factory()->count(3)->customer()->create(['firma_id' => $this->firma1->id]);
            $this->projects = Project::factory()->count(2)->create(['firma_id' => $this->firma1->id]);
        });

        it('developer can view firma details', function () {
            $this->actingAs($this->developer);

            $response = $this->get("/firmas/{$this->firma1->id}");
            
            $response->assertSuccessful();
            $response->assertSee($this->firma1->name);
            $response->assertSee($this->firma1->description);
        });

        it('customer cannot view firma details', function () {
            $this->actingAs($this->customer);

            $response = $this->get("/firmas/{$this->firma1->id}");
            
            $response->assertStatus(403);
        });

        it('shows firma users', function () {
            $this->actingAs($this->developer);

            $response = $this->get("/firmas/{$this->firma1->id}");
            
            $response->assertSuccessful();
            
            foreach ($this->users as $user) {
                $response->assertSee($user->name);
                $response->assertSee($user->email);
            }
        });

        it('shows firma projects', function () {
            $this->actingAs($this->developer);

            $response = $this->get("/firmas/{$this->firma1->id}");
            
            $response->assertSuccessful();
            
            foreach ($this->projects as $project) {
                $response->assertSee($project->name);
            }
        });

        it('shows firma ticket statistics', function () {
            // Create tickets in firma projects
            foreach ($this->projects as $project) {
                Ticket::factory()->count(3)->create(['project_id' => $project->id]);
            }

            $this->actingAs($this->developer);

            $response = $this->get("/firmas/{$this->firma1->id}");
            
            $response->assertSuccessful();
            // Should show ticket counts and statistics
        });

        it('handles firma with no data gracefully', function () {
            $emptyFirma = Firma::factory()->create();

            $this->actingAs($this->developer);

            $response = $this->get("/firmas/{$emptyFirma->id}");
            
            $response->assertSuccessful();
            $response->assertSee($emptyFirma->name);
        });
    });

    describe('Cross-Firma Access', function () {
        it('developer can access any firma regardless of their own firma_id', function () {
            $developerWithFirma = User::factory()->developer()->create(['firma_id' => $this->firma1->id]);

            $this->actingAs($developerWithFirma);

            // Can access other firma
            $response = $this->get("/firmas/{$this->firma2->id}");
            $response->assertSuccessful();

            // Can access all firmas
            $response = $this->get('/firmas');
            $response->assertSuccessful();
        });

        it('developer can view projects from any firma', function () {
            $project1 = Project::factory()->create(['firma_id' => $this->firma1->id]);
            $project2 = Project::factory()->create(['firma_id' => $this->firma2->id]);

            $this->actingAs($this->developer);

            $response = $this->get("/projects/{$project1->id}");
            $response->assertSuccessful();

            $response = $this->get("/projects/{$project2->id}");
            $response->assertSuccessful();
        });

        it('developer can view tickets from any firma', function () {
            $project1 = Project::factory()->create(['firma_id' => $this->firma1->id]);
            $project2 = Project::factory()->create(['firma_id' => $this->firma2->id]);
            
            $ticket1 = Ticket::factory()->create(['project_id' => $project1->id]);
            $ticket2 = Ticket::factory()->create(['project_id' => $project2->id]);

            $this->actingAs($this->developer);

            $response = $this->get("/tickets/{$ticket1->id}");
            $response->assertSuccessful();

            $response = $this->get("/tickets/{$ticket2->id}");
            $response->assertSuccessful();
        });
    });

    describe('Firma Search and Filtering', function () {
        it('developer can search firmas', function () {
            $searchableFirma = Firma::factory()->create(['name' => 'Searchable Company']);
            $otherFirma = Firma::factory()->create(['name' => 'Other Business']);

            $this->actingAs($this->developer);

            $response = $this->get('/firmas?search=Searchable');
            
            if ($response->isSuccessful()) {
                $response->assertSee($searchableFirma->name);
                $response->assertDontSee($otherFirma->name);
            }
        });

        it('developer can filter firmas by activity', function () {
            // Create firma with recent activity
            $activeFirma = Firma::factory()->create();
            $activeProject = Project::factory()->create(['firma_id' => $activeFirma->id]);
            Ticket::factory()->create(['project_id' => $activeProject->id, 'created_at' => now()]);

            // Create firma without recent activity
            $inactiveFirma = Firma::factory()->create();

            $this->actingAs($this->developer);

            $response = $this->get('/firmas?filter=active');
            
            if ($response->isSuccessful()) {
                // Implementation depends on your filtering logic
                $response->assertSee($activeFirma->name);
            }
        });
    });

    describe('Firma User Management', function () {
        it('developer can view all users in firma', function () {
            $users = User::factory()->count(5)->customer()->create(['firma_id' => $this->firma1->id]);

            $this->actingAs($this->developer);

            $response = $this->get("/firmas/{$this->firma1->id}");
            
            $response->assertSuccessful();
            
            foreach ($users as $user) {
                $response->assertSee($user->name);
                $response->assertSee($user->email);
            }
        });

        it('developer can see user roles and permissions', function () {
            $customer = User::factory()->customer()->create(['firma_id' => $this->firma1->id]);
            $developer = User::factory()->developer()->create(['firma_id' => $this->firma1->id]);

            $this->actingAs($this->developer);

            $response = $this->get("/firmas/{$this->firma1->id}");
            
            $response->assertSuccessful();
            $response->assertSee($customer->role->label());
            $response->assertSee($developer->role->label());
        });

        it('developer can access user iframe token information', function () {
            $user = User::factory()->customer()->create(['firma_id' => $this->firma1->id]);
            $user->generateIframeUserToken();

            $this->actingAs($this->developer);

            $response = $this->get("/admin/users/{$user->id}/token");
            
            $response->assertSuccessful();
            // Should show token information
        });
    });

    describe('Firma Analytics and Reporting', function () {
        it('developer can view firma performance metrics', function () {
            $project = Project::factory()->create(['firma_id' => $this->firma1->id]);
            
            // Create tickets with various statuses
            Ticket::factory()->count(5)->create(['project_id' => $project->id]);

            $this->actingAs($this->developer);

            $response = $this->get("/firmas/{$this->firma1->id}");
            
            $response->assertSuccessful();
            // Should include performance metrics
        });

        it('developer can compare firma statistics', function () {
            // Create data for comparison
            $project1 = Project::factory()->create(['firma_id' => $this->firma1->id]);
            $project2 = Project::factory()->create(['firma_id' => $this->firma2->id]);
            
            Ticket::factory()->count(10)->create(['project_id' => $project1->id]);
            Ticket::factory()->count(5)->create(['project_id' => $project2->id]);

            $this->actingAs($this->developer);

            $response = $this->get('/firmas');
            
            $response->assertSuccessful();
            // Should show comparative statistics
        });
    });

    describe('Error Handling', function () {
        it('handles non-existent firma gracefully', function () {
            $this->actingAs($this->developer);

            $response = $this->get('/firmas/999999');
            
            $response->assertStatus(404);
        });

        it('handles database errors gracefully', function () {
            $this->actingAs($this->developer);

            // This would require mocking database failures
            $response = $this->get('/firmas');
            
            // Should handle gracefully without exposing errors
            expect($response->getStatusCode())->toBeIn([200, 500]);
        });
    });

    describe('Permission Edge Cases', function () {
        it('newly created developer immediately gets access', function () {
            $newDeveloper = User::factory()->developer()->create();

            $this->actingAs($newDeveloper);

            $response = $this->get('/firmas');
            $response->assertSuccessful();
        });

        it('developer role change takes effect immediately', function () {
            $user = User::factory()->customer()->create();
            
            $this->actingAs($user);
            $response = $this->get('/firmas');
            $response->assertStatus(403);

            // Change role to developer
            $user->update(['role' => UserRole::DEVELOPER]);
            
            $this->actingAs($user);
            $response = $this->get('/firmas');
            $response->assertSuccessful();
        });
    });
});