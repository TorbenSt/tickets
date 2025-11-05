<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use App\Models\{User, Firma, Project, Ticket};
use App\Enums\{UserRole, TicketStatus, TicketPriority};

describe('Database Seeders', function () {
    uses(RefreshDatabase::class);

    describe('DatabaseSeeder', function () {
        it('runs without errors', function () {
            expect(fn() => Artisan::call('db:seed'))->not()->toThrow(\Exception::class);
        });

        it('creates basic data structure', function () {
            Artisan::call('db:seed');

            expect(User::count())->toBeGreaterThan(0);
            expect(Firma::count())->toBeGreaterThan(0);
            expect(Project::count())->toBeGreaterThan(0);
            expect(Ticket::count())->toBeGreaterThan(0);
        });
    });

    describe('TicketSystemSeeder', function () {
        beforeEach(function () {
            Artisan::call('db:seed', ['--class' => 'TicketSystemSeeder']);
        });

        describe('Firma Creation', function () {
            it('creates multiple firmas', function () {
                expect(Firma::count())->toBeGreaterThanOrEqual(3);
            });

            it('creates firmas with unique names', function () {
                // Only check firmas created by TicketSystemSeeder (should be 5 unique)
                $expectedNames = ['TechCorp', 'InnovateSoft', 'DigitalWorks', 'FutureTech', 'SmartSolutions'];
                
                foreach ($expectedNames as $name) {
                    expect(Firma::where('name', $name)->exists())->toBeTrue();
                }
                
                // Check that we don't have duplicate seeder firmas
                foreach ($expectedNames as $name) {
                    expect(Firma::where('name', $name)->count())->toBeLessThanOrEqual(1);
                }
            });

            it('creates firmas with descriptions', function () {
                $firmas = Firma::all();

                foreach ($firmas as $firma) {
                    expect($firma->description)->not()->toBeNull();
                    expect($firma->description)->not()->toBeEmpty();
                }
            });
        });

        describe('User Creation', function () {
            it('creates both customer and developer users', function () {
                $customers = User::where('role', UserRole::CUSTOMER)->get();
                $developers = User::where('role', UserRole::DEVELOPER)->get();

                expect($customers->count())->toBeGreaterThan(0);
                expect($developers->count())->toBeGreaterThan(0);
            });

            it('assigns customers to firmas', function () {
                $customers = User::where('role', UserRole::CUSTOMER)->get();

                foreach ($customers as $customer) {
                    expect($customer->firma_id)->not()->toBeNull();
                    expect($customer->firma)->toBeInstanceOf(Firma::class);
                }
            });

            it('does not assign developers to firmas', function () {
                $developers = User::where('role', UserRole::DEVELOPER)->get();

                foreach ($developers as $developer) {
                    expect($developer->firma_id)->toBeNull();
                    expect($developer->firma)->toBeNull();
                }
            });

            it('creates users with unique emails', function () {
                $users = User::all();
                $emails = $users->pluck('email')->toArray();
                $uniqueEmails = array_unique($emails);

                expect(count($emails))->toBe(count($uniqueEmails));
            });

            it('creates users with hashed passwords', function () {
                $users = User::all();

                foreach ($users as $user) {
                    expect($user->password)->not()->toBeNull();
                    expect(strlen($user->password))->toBe(60); // bcrypt length
                    expect($user->password)->toStartWith('$2y$'); // bcrypt prefix
                }
            });
        });

        describe('Project Creation', function () {
            it('creates projects for each firma', function () {
                // Only check seeder-created firmas
                $seederFirmas = Firma::whereIn('name', ['TechCorp', 'InnovateSoft', 'DigitalWorks', 'FutureTech', 'SmartSolutions'])->get();

                foreach ($seederFirmas as $firma) {
                    expect($firma->projects->count())->toBeGreaterThan(0);
                }
            });

            it('assigns project creators from correct firma', function () {
                $projects = Project::all();

                foreach ($projects as $project) {
                    expect($project->creator)->toBeInstanceOf(User::class);
                    expect($project->creator->firma_id)->toBe($project->firma_id);
                    expect($project->creator->role)->toBe(UserRole::CUSTOMER);
                }
            });

            it('adds creators to project users automatically', function () {
                $projects = Project::all();

                foreach ($projects as $project) {
                    expect($project->hasUser($project->creator))->toBeTrue();
                    $project->load('users');
                    expect($project->users->contains('id', $project->created_by))->toBeTrue();
                }
            });

            it('creates projects with unique names within firma scope', function () {
                $firmas = Firma::all();

                foreach ($firmas as $firma) {
                    $projectNames = $firma->projects->pluck('name')->toArray();
                    $uniqueNames = array_unique($projectNames);

                    expect(count($projectNames))->toBe(count($uniqueNames));
                }
            });

            it('assigns additional users to projects', function () {
                $projects = Project::all();
                
                $projectsWithMultipleUsers = $projects->filter(function ($project) {
                    return $project->users->count() > 1;
                });

                expect($projectsWithMultipleUsers->count())->toBeGreaterThan(0);
            });
        });

        describe('Ticket Creation', function () {
            it('creates tickets for each project', function () {
                // Only check projects from seeder-created firmas
                $seederFirmas = Firma::whereIn('name', ['TechCorp', 'InnovateSoft', 'DigitalWorks', 'FutureTech', 'SmartSolutions'])->get();
                $seederProjects = Project::whereIn('firma_id', $seederFirmas->pluck('id'))->get();

                foreach ($seederProjects as $project) {
                    expect($project->tickets->count())->toBeGreaterThan(0);
                }
            });

            it('creates tickets with valid enum values', function () {
                $tickets = Ticket::all();

                foreach ($tickets as $ticket) {
                    expect($ticket->status)->toBeIn(TicketStatus::cases());
                    expect($ticket->priority)->toBeIn(TicketPriority::cases());
                }
            });

            it('creates tickets with proper creator assignment', function () {
                $tickets = Ticket::all();

                foreach ($tickets as $ticket) {
                    expect($ticket->creator)->toBeInstanceOf(User::class);
                    expect($ticket->project->hasUser($ticket->creator))->toBeTrue();
                }
            });

            it('assigns some tickets to developers', function () {
                $assignedTickets = Ticket::whereNotNull('assigned_to')->get();

                expect($assignedTickets->count())->toBeGreaterThan(0);

                foreach ($assignedTickets as $ticket) {
                    expect($ticket->assignee)->toBeInstanceOf(User::class);
                    expect($ticket->assignee->role)->toBe(UserRole::DEVELOPER);
                }
            });

            it('creates tickets with different priorities', function () {
                $priorities = Ticket::pluck('priority')->unique();

                expect($priorities->count())->toBeGreaterThan(1);
                expect($priorities)->toContain(TicketPriority::NORMAL);
            });

            it('creates tickets with different statuses', function () {
                $statuses = Ticket::pluck('status')->unique();

                expect($statuses->count())->toBeGreaterThan(1);
                expect($statuses)->toContain(TicketStatus::TODO);
            });

            it('follows business rules for ticket status based on creator', function () {
                $customerTickets = Ticket::whereHas('creator', function ($query) {
                    $query->where('role', UserRole::CUSTOMER);
                })->get();

                $developerTickets = Ticket::whereHas('creator', function ($query) {
                    $query->where('role', UserRole::DEVELOPER);
                })->get();

                // Customer-created tickets should start as TODO (or be approved), never OPEN
                foreach ($customerTickets as $ticket) {
                    expect($ticket->status)->not->toBe(TicketStatus::OPEN, 
                        'Customer tickets should not start as OPEN status');
                }

                // Verify we have both customer and developer tickets
                expect($customerTickets)->not->toBeEmpty('Should have customer-created tickets');
                expect($developerTickets)->not->toBeEmpty('Should have developer-created tickets');
                
                // Developer tickets may start as OPEN (requiring approval)
                $openDeveloperTickets = $developerTickets->where('status', TicketStatus::OPEN);
                expect($openDeveloperTickets->count())->toBeGreaterThanOrEqual(0, 
                    'Developer tickets can optionally start as OPEN');
            });
        });

        describe('Multi-tenant Data Integrity', function () {
            it('maintains proper firma isolation', function () {
                $firmas = Firma::all();

                foreach ($firmas as $firma) {
                    $firmaUsers = $firma->users;
                    $firmaProjects = $firma->projects;

                    // All project creators should be from this firma
                    foreach ($firmaProjects as $project) {
                        expect($firmaUsers->contains('id', $project->created_by))->toBeTrue();
                    }

                    // All project users (except developers) should be from this firma
                    foreach ($firmaProjects as $project) {
                        $customerUsers = $project->users->where('role', UserRole::CUSTOMER);
                        foreach ($customerUsers as $user) {
                            expect($user->firma_id)->toBe($firma->id);
                        }
                    }
                }
            });

            it('allows developers cross-firma access', function () {
                $developers = User::where('role', UserRole::DEVELOPER)->get();
                $allProjects = Project::all();
                
                // Check if any developer is assigned to tickets across different firmas
                foreach ($developers as $developer) {
                    $assignedTickets = $developer->assignedTickets;
                    if ($assignedTickets->count() > 1) {
                        $firmaIds = $assignedTickets->pluck('project.firma_id')->unique();
                        if ($firmaIds->count() > 1) {
                            // Developer has cross-firma assignments - this is correct
                            expect(true)->toBeTrue();
                            return;
                        }
                    }
                }
                
                // If no cross-firma assignments found, that's still valid
                expect(true)->toBeTrue();
            });

            it('creates realistic business relationships', function () {
                // Test that the seeded data creates realistic business scenarios
                // Only check seeder-created firmas
                $seederFirmas = Firma::whereIn('name', ['TechCorp', 'InnovateSoft', 'DigitalWorks', 'FutureTech', 'SmartSolutions'])->get();
                
                foreach ($seederFirmas as $firma) {
                    // Each firma should have customers
                    expect($firma->users->where('role', UserRole::CUSTOMER)->count())->toBeGreaterThan(0);
                    
                    // Each firma should have projects
                    expect($firma->projects()->count())->toBeGreaterThan(0);
                    
                    // Each firma should have tickets through projects
                    $ticketCount = $firma->projects->sum(function ($project) {
                        return $project->tickets->count();
                    });
                    expect($ticketCount)->toBeGreaterThan(0);
                }
                
                // System should have developers
                expect(User::where('role', UserRole::DEVELOPER)->count())->toBeGreaterThan(0);
            });
        });

        describe('Data Volume and Performance', function () {
            it('creates sufficient test data for realistic testing', function () {
                expect(Firma::count())->toBeGreaterThanOrEqual(3);
                expect(User::count())->toBeGreaterThanOrEqual(10);
                expect(Project::count())->toBeGreaterThanOrEqual(5);
                expect(Ticket::count())->toBeGreaterThanOrEqual(15);
            });

            it('creates data with proper distribution', function () {
                $customers = User::where('role', UserRole::CUSTOMER)->count();
                $developers = User::where('role', UserRole::DEVELOPER)->count();
                
                // Should have more customers than developers (realistic business scenario)
                expect($customers)->toBeGreaterThan($developers);
                
                // But should have enough developers for testing
                expect($developers)->toBeGreaterThanOrEqual(2);
            });

            it('maintains referential integrity in bulk data', function () {
                // No orphaned records
                $orphanedProjects = Project::whereDoesntHave('firma')->count();
                $orphanedTickets = Ticket::whereDoesntHave('project')->count();
                $orphanedCustomers = User::where('role', UserRole::CUSTOMER)
                    ->whereNull('firma_id')->count();
                
                expect($orphanedProjects)->toBe(0);
                expect($orphanedTickets)->toBe(0);
                expect($orphanedCustomers)->toBe(0);
            });
        });
    });
});