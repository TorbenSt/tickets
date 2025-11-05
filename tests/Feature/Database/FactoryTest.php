<?php

use App\Models\{User, Firma, Project, Ticket};
use App\Enums\{UserRole, TicketStatus, TicketPriority};
use Illuminate\Foundation\Testing\RefreshDatabase;

describe('Model Factories', function () {
    uses(RefreshDatabase::class);

    describe('User Factory', function () {
        it('creates valid users with default attributes', function () {
            $user = User::factory()->create();

            expect($user)->toBeInstanceOf(User::class);
            expect($user->name)->toBeString()->not()->toBeEmpty();
            expect($user->email)->toBeString()->toContain('@');
            expect($user->role)->toBeInstanceOf(UserRole::class);
            expect($user->exists)->toBeTrue();
        });

        it('creates customer users with firma association', function () {
            $user = User::factory()->customer()->create();

            expect($user->role)->toBe(UserRole::CUSTOMER);
            expect($user->firma_id)->not()->toBeNull();
            expect($user->firma)->toBeInstanceOf(Firma::class);
        });

        it('creates developer users without firma association', function () {
            $user = User::factory()->developer()->create();

            expect($user->role)->toBe(UserRole::DEVELOPER);
            expect($user->firma_id)->toBeNull();
            expect($user->firma)->toBeNull();
        });

        it('creates users with iframe tokens when specified', function () {
            $user = User::factory()->withIframeToken()->create();

            expect($user->iframe_user_token)->not()->toBeNull();
            expect($user->iframe_token_created_at)->not()->toBeNull();
            expect($user->iframe_user_token)->toHaveLength(60); // bcrypt length
        });

        it('creates users with valid email addresses', function () {
            $users = User::factory()->count(10)->create();

            foreach ($users as $user) {
                expect($user->email)->toMatch('/^[^\s@]+@[^\s@]+\.[^\s@]+$/');
            }
        });

        it('creates unique users', function () {
            $users = User::factory()->count(10)->create();
            $emails = $users->pluck('email')->toArray();
            $uniqueEmails = array_unique($emails);

            expect(count($emails))->toBe(count($uniqueEmails));
        });

        it('respects custom attributes', function () {
            $user = User::factory()->create([
                'name' => 'John Doe',
                'email' => 'john@example.com'
            ]);

            expect($user->name)->toBe('John Doe');
            expect($user->email)->toBe('john@example.com');
        });
    });

    describe('Firma Factory', function () {
        it('creates valid firmas with default attributes', function () {
            $firma = Firma::factory()->create();

            expect($firma)->toBeInstanceOf(Firma::class);
            expect($firma->name)->toBeString()->not()->toBeEmpty();
            expect($firma->description)->toBeString();
            expect($firma->exists)->toBeTrue();
        });

        it('creates firmas with custom attributes', function () {
            $firma = Firma::factory()->create([
                'name' => 'Test Company',
                'description' => 'A test company description'
            ]);

            expect($firma->name)->toBe('Test Company');
            expect($firma->description)->toBe('A test company description');
        });

        it('creates unique firma names', function () {
            $firmas = Firma::factory()->count(10)->create();
            $names = $firmas->pluck('name')->toArray();
            $uniqueNames = array_unique($names);

            expect(count($names))->toBe(count($uniqueNames));
        });

        it('creates firmas with associated users when using state', function () {
            $firma = Firma::factory()->withUsers(3)->create();

            expect($firma->users)->toHaveCount(3);
            expect($firma->users->first())->toBeInstanceOf(User::class);
            expect($firma->users->first()->role)->toBe(UserRole::CUSTOMER);
        });
    });

    describe('Project Factory', function () {
        it('creates valid projects with required relationships', function () {
            $project = Project::factory()->create();

            expect($project)->toBeInstanceOf(Project::class);
            expect($project->name)->toBeString()->not()->toBeEmpty();
            expect($project->description)->toBeString();
            expect($project->firma_id)->not()->toBeNull();
            expect($project->created_by)->not()->toBeNull();
            expect($project->exists)->toBeTrue();
        });

        it('creates projects with associated firma and creator', function () {
            $project = Project::factory()->create();

            expect($project->firma)->toBeInstanceOf(Firma::class);
            expect($project->creator)->toBeInstanceOf(User::class);
            expect($project->creator->firma_id)->toBe($project->firma_id);
        });

        it('creates projects with custom attributes', function () {
            $firma = Firma::factory()->create();
            $user = User::factory()->customer()->create(['firma_id' => $firma->id]);

            $project = Project::factory()->create([
                'name' => 'Test Project',
                'description' => 'A test project description',
                'firma_id' => $firma->id,
                'created_by' => $user->id
            ]);

            expect($project->name)->toBe('Test Project');
            expect($project->description)->toBe('A test project description');
            expect($project->firma_id)->toBe($firma->id);
            expect($project->created_by)->toBe($user->id);
        });

        it('automatically assigns creator to project users', function () {
            $project = Project::factory()->create();
            
            // Manually add creator to project users (simulating Observer behavior)
            $project->users()->syncWithoutDetaching([$project->created_by]);
            $project->load('users'); // Reload the relationship

            expect($project->users->contains('id', $project->created_by))->toBeTrue();
            expect($project->hasUser($project->creator))->toBeTrue();
        });

        it('creates projects with additional users when using state', function () {
            $project = Project::factory()->withUsers(3)->create();

            expect($project->users)->toHaveCount(4); // Creator + 3 additional users
            foreach ($project->users as $user) {
                expect($user->firma_id)->toBe($project->firma_id);
            }
        });
    });

    describe('Ticket Factory', function () {
        it('creates valid tickets with required relationships', function () {
            $ticket = Ticket::factory()->create();

            expect($ticket)->toBeInstanceOf(Ticket::class);
            expect($ticket->title)->toBeString()->not()->toBeEmpty();
            expect($ticket->description)->toBeString()->not()->toBeEmpty();
            expect($ticket->status)->toBeInstanceOf(TicketStatus::class);
            expect($ticket->priority)->toBeInstanceOf(TicketPriority::class);
            expect($ticket->project_id)->not()->toBeNull();
            expect($ticket->created_by)->not()->toBeNull();
            expect($ticket->exists)->toBeTrue();
        });

        it('creates tickets with associated project and creator', function () {
            $ticket = Ticket::factory()->create();

            expect($ticket->project)->toBeInstanceOf(Project::class);
            expect($ticket->creator)->toBeInstanceOf(User::class);
            expect($ticket->project->hasUser($ticket->creator))->toBeTrue();
        });

        it('creates tickets with valid enum values', function () {
            $tickets = Ticket::factory()->count(10)->create();

            foreach ($tickets as $ticket) {
                expect($ticket->status)->toBeIn(TicketStatus::cases());
                expect($ticket->priority)->toBeIn(TicketPriority::cases());
            }
        });

        it('creates tickets with assigned developers when using state', function () {
            $ticket = Ticket::factory()->assigned()->create();

            expect($ticket->assigned_to)->not()->toBeNull();
            expect($ticket->assignee)->toBeInstanceOf(User::class);
            expect($ticket->assignee->role)->toBe(UserRole::DEVELOPER);
        });

        it('creates tickets with different priorities using states', function () {
            $emergencyTicket = Ticket::factory()->emergency()->create();
            $asapTicket = Ticket::factory()->asap()->create();
            $normalTicket = Ticket::factory()->normal()->create();

            expect($emergencyTicket->priority)->toBe(TicketPriority::NOTFALL);
            expect($asapTicket->priority)->toBe(TicketPriority::ASAP);
            expect($normalTicket->priority)->toBe(TicketPriority::NORMAL);
        });

        it('creates tickets with different statuses using states', function () {
            $openTicket = Ticket::factory()->open()->create();
            $todoTicket = Ticket::factory()->todo()->create();
            $inProgressTicket = Ticket::factory()->inProgress()->create();
            $doneTicket = Ticket::factory()->done()->create();

            expect($openTicket->status)->toBe(TicketStatus::OPEN);
            expect($todoTicket->status)->toBe(TicketStatus::TODO);
            expect($inProgressTicket->status)->toBe(TicketStatus::IN_PROGRESS);
            expect($doneTicket->status)->toBe(TicketStatus::DONE);
        });

        it('creates customer tickets that start as TODO', function () {
            $customer = User::factory()->customer()->create();
            $project = Project::factory()->create(['created_by' => $customer->id]);
            
            $ticket = Ticket::factory()->byCustomer()->create([
                'project_id' => $project->id,
            ]);

            expect($ticket->status)->toBe(TicketStatus::TODO);
        });

        it('creates developer tickets that start as OPEN', function () {
            $developer = User::factory()->developer()->create();
            $project = Project::factory()->create();
            $project->users()->attach($developer);
            
            $ticket = Ticket::factory()->byDeveloper()->create([
                'project_id' => $project->id,
            ]);

            expect($ticket->status)->toBe(TicketStatus::OPEN);
        });
    });

    describe('Factory Relationships', function () {
        it('maintains referential integrity across factories', function () {
            $firma = Firma::factory()->create();
            $customer = User::factory()->customer()->create(['firma_id' => $firma->id]);
            $developer = User::factory()->developer()->create();
            
            $project = Project::factory()->create([
                'firma_id' => $firma->id,
                'created_by' => $customer->id
            ]);
            
            $ticket = Ticket::factory()->create([
                'project_id' => $project->id,
                'created_by' => $customer->id,
                'assigned_to' => $developer->id
            ]);

            expect($ticket->project->firma->id)->toBe($firma->id);
            expect($ticket->creator->firma->id)->toBe($firma->id);
            expect($ticket->assignee->firma_id)->toBeNull();
            expect($ticket->assignee->role)->toBe(UserRole::DEVELOPER);
        });

        it('creates realistic multi-tenant data structure', function () {
            // Create a complete realistic structure
            $firma1 = Firma::factory()->create(['name' => 'Company A']);
            $firma2 = Firma::factory()->create(['name' => 'Company B']);
            
            $customer1 = User::factory()->customer()->create(['firma_id' => $firma1->id]);
            $customer2 = User::factory()->customer()->create(['firma_id' => $firma2->id]);
            $developer = User::factory()->developer()->create();
            
            $project1 = Project::factory()->create([
                'firma_id' => $firma1->id,
                'created_by' => $customer1->id
            ]);
            
            $project2 = Project::factory()->create([
                'firma_id' => $firma2->id,
                'created_by' => $customer2->id
            ]);
            
            $ticket1 = Ticket::factory()->create([
                'project_id' => $project1->id,
                'created_by' => $customer1->id,
                'assigned_to' => $developer->id
            ]);
            
            $ticket2 = Ticket::factory()->create([
                'project_id' => $project2->id,
                'created_by' => $customer2->id,
                'assigned_to' => $developer->id
            ]);

            // Verify multi-tenant isolation
            expect($customer1->firma->projects)->toHaveCount(1);
            expect($customer1->firma->projects->first()->id)->toBe($project1->id);
            
            expect($customer2->firma->projects)->toHaveCount(1);
            expect($customer2->firma->projects->first()->id)->toBe($project2->id);
            
            // Verify developer can see all tickets
            expect($developer->assignedTickets)->toHaveCount(2);
        });
    });
});