<?php

use App\Models\{User, Firma, Project};
use App\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

describe('Project API Endpoints', function () {
    uses(RefreshDatabase::class);

    describe('Available Users Endpoint', function () {
        it('returns available users for project assignment', function () {
            $firma = Firma::factory()->create();
            $customer = User::factory()->customer()->create(['firma_id' => $firma->id]);
            $developer = User::factory()->developer()->create();
            $project = Project::factory()->create([
                'firma_id' => $firma->id,
                'created_by' => $customer->id
            ]);

            // Create additional users in same firma
            $availableUser = User::factory()->customer()->create(['firma_id' => $firma->id]);
            
            // Create user in different firma (should not be available)
            $otherFirma = Firma::factory()->create();
            $otherUser = User::factory()->customer()->create(['firma_id' => $otherFirma->id]);

            Sanctum::actingAs($customer);

            $response = $this->getJson("/api/projects/{$project->id}/available-users");

            $response->assertStatus(200);
            $response->assertJsonStructure([
                '*' => [
                    'id',
                    'name',
                    'email',
                    'role'
                ]
            ]);

            $responseData = $response->json();
            $userIds = collect($responseData)->pluck('id')->toArray();

            // Should include available users from same firma
            expect($userIds)->toContain($availableUser->id);
            
            // Should include developers (cross-firma access)
            expect($userIds)->toContain($developer->id);
            
            // Should NOT include users from other firmas
            expect($userIds)->not()->toContain($otherUser->id);
            
            // Should NOT include users already assigned to project
            expect($userIds)->not()->toContain($customer->id);
        });

        it('requires authentication', function () {
            $project = Project::factory()->create();

            $response = $this->getJson("/api/projects/{$project->id}/available-users");

            $response->assertStatus(401);
        });

        it('requires project access permission', function () {
            $firma1 = Firma::factory()->create();
            $firma2 = Firma::factory()->create();
            
            $customer1 = User::factory()->customer()->create(['firma_id' => $firma1->id]);
            $customer2 = User::factory()->customer()->create(['firma_id' => $firma2->id]);
            
            $project = Project::factory()->create([
                'firma_id' => $firma1->id,
                'created_by' => $customer1->id
            ]);

            // Customer from different firma should not have access
            Sanctum::actingAs($customer2);

            $response = $this->getJson("/api/projects/{$project->id}/available-users");

            $response->assertStatus(403);
        });

        it('allows developer access to all projects', function () {
            $developer = User::factory()->developer()->create();
            $project = Project::factory()->create();

            Sanctum::actingAs($developer);

            $response = $this->getJson("/api/projects/{$project->id}/available-users");

            $response->assertStatus(200);
        });

        it('returns empty array when no users available', function () {
            $firma = Firma::factory()->create();
            $customer = User::factory()->customer()->create(['firma_id' => $firma->id]);
            $project = Project::factory()->create([
                'firma_id' => $firma->id,
                'created_by' => $customer->id
            ]);

            // No other users available
            Sanctum::actingAs($customer);

            $response = $this->getJson("/api/projects/{$project->id}/available-users");

            $response->assertStatus(200);
            $response->assertJson([]);
        });

        it('filters out already assigned users', function () {
            $firma = Firma::factory()->create();
            $customer = User::factory()->customer()->create(['firma_id' => $firma->id]);
            $availableUser = User::factory()->customer()->create(['firma_id' => $firma->id]);
            $assignedUser = User::factory()->customer()->create(['firma_id' => $firma->id]);
            
            $project = Project::factory()->create([
                'firma_id' => $firma->id,
                'created_by' => $customer->id
            ]);
            
            // Assign one user to project
            $project->users()->attach($assignedUser);

            Sanctum::actingAs($customer);

            $response = $this->getJson("/api/projects/{$project->id}/available-users");

            $response->assertStatus(200);
            
            $responseData = $response->json();
            $userIds = collect($responseData)->pluck('id')->toArray();

            expect($userIds)->toContain($availableUser->id);
            expect($userIds)->not()->toContain($assignedUser->id);
            expect($userIds)->not()->toContain($customer->id); // Creator is always assigned
        });

        it('includes user role information', function () {
            $firma = Firma::factory()->create();
            $customer = User::factory()->customer()->create(['firma_id' => $firma->id]);
            $developer = User::factory()->developer()->create();
            $availableCustomer = User::factory()->customer()->create(['firma_id' => $firma->id]);
            
            $project = Project::factory()->create([
                'firma_id' => $firma->id,
                'created_by' => $customer->id
            ]);

            Sanctum::actingAs($customer);

            $response = $this->getJson("/api/projects/{$project->id}/available-users");

            $response->assertStatus(200);
            
            $responseData = $response->json();
            
            foreach ($responseData as $user) {
                expect($user)->toHaveKey('role');
                expect($user['role'])->toBeIn(['customer', 'developer']);
            }
        });

        it('handles non-existent project', function () {
            $customer = User::factory()->customer()->create();
            Sanctum::actingAs($customer);

            $response = $this->getJson("/api/projects/999/available-users");

            $response->assertStatus(404);
        });

        it('returns properly formatted JSON response', function () {
            $firma = Firma::factory()->create();
            $customer = User::factory()->customer()->create(['firma_id' => $firma->id]);
            $project = Project::factory()->create([
                'firma_id' => $firma->id,
                'created_by' => $customer->id
            ]);

            Sanctum::actingAs($customer);

            $response = $this->getJson("/api/projects/{$project->id}/available-users");

            $response->assertStatus(200);
            $response->assertHeader('Content-Type', 'application/json');
            
            $responseData = $response->json();
            expect($responseData)->toBeArray();
        });
    });

    describe('Add User to Project Endpoint', function () {
        it('adds user to project via API', function () {
            $firma = Firma::factory()->create();
            $customer = User::factory()->customer()->create(['firma_id' => $firma->id]);
            $userToAdd = User::factory()->customer()->create(['firma_id' => $firma->id]);
            
            $project = Project::factory()->create([
                'firma_id' => $firma->id,
                'created_by' => $customer->id
            ]);

            Sanctum::actingAs($customer);

            $response = $this->postJson("/api/projects/{$project->id}/users", [
                'user_id' => $userToAdd->id
            ]);

            $response->assertStatus(200);
            $response->assertJson([
                'message' => 'User added to project successfully'
            ]);

            expect($project->fresh()->hasUser($userToAdd))->toBeTrue();
        });

        it('prevents adding user from different firma', function () {
            $firma1 = Firma::factory()->create();
            $firma2 = Firma::factory()->create();
            
            $customer = User::factory()->customer()->create(['firma_id' => $firma1->id]);
            $otherUser = User::factory()->customer()->create(['firma_id' => $firma2->id]);
            
            $project = Project::factory()->create([
                'firma_id' => $firma1->id,
                'created_by' => $customer->id
            ]);

            Sanctum::actingAs($customer);

            $response = $this->postJson("/api/projects/{$project->id}/users", [
                'user_id' => $otherUser->id
            ]);

            $response->assertStatus(422);
            expect($project->fresh()->hasUser($otherUser))->toBeFalse();
        });

        it('allows developers to be added to any project', function () {
            $customer = User::factory()->customer()->create();
            $developer = User::factory()->developer()->create();
            
            $project = Project::factory()->create(['created_by' => $customer->id]);

            Sanctum::actingAs($customer);

            $response = $this->postJson("/api/projects/{$project->id}/users", [
                'user_id' => $developer->id
            ]);

            $response->assertStatus(200);
            expect($project->fresh()->hasUser($developer))->toBeTrue();
        });

        it('prevents duplicate user assignment', function () {
            $firma = Firma::factory()->create();
            $customer = User::factory()->customer()->create(['firma_id' => $firma->id]);
            $userToAdd = User::factory()->customer()->create(['firma_id' => $firma->id]);
            
            $project = Project::factory()->create([
                'firma_id' => $firma->id,
                'created_by' => $customer->id
            ]);

            // Add user first time
            $project->users()->attach($userToAdd);

            Sanctum::actingAs($customer);

            $response = $this->postJson("/api/projects/{$project->id}/users", [
                'user_id' => $userToAdd->id
            ]);

            $response->assertStatus(422);
            $response->assertJsonValidationErrors(['user_id']);
        });

        it('validates required user_id parameter', function () {
            $customer = User::factory()->customer()->create();
            $project = Project::factory()->create(['created_by' => $customer->id]);

            Sanctum::actingAs($customer);

            $response = $this->postJson("/api/projects/{$project->id}/users", []);

            $response->assertStatus(422);
            $response->assertJsonValidationErrors(['user_id']);
        });
    });

    describe('Remove User from Project Endpoint', function () {
        it('removes user from project via API', function () {
            $firma = Firma::factory()->create();
            $customer = User::factory()->customer()->create(['firma_id' => $firma->id]);
            $userToRemove = User::factory()->customer()->create(['firma_id' => $firma->id]);
            
            $project = Project::factory()->create([
                'firma_id' => $firma->id,
                'created_by' => $customer->id
            ]);
            
            $project->users()->attach($userToRemove);

            Sanctum::actingAs($customer);

            $response = $this->deleteJson("/api/projects/{$project->id}/users/{$userToRemove->id}");

            $response->assertStatus(200);
            $response->assertJson([
                'message' => 'User removed from project successfully'
            ]);

            expect($project->fresh()->hasUser($userToRemove))->toBeFalse();
        });

        it('prevents removing project creator', function () {
            $customer = User::factory()->customer()->create();
            $project = Project::factory()->create(['created_by' => $customer->id]);

            Sanctum::actingAs($customer);

            $response = $this->deleteJson("/api/projects/{$project->id}/users/{$customer->id}");

            $response->assertStatus(422);
            expect($project->fresh()->hasUser($customer))->toBeTrue();
        });

        it('handles removing non-existent user', function () {
            $customer = User::factory()->customer()->create();
            $project = Project::factory()->create(['created_by' => $customer->id]);

            Sanctum::actingAs($customer);

            $response = $this->deleteJson("/api/projects/{$project->id}/users/999");

            $response->assertStatus(404);
        });

        it('requires appropriate permissions', function () {
            $firma1 = Firma::factory()->create();
            $firma2 = Firma::factory()->create();
            
            $customer1 = User::factory()->customer()->create(['firma_id' => $firma1->id]);
            $customer2 = User::factory()->customer()->create(['firma_id' => $firma2->id]);
            
            $project = Project::factory()->create([
                'firma_id' => $firma1->id,
                'created_by' => $customer1->id
            ]);

            Sanctum::actingAs($customer2);

            $response = $this->deleteJson("/api/projects/{$project->id}/users/{$customer1->id}");

            $response->assertStatus(403);
        });
    });
});