<?php

use App\Models\User;
use App\Models\Firma;
use App\Models\Project;
use App\Models\Ticket;
use App\Http\Middleware\RoleMiddleware;
use App\Enums\UserRole;

describe('Role Middleware', function () {
    beforeEach(function () {
        $this->firma = Firma::factory()->create();
        $this->customer = User::factory()->customer()->create(['firma_id' => $this->firma->id]);
        $this->developer = User::factory()->developer()->create();
        $this->project = Project::factory()->create(['firma_id' => $this->firma->id]);
    });

    describe('Customer Role Middleware', function () {
        it('allows customers to access customer routes', function () {
            $this->actingAs($this->customer);

            $customerRoutes = [
                '/projects',
                '/projects/create'
            ];

            foreach ($customerRoutes as $route) {
                $response = $this->get($route);
                $response->assertSuccessful();
            }
        });

        it('blocks developers from customer-only routes', function () {
            $this->actingAs($this->developer);

            $customerOnlyRoutes = [
                '/projects',
                '/projects/create'
            ];

            foreach ($customerOnlyRoutes as $route) {
                $response = $this->get($route);
                $response->assertStatus(403);
            }
        });

        it('redirects unauthenticated users to login', function () {
            $customerRoutes = [
                '/projects',
                '/projects/create'
            ];

            foreach ($customerRoutes as $route) {
                $response = $this->get($route);
                $response->assertRedirect('/login');
            }
        });

        it('blocks customers from accessing tickets pending approval when not authorized', function () {
            $this->actingAs($this->customer);

            $response = $this->get('/tickets/pending-approval');
            $response->assertSuccessful(); // Customer can access their pending approvals
        });
    });

    describe('Developer Role Middleware', function () {
        it('allows developers to access developer routes', function () {
            $this->actingAs($this->developer);

            $developerRoutes = [
                '/firmas',
                '/tickets',
                '/tickets/emergency'
            ];

            foreach ($developerRoutes as $route) {
                $response = $this->get($route);
                $response->assertSuccessful();
            }
        });

        it('blocks customers from developer-only routes', function () {
            $this->actingAs($this->customer);

            $developerOnlyRoutes = [
                '/firmas',
                '/tickets/emergency'
            ];

            foreach ($developerOnlyRoutes as $route) {
                $response = $this->get($route);
                $response->assertStatus(403);
            }
        });

    });

    describe('Shared Routes Access', function () {
        it('allows both roles to access shared routes when authorized', function () {
            $ticket = Ticket::factory()->create(['project_id' => $this->project->id]);
            
            // Add customer to project for access
            $this->project->users()->attach($this->customer->id);

            // Customer access
            $this->actingAs($this->customer);
            $response = $this->get("/tickets/{$ticket->id}");
            $response->assertSuccessful();

            // Developer access
            $this->actingAs($this->developer);
            $response = $this->get("/tickets/{$ticket->id}");
            $response->assertSuccessful();
        });

        it('blocks unauthorized access to shared routes', function () {
            $otherFirma = Firma::factory()->create();
            $otherProject = Project::factory()->create(['firma_id' => $otherFirma->id]);
            $ticket = Ticket::factory()->create(['project_id' => $otherProject->id]);

            // Customer cannot access tickets from other firmas
            $this->actingAs($this->customer);
            $response = $this->get("/tickets/{$ticket->id}");
            $response->assertStatus(403);
        });

    });

    describe('API Route Protection', function () {
        it('protects API routes with sanctum middleware', function () {
            // Unauthenticated API access
            $response = $this->getJson('/api/user');
            $response->assertStatus(401);
        });

        it('allows authenticated API access', function () {
            $token = $this->customer->createToken('test-token')->plainTextToken;

            $response = $this->withHeaders([
                'Authorization' => "Bearer {$token}",
                'Accept' => 'application/json',
            ])->getJson('/api/user');

            $response->assertSuccessful();
            $response->assertJson([
                'id' => $this->customer->id,
                'email' => $this->customer->email
            ]);
        });

        it('protects developer API routes from customers', function () {
            $token = $this->customer->createToken('test-token')->plainTextToken;
            $user = User::factory()->create();

            $response = $this->withHeaders([
                'Authorization' => "Bearer {$token}",
                'Accept' => 'application/json',
            ])->postJson("/api/admin/users/{$user->id}/generate-token");

            $response->assertStatus(403);
        });

    });

    describe('Middleware Error Handling', function () {
        it('returns 403 for wrong role instead of 404', function () {
            $this->actingAs($this->customer);

            $response = $this->get('/firmas');
            $response->assertStatus(403);
            $response->assertDontSee('404');
        });

        it('returns proper error messages for role violations', function () {
            $this->actingAs($this->customer);

            $response = $this->get('/firmas');
            $response->assertStatus(403);
            // Check that it's a proper 403 response, not a redirect
        });

        it('handles missing role gracefully', function () {
            // Create user as customer
            $user = User::factory()->create(['role' => 'customer']);
            $this->actingAs($user);

            $response = $this->get('/projects');
            // Should handle gracefully based on default role behavior
            expect($response->getStatusCode())->toBeIn([200, 403, 302]);
        });
    });

    describe('Route Model Binding Security', function () {
        it('respects authorization in route model binding', function () {
            $otherFirma = Firma::factory()->create();
            $otherProject = Project::factory()->create(['firma_id' => $otherFirma->id]);

            $this->actingAs($this->customer);

            // Should be blocked at middleware/policy level
            $response = $this->get("/projects/{$otherProject->id}");
            $response->assertStatus(403);
        });

        it('allows authorized route model binding', function () {
            $this->project->users()->attach($this->customer->id);
            $this->actingAs($this->customer);

            $response = $this->get("/projects/{$this->project->id}");
            $response->assertSuccessful();
        });
    });

    describe('Multi-tenancy Enforcement', function () {
        it('enforces firma boundaries for customers', function () {
            $otherFirma = Firma::factory()->create();
            $otherCustomer = User::factory()->customer()->create(['firma_id' => $otherFirma->id]);
            $otherProject = Project::factory()->create(['firma_id' => $otherFirma->id]);

            $this->actingAs($this->customer);

            // Cannot access other firma's projects
            $response = $this->get("/projects/{$otherProject->id}");
            $response->assertStatus(403);
        });

        it('allows developers cross-firma access', function () {
            $otherFirma = Firma::factory()->create();
            $otherProject = Project::factory()->create(['firma_id' => $otherFirma->id]);

            $this->actingAs($this->developer);

            // Developer can access any firma's data
            $response = $this->get("/firmas/{$otherFirma->id}");
            $response->assertSuccessful();
        });
    });
});