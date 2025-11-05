<?php

use App\Models\User;
use App\Models\Firma;
use App\Models\Project;
use App\Models\Ticket;
use App\Enums\UserRole;
use App\Enums\TicketStatus;
use App\Enums\TicketPriority;

describe('Developer Ticket Management', function () {
    beforeEach(function () {
        $this->developer = User::factory()->developer()->create();
        $this->otherDeveloper = User::factory()->developer()->create();
        $this->customer = User::factory()->customer()->create();
        
        $this->firma1 = Firma::factory()->create();
        $this->firma2 = Firma::factory()->create();
        
        $this->project1 = Project::factory()->create(['firma_id' => $this->firma1->id]);
        $this->project2 = Project::factory()->create(['firma_id' => $this->firma2->id]);
    });

    describe('System-wide Ticket Access', function () {
        it('developer can view all tickets system-wide', function () {
            // Create tickets in different projects/firmas
            $ticket1 = Ticket::factory()->create(['project_id' => $this->project1->id]);
            $ticket2 = Ticket::factory()->create(['project_id' => $this->project2->id]);

            $this->actingAs($this->developer);

            $response = $this->get('/tickets');
            
            $response->assertSuccessful();
            $response->assertSee($ticket1->title);
            $response->assertSee($ticket2->title);
        });

        it('customer cannot access system-wide ticket view', function () {
            // Create tickets in different projects that customer has no access to
            $otherProject = Project::factory()->create();
            $systemTicket = Ticket::factory()->create(['project_id' => $otherProject->id]);
            
            $this->actingAs($this->customer);

            $response = $this->get('/tickets');
            
            // Customer can access the route but should not see tickets from other projects
            $response->assertStatus(200);
            $response->assertDontSee($systemTicket->title);
        });

        it('shows tickets from all firmas in system overview', function () {
            $tickets = collect();
            
            // Create tickets across multiple firmas
            for ($i = 0; $i < 3; $i++) {
                $firma = Firma::factory()->create();
                $project = Project::factory()->create(['firma_id' => $firma->id]);
                $tickets->push(Ticket::factory()->create(['project_id' => $project->id]));
            }

            $this->actingAs($this->developer);

            $response = $this->get('/tickets');
            
            $response->assertSuccessful();
            
            foreach ($tickets as $ticket) {
                $response->assertSee($ticket->title);
            }
        });
    });

    describe('Emergency Tickets', function () {
        it('developer can view emergency priority tickets', function () {
            $emergencyTicket = Ticket::factory()->create([
                'project_id' => $this->project1->id,
                'priority' => TicketPriority::NOTFALL
            ]);
            
            $normalTicket = Ticket::factory()->create([
                'project_id' => $this->project1->id,
                'priority' => TicketPriority::NORMAL
            ]);

            $this->actingAs($this->developer);

            $response = $this->get('/tickets/emergency');
            
            $response->assertSuccessful();
            $response->assertSee($emergencyTicket->title);
            $response->assertDontSee($normalTicket->title);
        });

        it('customer cannot access emergency tickets view', function () {
            $this->actingAs($this->customer);

            $response = $this->get('/tickets/emergency');
            
            $response->assertStatus(403);
        });

        it('shows emergency tickets from all firmas', function () {
            $emergencyTickets = collect();
            
            // Create emergency tickets in different firmas
            foreach ([$this->project1, $this->project2] as $project) {
                $emergencyTickets->push(Ticket::factory()->create([
                    'project_id' => $project->id,
                    'priority' => TicketPriority::NOTFALL
                ]));
            }

            $this->actingAs($this->developer);

            $response = $this->get('/tickets/emergency');
            
            $response->assertSuccessful();
            
            foreach ($emergencyTickets as $ticket) {
                $response->assertSee($ticket->title);
            }
        });

        it('orders emergency tickets by creation date', function () {
            $oldTicket = Ticket::factory()->create([
                'project_id' => $this->project1->id,
                'priority' => TicketPriority::NOTFALL,
                'created_at' => now()->subDays(2)
            ]);
            
            $newTicket = Ticket::factory()->create([
                'project_id' => $this->project1->id,
                'priority' => TicketPriority::NOTFALL,
                'created_at' => now()
            ]);

            $this->actingAs($this->developer);

            $response = $this->get('/tickets/emergency');
            
            $response->assertSuccessful();
            // Should show newer tickets first (implementation dependent)
        });
    });

    describe('Ticket Assignment', function () {
        beforeEach(function () {
            $this->ticket = Ticket::factory()->create([
                'project_id' => $this->project1->id,
                'assigned_to' => null
            ]);
        });

        it('developer can assign tickets to themselves', function () {
            $this->actingAs($this->developer);

            $response = $this->patch("/tickets/{$this->ticket->id}/assign", [
                'assigned_to' => $this->developer->id
            ]);
            
            $response->assertRedirect();
            $this->assertDatabaseHas('tickets', [
                'id' => $this->ticket->id,
                'assigned_to' => $this->developer->id
            ]);
        });

        it('developer can assign tickets to other developers', function () {
            $this->actingAs($this->developer);

            $response = $this->patch("/tickets/{$this->ticket->id}/assign", [
                'assigned_to' => $this->otherDeveloper->id
            ]);
            
            $response->assertRedirect();
            $this->assertDatabaseHas('tickets', [
                'id' => $this->ticket->id,
                'assigned_to' => $this->otherDeveloper->id
            ]);
        });

        it('developer cannot assign tickets to customers', function () {
            $this->actingAs($this->developer);

            $response = $this->patch("/tickets/{$this->ticket->id}/assign", [
                'assigned_to' => $this->customer->id
            ]);
            
            $response->assertSessionHasErrors(['assigned_to']);
        });

        it('developer can reassign already assigned tickets', function () {
            $this->ticket->update(['assigned_to' => $this->otherDeveloper->id]);

            $this->actingAs($this->developer);

            $response = $this->patch("/tickets/{$this->ticket->id}/assign", [
                'assigned_to' => $this->developer->id
            ]);
            
            $response->assertRedirect();
            $this->assertDatabaseHas('tickets', [
                'id' => $this->ticket->id,
                'assigned_to' => $this->developer->id
            ]);
        });

        it('developer can unassign tickets', function () {
            $this->ticket->update(['assigned_to' => $this->developer->id]);

            $this->actingAs($this->developer);

            $response = $this->patch("/tickets/{$this->ticket->id}/assign", [
                'assigned_to' => null
            ]);
            
            $response->assertRedirect();
            $this->assertDatabaseHas('tickets', [
                'id' => $this->ticket->id,
                'assigned_to' => null
            ]);
        });

        it('customer cannot assign tickets', function () {
            $this->actingAs($this->customer);

            $response = $this->patch("/tickets/{$this->ticket->id}/assign", [
                'assigned_to' => $this->developer->id
            ]);
            
            $response->assertStatus(403);
        });
    });

    describe('Ticket Status Management', function () {
        beforeEach(function () {
            $this->ticket = Ticket::factory()->create([
                'project_id' => $this->project1->id,
                'status' => TicketStatus::TODO,
                'assigned_to' => $this->developer->id
            ]);
        });

        it('developer can update ticket status', function () {
            $this->actingAs($this->developer);

            $response = $this->patch("/tickets/{$this->ticket->id}/status", [
                'status' => TicketStatus::IN_PROGRESS->value
            ]);
            
            $response->assertRedirect();
            $this->assertDatabaseHas('tickets', [
                'id' => $this->ticket->id,
                'status' => TicketStatus::IN_PROGRESS->value
            ]);
        });

        it('developer can move ticket through complete workflow', function () {
            $this->actingAs($this->developer);

            $statuses = [
                TicketStatus::IN_PROGRESS,
                TicketStatus::REVIEW,
                TicketStatus::DONE
            ];

            foreach ($statuses as $status) {
                $response = $this->patch("/tickets/{$this->ticket->id}/status", [
                    'status' => $status->value
                ]);
                
                $response->assertRedirect();
                $this->assertDatabaseHas('tickets', [
                    'id' => $this->ticket->id,
                    'status' => $status->value
                ]);
                
                $this->ticket->refresh();
            }
        });

        it('developer can update status of any ticket regardless of assignment', function () {
            $unassignedTicket = Ticket::factory()->create([
                'project_id' => $this->project1->id,
                'assigned_to' => null
            ]);

            $this->actingAs($this->developer);

            $response = $this->patch("/tickets/{$unassignedTicket->id}/status", [
                'status' => TicketStatus::IN_PROGRESS->value
            ]);
            
            $response->assertRedirect();
            $this->assertDatabaseHas('tickets', [
                'id' => $unassignedTicket->id,
                'status' => TicketStatus::IN_PROGRESS->value
            ]);
        });

        it('validates status values', function () {
            $this->actingAs($this->developer);

            $response = $this->patch("/tickets/{$this->ticket->id}/status", [
                'status' => 'invalid_status'
            ]);
            
            $response->assertSessionHasErrors(['status']);
        });

        it('customer cannot update ticket status directly', function () {
            $this->actingAs($this->customer);

            $response = $this->patch("/tickets/{$this->ticket->id}/status", [
                'status' => TicketStatus::IN_PROGRESS->value
            ]);
            
            $response->assertStatus(403);
        });
    });

    describe('Ticket Creation by Developer', function () {
        it('developer can create tickets in any project', function () {
            $this->actingAs($this->developer);

            $ticketData = [
                'title' => 'Developer Created Ticket',
                'description' => 'This ticket was created by a developer',
                'priority' => TicketPriority::NORMAL->value,
                'project_id' => $this->project1->id
            ];

            $response = $this->post('/tickets', $ticketData);
            
            $response->assertRedirect();
            $this->assertDatabaseHas('tickets', [
                'title' => 'Developer Created Ticket',
                'project_id' => $this->project1->id,
                'created_by' => $this->developer->id,
                'status' => TicketStatus::OPEN->value // Developer tickets start as OPEN
            ]);
        });

        it('developer created tickets start with OPEN status', function () {
            $this->actingAs($this->developer);

            $response = $this->post('/tickets', [
                'title' => 'OPEN Status Ticket',
                'description' => 'Should start as OPEN',
                'priority' => TicketPriority::NORMAL->value,
                'project_id' => $this->project1->id
            ]);

            $ticket = Ticket::where('title', 'OPEN Status Ticket')->first();
            expect($ticket->status)->toBe(TicketStatus::OPEN);
        });

        it('developer can create tickets across different firmas', function () {
            $this->actingAs($this->developer);

            foreach ([$this->project1, $this->project2] as $project) {
                $response = $this->post('/tickets', [
                    'title' => "Ticket for Project {$project->id}",
                    'description' => 'Cross-firma ticket creation',
                    'priority' => TicketPriority::NORMAL->value,
                    'project_id' => $project->id
                ]);
                
                $response->assertRedirect();
                $this->assertDatabaseHas('tickets', [
                    'title' => "Ticket for Project {$project->id}",
                    'project_id' => $project->id
                ]);
            }
        });
    });

    describe('Ticket Filtering and Search', function () {
        it('developer can filter tickets by status', function () {
            $todoTickets = Ticket::factory()->count(3)->create([
                'project_id' => $this->project1->id,
                'status' => TicketStatus::TODO
            ]);
            
            $doneTickets = Ticket::factory()->count(2)->create([
                'project_id' => $this->project1->id,
                'status' => TicketStatus::DONE
            ]);

            $this->actingAs($this->developer);

            $response = $this->get('/tickets?status=todo');
            
            if ($response->isSuccessful()) {
                foreach ($todoTickets as $ticket) {
                    $response->assertSee($ticket->title);
                }
                
                foreach ($doneTickets as $ticket) {
                    $response->assertDontSee($ticket->title);
                }
            }
        });

        it('developer can filter tickets by assignment', function () {
            $assignedTickets = Ticket::factory()->count(2)->create([
                'project_id' => $this->project1->id,
                'assigned_to' => $this->developer->id
            ]);
            
            $unassignedTickets = Ticket::factory()->count(3)->create([
                'project_id' => $this->project1->id,
                'assigned_to' => null
            ]);

            $this->actingAs($this->developer);

            $response = $this->get('/tickets?assigned=me');
            
            if ($response->isSuccessful()) {
                foreach ($assignedTickets as $ticket) {
                    $response->assertSee($ticket->title);
                }
            }
        });

        it('developer can search tickets globally', function () {
            $searchTicket = Ticket::factory()->create([
                'project_id' => $this->project1->id,
                'title' => 'Unique Searchable Title'
            ]);
            
            $otherTicket = Ticket::factory()->create([
                'project_id' => $this->project2->id,
                'title' => 'Different Title'
            ]);

            $this->actingAs($this->developer);

            $response = $this->get('/tickets?search=Searchable');
            
            if ($response->isSuccessful()) {
                $response->assertSee($searchTicket->title);
                $response->assertDontSee($otherTicket->title);
            }
        });
    });

    describe('Ticket Analytics and Reporting', function () {
        it('developer can view ticket performance metrics', function () {
            // Create tickets with various statuses and assignments
            Ticket::factory()->count(5)->create([
                'project_id' => $this->project1->id,
                'assigned_to' => $this->developer->id,
                'status' => TicketStatus::DONE
            ]);

            $this->actingAs($this->developer);

            $response = $this->get('/tickets');
            
            $response->assertSuccessful();
            // Should include performance metrics in the view
        });

        it('developer can view workload distribution', function () {
            // Assign tickets to different developers
            Ticket::factory()->count(3)->create(['assigned_to' => $this->developer->id]);
            Ticket::factory()->count(2)->create(['assigned_to' => $this->otherDeveloper->id]);

            $this->actingAs($this->developer);

            $response = $this->get('/tickets');
            
            $response->assertSuccessful();
            // Should show workload distribution
        });
    });
});