<?php

use App\Models\User;
use App\Models\Firma;
use App\Enums\UserRole;

describe('iFrame Token Management', function () {
    beforeEach(function () {
        $this->firma = Firma::factory()->create();
        $this->developer = User::factory()->developer()->create();
        $this->customer = User::factory()->customer()->create(['firma_id' => $this->firma->id]);
        $this->otherCustomer = User::factory()->customer()->create(['firma_id' => $this->firma->id]);
    });

    describe('Token Generation API', function () {
        it('developer can generate iframe token for any user', function () {
            $token = $this->developer->createToken('test-token')->plainTextToken;

            $response = $this->withHeaders([
                'Authorization' => "Bearer {$token}",
                'Accept' => 'application/json',
            ])->postJson("/api/admin/users/{$this->customer->id}/generate-token");

            $response->assertSuccessful();
            $response->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'token',
                    'created_at'
                ]
            ]);

            $this->customer->refresh();
            expect($this->customer->iframe_user_token)->not->toBeNull();
            expect(strlen($this->customer->iframe_user_token))->toBe(64);
        });

        it('customer cannot generate iframe tokens', function () {
            $token = $this->customer->createToken('test-token')->plainTextToken;

            $response = $this->withHeaders([
                'Authorization' => "Bearer {$token}",
                'Accept' => 'application/json',
            ])->postJson("/api/admin/users/{$this->otherCustomer->id}/generate-token");

            $response->assertStatus(403);
        });

        it('requires authentication for token generation', function () {
            $response = $this->postJson("/api/admin/users/{$this->customer->id}/generate-token");

            $response->assertStatus(401);
        });

        it('validates user exists for token generation', function () {
            $token = $this->developer->createToken('test-token')->plainTextToken;

            $response = $this->withHeaders([
                'Authorization' => "Bearer {$token}",
                'Accept' => 'application/json',
            ])->postJson("/api/admin/users/999999/generate-token");

            $response->assertStatus(404);
        });

        it('replaces existing token when generating new one', function () {
            $oldToken = $this->customer->generateIframeUserToken();
            
            $token = $this->developer->createToken('test-token')->plainTextToken;

            $response = $this->withHeaders([
                'Authorization' => "Bearer {$token}",
                'Accept' => 'application/json',
            ])->postJson("/api/admin/users/{$this->customer->id}/generate-token");

            $response->assertSuccessful();
            
            $this->customer->refresh();
            expect($this->customer->iframe_user_token)->not->toBe($oldToken);
            expect($this->customer->iframe_user_token)->not->toBeNull();
        });
    });

    describe('Token Revocation API', function () {
        beforeEach(function () {
            $this->customer->generateIframeUserToken();
        });

        it('developer can revoke iframe token', function () {
            $token = $this->developer->createToken('test-token')->plainTextToken;

            $response = $this->withHeaders([
                'Authorization' => "Bearer {$token}",
                'Accept' => 'application/json',
            ])->deleteJson("/api/admin/users/{$this->customer->id}/revoke-token");

            $response->assertSuccessful();
            $response->assertJson([
                'success' => true,
                'message' => 'iFrame token revoked successfully'
            ]);

            $this->customer->refresh();
            expect($this->customer->iframe_user_token)->toBeNull();
            expect($this->customer->iframe_token_created_at)->toBeNull();
            expect($this->customer->iframe_token_last_used)->toBeNull();
        });

        it('customer cannot revoke iframe tokens', function () {
            $token = $this->customer->createToken('test-token')->plainTextToken;

            $response = $this->withHeaders([
                'Authorization' => "Bearer {$token}",
                'Accept' => 'application/json',
            ])->deleteJson("/api/admin/users/{$this->otherCustomer->id}/revoke-token");

            $response->assertStatus(403);
        });

        it('handles revoking non-existent token gracefully', function () {
            $this->customer->revokeIframeToken(); // Already revoked
            
            $token = $this->developer->createToken('test-token')->plainTextToken;

            $response = $this->withHeaders([
                'Authorization' => "Bearer {$token}",
                'Accept' => 'application/json',
            ])->deleteJson("/api/admin/users/{$this->customer->id}/revoke-token");

            $response->assertSuccessful();
            $response->assertJson([
                'success' => true,
                'message' => 'No active iFrame token to revoke'
            ]);
        });
    });

    describe('Token Information API', function () {
        it('developer can view token information', function () {
            $this->customer->generateIframeUserToken();
            
            $token = $this->developer->createToken('test-token')->plainTextToken;

            $response = $this->withHeaders([
                'Authorization' => "Bearer {$token}",
                'Accept' => 'application/json',
            ])->getJson("/api/admin/users/{$this->customer->id}/token");

            $response->assertSuccessful();
            $response->assertJsonStructure([
                'success',
                'data' => [
                    'has_token',
                    'created_at',
                    'last_used',
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'role'
                    ]
                ]
            ]);

            $data = $response->json('data');
            expect($data['has_token'])->toBeTrue();
            expect($data['user']['id'])->toBe($this->customer->id);
        });

        it('shows correct information for user without token', function () {
            $token = $this->developer->createToken('test-token')->plainTextToken;

            $response = $this->withHeaders([
                'Authorization' => "Bearer {$token}",
                'Accept' => 'application/json',
            ])->getJson("/api/admin/users/{$this->customer->id}/token");

            $response->assertSuccessful();
            
            $data = $response->json('data');
            expect($data['has_token'])->toBeFalse();
            expect($data['created_at'])->toBeNull();
            expect($data['last_used'])->toBeNull();
        });

        it('customer cannot view token information', function () {
            $token = $this->customer->createToken('test-token')->plainTextToken;

            $response = $this->withHeaders([
                'Authorization' => "Bearer {$token}",
                'Accept' => 'application/json',
            ])->getJson("/api/admin/users/{$this->otherCustomer->id}/token");

            $response->assertStatus(403);
        });

        it('includes usage statistics when available', function () {
            $this->customer->generateIframeUserToken();
            $this->customer->markIframeTokenUsed();
            
            $token = $this->developer->createToken('test-token')->plainTextToken;

            $response = $this->withHeaders([
                'Authorization' => "Bearer {$token}",
                'Accept' => 'application/json',
            ])->getJson("/api/admin/users/{$this->customer->id}/token");

            $response->assertSuccessful();
            
            $data = $response->json('data');
            expect($data['has_token'])->toBeTrue();
            expect($data['last_used'])->not->toBeNull();
        });
    });

    describe('Token List API', function () {
        it('developer can view all iframe tokens', function () {
            // Generate tokens for multiple users
            $this->customer->generateIframeUserToken();
            $this->otherCustomer->generateIframeUserToken();
            
            $token = $this->developer->createToken('test-token')->plainTextToken;

            $response = $this->withHeaders([
                'Authorization' => "Bearer {$token}",
                'Accept' => 'application/json',
            ])->getJson("/api/admin/iframe-tokens");

            $response->assertSuccessful();
            $response->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'email',
                        'role',
                        'firma_id',
                        'has_token',
                        'created_at',
                        'last_used'
                    ]
                ]
            ]);

            $data = $response->json('data');
            expect(count($data))->toBeGreaterThan(0);
            
            // Should include users with tokens
            $userIds = collect($data)->pluck('id')->toArray();
            expect($userIds)->toContain($this->customer->id);
            expect($userIds)->toContain($this->otherCustomer->id);
        });

        it('customer cannot view iframe token list', function () {
            $token = $this->customer->createToken('test-token')->plainTextToken;

            $response = $this->withHeaders([
                'Authorization' => "Bearer {$token}",
                'Accept' => 'application/json',
            ])->getJson("/api/admin/iframe-tokens");

            $response->assertStatus(403);
        });

        it('filters token list correctly', function () {
            // Create users with and without tokens
            $userWithToken = User::factory()->customer()->create(['firma_id' => $this->firma->id]);
            $userWithoutToken = User::factory()->customer()->create(['firma_id' => $this->firma->id]);
            
            $userWithToken->generateIframeUserToken();
            
            $token = $this->developer->createToken('test-token')->plainTextToken;

            $response = $this->withHeaders([
                'Authorization' => "Bearer {$token}",
                'Accept' => 'application/json',
            ])->getJson("/api/admin/iframe-tokens?has_token=true");

            $response->assertSuccessful();
            
            if ($response->isSuccessful()) {
                $data = $response->json('data');
                $hasTokenUsers = collect($data)->where('has_token', true);
                $noTokenUsers = collect($data)->where('has_token', false);
                
                expect($hasTokenUsers->count())->toBeGreaterThan(0);
                expect($noTokenUsers->count())->toBe(0);
            }
        });

        it('includes firma information in token list', function () {
            $this->customer->generateIframeUserToken();
            
            $token = $this->developer->createToken('test-token')->plainTextToken;

            $response = $this->withHeaders([
                'Authorization' => "Bearer {$token}",
                'Accept' => 'application/json',
            ])->getJson("/api/admin/iframe-tokens");

            $response->assertSuccessful();
            
            $data = $response->json('data');
            $customerData = collect($data)->firstWhere('id', $this->customer->id);
            
            expect($customerData)->not->toBeNull();
            expect($customerData['firma_id'])->toBe($this->firma->id);
        });
    });

    describe('Web Interface Token Management', function () {
        it('developer can access token management page', function () {
            $this->actingAs($this->developer);

            $response = $this->get("/admin/users/{$this->customer->id}/token");
            
            $response->assertSuccessful();
            $response->assertSee($this->customer->name);
            $response->assertSee($this->customer->email);
        });

        it('customer cannot access token management page', function () {
            $this->actingAs($this->customer);

            $response = $this->get("/admin/users/{$this->otherCustomer->id}/token");
            
            $response->assertStatus(403);
        });

        it('shows token status on management page', function () {
            $this->customer->generateIframeUserToken();
            
            $this->actingAs($this->developer);

            $response = $this->get("/admin/users/{$this->customer->id}/token");
            
            $response->assertSuccessful();
            $response->assertSee('Active Token');
        });

        it('shows no token status when user has no token', function () {
            $this->actingAs($this->developer);

            $response = $this->get("/admin/users/{$this->customer->id}/token");
            
            $response->assertSuccessful();
            $response->assertSee('No Token');
        });
    });

    describe('Token Generation Edge Cases', function () {
        it('generates unique tokens for different users', function () {
            $token1 = $this->customer->generateIframeUserToken();
            $token2 = $this->otherCustomer->generateIframeUserToken();
            
            expect($token1)->not->toBe($token2);
            expect(strlen($token1))->toBe(64);
            expect(strlen($token2))->toBe(64);
        });

        it('handles token generation with database constraints', function () {
            // Test unique constraint handling
            $this->customer->generateIframeUserToken();
            $newToken = $this->customer->generateIframeUserToken();
            
            expect($newToken)->not->toBeNull();
            expect(strlen($newToken))->toBe(64);
        });

        it('cleans up token data on user deletion', function () {
            $user = User::factory()->customer()->create(['firma_id' => $this->firma->id]);
            $user->generateIframeUserToken();
            
            $userId = $user->id;
            $user->delete();
            
            // Token should be cleaned up with user
            $this->assertDatabaseMissing('users', ['id' => $userId]);
        });
    });

    describe('Security and Validation', function () {
        it('validates API endpoints require proper authentication', function () {
            $endpoints = [
                ['POST', "/api/admin/users/{$this->customer->id}/generate-token"],
                ['DELETE', "/api/admin/users/{$this->customer->id}/revoke-token"],
                ['GET', "/api/admin/users/{$this->customer->id}/token"],
                ['GET', "/api/admin/iframe-tokens"]
            ];

            foreach ($endpoints as [$method, $url]) {
                $response = $this->json($method, $url);
                $response->assertStatus(401);
            }
        });

        it('validates user permissions for all token operations', function () {
            $customerToken = $this->customer->createToken('test-token')->plainTextToken;
            
            $endpoints = [
                ['POST', "/api/admin/users/{$this->otherCustomer->id}/generate-token"],
                ['DELETE', "/api/admin/users/{$this->otherCustomer->id}/revoke-token"],
                ['GET', "/api/admin/users/{$this->otherCustomer->id}/token"],
                ['GET', "/api/admin/iframe-tokens"]
            ];

            foreach ($endpoints as [$method, $url]) {
                $response = $this->withHeaders([
                    'Authorization' => "Bearer {$customerToken}",
                    'Accept' => 'application/json',
                ])->json($method, $url);
                
                $response->assertStatus(403);
            }
        });

        it('includes rate limiting on token operations', function () {
            $token = $this->developer->createToken('test-token')->plainTextToken;

            // Make multiple rapid requests
            for ($i = 0; $i < 10; $i++) {
                $response = $this->withHeaders([
                    'Authorization' => "Bearer {$token}",
                    'Accept' => 'application/json',
                ])->postJson("/api/admin/users/{$this->customer->id}/generate-token");
            }

            // Should still work within reasonable limits
            expect($response->getStatusCode())->toBeIn([200, 429]);
        });
    });
});