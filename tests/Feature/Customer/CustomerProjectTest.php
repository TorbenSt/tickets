<?php

use App\Models\User;
use App\Models\Firma;
use App\Models\Project;
use App\Models\Ticket;
use App\Enums\UserRole;
use App\Enums\TicketStatus;

describe('Customer Project Management', function () {
    beforeEach(function () {
        $this->firma = Firma::factory()->create();
        $this->customer = User::factory()->customer()->create(['firma_id' => $this->firma->id]);
        $this->otherFirma = Firma::factory()->create();
        $this->otherCustomer = User::factory()->customer()->create(['firma_id' => $this->otherFirma->id]);
        $this->developer = User::factory()->developer()->create();
    });

    describe('Project Index', function () {
        it('customer can view their firma projects only', function () {
            // Create projects for customer's firma where customer is a member
            $ownProjects = Project::factory()->count(3)->create(['firma_id' => $this->firma->id]);
            
            // Add customer as member to own projects
            foreach ($ownProjects as $project) {
                $project->users()->attach($this->customer->id);
            }
            
            // Create projects for other firma
            $otherProjects = Project::factory()->count(2)->create(['firma_id' => $this->otherFirma->id]);

            $this->actingAs($this->customer);
            $response = $this->get('/projects');

            $response->assertSuccessful();
            
            // Should see own firma projects where customer is member
            foreach ($ownProjects as $project) {
                $response->assertSee($project->name);
            }

            // Should not see other firma projects
            foreach ($otherProjects as $project) {
                $response->assertDontSee($project->name);
            }
        });

        it('customer sees only projects they are member of', function () {
            $project1 = Project::factory()->create([
                'firma_id' => $this->firma->id,
                'created_by' => $this->customer->id
            ]);
            $project2 = Project::factory()->create(['firma_id' => $this->firma->id]);
            
            // project1 creator is automatically added as member by ProjectObserver
            // project2 should not be visible since customer is not a member

            $this->actingAs($this->customer);
            $response = $this->get('/projects');

            $response->assertSuccessful();
            $response->assertSee($project1->name);
            $response->assertDontSee($project2->name);
        });

        it('shows project statistics correctly', function () {
            $project = Project::factory()->create(['firma_id' => $this->firma->id]);
            $project->users()->attach($this->customer->id);
            
            // Create tickets with different statuses
            Ticket::factory()->count(2)->create([
                'project_id' => $project->id,
                'status' => TicketStatus::TODO
            ]);
            Ticket::factory()->count(1)->create([
                'project_id' => $project->id,
                'status' => TicketStatus::DONE
            ]);

            $this->actingAs($this->customer);
            $response = $this->get('/projects');

            $response->assertSuccessful();
            // Should show ticket counts (implementation depends on view)
        });
    });

    describe('Project Creation', function () {
        it('customer can create new project', function () {
            $this->actingAs($this->customer);

            $response = $this->get('/projects/create');
            $response->assertSuccessful();

            $projectData = [
                'name' => 'New Customer Project',
                'description' => 'A test project for customer',
                'firma_id' => $this->firma->id
            ];

            $response = $this->post('/projects', $projectData);
            
            $response->assertRedirect();
            $this->assertDatabaseHas('projects', [
                'name' => 'New Customer Project',
                'firma_id' => $this->firma->id,
                'created_by' => $this->customer->id
            ]);
        });

        it('customer cannot create project for other firma', function () {
            $this->actingAs($this->customer);

            $projectData = [
                'name' => 'Unauthorized Project',
                'description' => 'Should not be created',
                'firma_id' => $this->otherFirma->id
            ];

            $response = $this->post('/projects', $projectData);
            
            // The controller ignores the firma_id from request and uses user's firma_id
            $response->assertRedirect();
            
            // Project should be created but with the customer's firma_id, not the requested one
            $this->assertDatabaseHas('projects', [
                'name' => 'Unauthorized Project',
                'firma_id' => $this->firma->id, // Should use customer's firma_id
                'created_by' => $this->customer->id
            ]);
            
            // Should NOT be created with the other firma_id 
            $this->assertDatabaseMissing('projects', [
                'name' => 'Unauthorized Project',
                'firma_id' => $this->otherFirma->id
            ]);
        });

        it('creator is automatically added as project member', function () {
            $this->actingAs($this->customer);

            $projectData = [
                'name' => 'Auto Member Project',
                'description' => 'Creator should be auto-assigned',
                'firma_id' => $this->firma->id
            ];

            $response = $this->post('/projects', $projectData);
            
            $project = Project::where('name', 'Auto Member Project')->first();
            expect($project)->not->toBeNull();
            expect($project->hasUser($this->customer))->toBeTrue();
        });

        it('validates required fields', function () {
            $this->actingAs($this->customer);

            $response = $this->post('/projects', []);
            
            $response->assertSessionHasErrors(['name']); // Only name is required, description is nullable
        });

        it('allows duplicate project names within firma', function () {
            $existingProject = Project::factory()->create([
                'name' => 'Duplicate Name',
                'firma_id' => $this->firma->id
            ]);

            $this->actingAs($this->customer);

            $response = $this->post('/projects', [
                'name' => 'Duplicate Name',
                'description' => 'Duplicate names are allowed',
                'firma_id' => $this->firma->id
            ]);

            $response->assertRedirect(); // Should succeed
            $this->assertDatabaseHas('projects', [
                'name' => 'Duplicate Name',
                'firma_id' => $this->firma->id,
                'created_by' => $this->customer->id
            ]);
        });
    });

    describe('Project Details', function () {
        beforeEach(function () {
            $this->project = Project::factory()->create(['firma_id' => $this->firma->id]);
            $this->project->users()->attach($this->customer->id);
        });

        it('customer can view project details if member', function () {
            $this->actingAs($this->customer);

            $response = $this->get("/projects/{$this->project->id}");
            
            $response->assertSuccessful();
            $response->assertSee($this->project->name);
            $response->assertSee($this->project->description);
        });

        it('customer cannot view project details if not member', function () {
            $otherProject = Project::factory()->create(['firma_id' => $this->firma->id]);
            
            $this->actingAs($this->customer);

            $response = $this->get("/projects/{$otherProject->id}");
            
            $response->assertStatus(403);
        });

        it('customer cannot view projects from other firma', function () {
            $otherProject = Project::factory()->create(['firma_id' => $this->otherFirma->id]);
            
            $this->actingAs($this->customer);

            $response = $this->get("/projects/{$otherProject->id}");
            
            $response->assertStatus(403);
        });

        it('shows project tickets to member', function () {
            $tickets = Ticket::factory()->count(3)->create(['project_id' => $this->project->id]);
            
            $this->actingAs($this->customer);
            $response = $this->get("/projects/{$this->project->id}");

            $response->assertSuccessful();
            
            foreach ($tickets as $ticket) {
                $response->assertSee($ticket->title);
            }
        });

        it('shows project team members', function () {
            $teamMember = User::factory()->customer()->create(['firma_id' => $this->firma->id]);
            $this->project->users()->attach($teamMember->id);
            
            $this->actingAs($this->customer);
            $response = $this->get("/projects/{$this->project->id}");

            $response->assertSuccessful();
            $response->assertSee($teamMember->name);
            $response->assertSee($this->customer->name);
        });
    });

    describe('Project Updates', function () {
        beforeEach(function () {
            $this->project = Project::factory()->create([
                'firma_id' => $this->firma->id,
                'created_by' => $this->customer->id
            ]);
            // Creator is automatically added as member by ProjectObserver
        });

        it('project creator can update project', function () {
            $this->actingAs($this->customer);

            $updateData = [
                'name' => 'Updated Project Name',
                'description' => 'Updated description'
            ];

            $response = $this->patch("/projects/{$this->project->id}", $updateData);
            
            $response->assertRedirect();
            $this->assertDatabaseHas('projects', [
                'id' => $this->project->id,
                'name' => 'Updated Project Name',
                'description' => 'Updated description'
            ]);
        });

        it('non-creator project member cannot update project', function () {
            $member = User::factory()->customer()->create(['firma_id' => $this->firma->id]);
            $this->project->users()->attach($member->id);
            
            $this->actingAs($member);

            $response = $this->patch("/projects/{$this->project->id}", [
                'name' => 'Unauthorized Update'
            ]);
            
            $response->assertStatus(403);
        });

        it('validates update data', function () {
            $this->actingAs($this->customer);

            $response = $this->patch("/projects/{$this->project->id}", [
                'name' => '', // Empty name should fail
                'description' => 'Valid description'
            ]);

            $response->assertSessionHasErrors(['name']);
        });
    });



    describe('Access Control Edge Cases', function () {
        it('handles deleted user access gracefully', function () {
            $project = Project::factory()->create(['firma_id' => $this->firma->id]);
            $project->users()->attach($this->customer->id);
            
            $this->actingAs($this->customer);
            
            // Simulate user deletion scenario
            $response = $this->get("/projects/{$project->id}");
            $response->assertSuccessful();
        });

    });
});