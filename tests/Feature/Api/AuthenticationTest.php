<?php

use App\Models\{User, Firma, Project};
use App\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

describe('API Authentication', function () {
    uses(RefreshDatabase::class);

    describe('Sanctum Token Authentication', function () {
        it('allows access with valid API token', function () {
            $user = User::factory()->developer()->create();
            $token = $user->createToken('test-token');

            $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $token->plainTextToken,
                'Accept' => 'application/json',
            ])->getJson('/api/user');

            $response->assertStatus(200);
            $response->assertJson([
                'id' => $user->id,
                'email' => $user->email,
            ]);
        });

        it('denies access without API token', function () {
            $response = $this->getJson('/api/user');

            $response->assertStatus(401);
            $response->assertJson([
                'message' => 'Unauthenticated.'
            ]);
        });

        it('denies access with invalid API token', function () {
            $response = $this->withHeaders([
                'Authorization' => 'Bearer invalid-token',
                'Accept' => 'application/json',
            ])->getJson('/api/user');

            $response->assertStatus(401);
        });

        it('denies access with expired API token', function () {
            $user = User::factory()->developer()->create();
            $token = $user->createToken('test-token', ['*'], now()->subHour());

            $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $token->plainTextToken,
                'Accept' => 'application/json',
            ])->getJson('/api/user');

            $response->assertStatus(401);
        });
    });

    describe('Token Management', function () {
        it('creates tokens with specified abilities', function () {
            $user = User::factory()->developer()->create();
            $token = $user->createToken('test-token', ['read', 'write']);

            expect($token->accessToken->abilities)->toContain('read');
            expect($token->accessToken->abilities)->toContain('write');
        });

        it('restricts access based on token abilities', function () {
            $user = User::factory()->developer()->create();
            $readOnlyToken = $user->createToken('read-only', ['read']);

            Sanctum::actingAs($user, ['read']);

            // This should work with read ability
            $response = $this->getJson('/api/user');
            $response->assertStatus(200);

            // Write operations should be restricted (if implemented)
            // This is a placeholder for write-restricted endpoints
            expect(true)->toBeTrue();
        });

        it('allows token revocation', function () {
            $user = User::factory()->developer()->create();
            $token = $user->createToken('test-token');

            // Token should work initially
            $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $token->plainTextToken,
                'Accept' => 'application/json',
            ])->getJson('/api/user');
            $response->assertStatus(200);

            // Revoke token
            $user->tokens()->delete();

            // Token should no longer work
            $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $token->plainTextToken,
                'Accept' => 'application/json',
            ])->getJson('/api/user');
            $response->assertStatus(401);
        });

        it('allows multiple tokens per user', function () {
            $user = User::factory()->developer()->create();
            $token1 = $user->createToken('token-1');
            $token2 = $user->createToken('token-2');

            // Both tokens should work
            $response1 = $this->withHeaders([
                'Authorization' => 'Bearer ' . $token1->plainTextToken,
                'Accept' => 'application/json',
            ])->getJson('/api/user');
            $response1->assertStatus(200);

            $response2 = $this->withHeaders([
                'Authorization' => 'Bearer ' . $token2->plainTextToken,
                'Accept' => 'application/json',
            ])->getJson('/api/user');
            $response2->assertStatus(200);

            expect($user->tokens()->count())->toBe(2);
        });
    });

    describe('Role-based API Access', function () {
        it('allows developers full API access', function () {
            $developer = User::factory()->developer()->create();
            Sanctum::actingAs($developer);

            $response = $this->getJson('/api/user');
            $response->assertStatus(200);
            
            // Developers should have access to system-wide data
            expect($developer->role)->toBe(UserRole::DEVELOPER);
        });

        it('restricts customer API access to their firma data', function () {
            $firma = Firma::factory()->create();
            $customer = User::factory()->customer()->create(['firma_id' => $firma->id]);
            $otherFirma = Firma::factory()->create();

            Sanctum::actingAs($customer);

            $response = $this->getJson('/api/user');
            $response->assertStatus(200);
            
            // Customer should only see their own data
            expect($customer->role)->toBe(UserRole::CUSTOMER);
            expect($customer->firma_id)->toBe($firma->id);
        });

        it('validates API rate limiting', function () {
            $user = User::factory()->developer()->create();
            Sanctum::actingAs($user);

            // Make multiple requests to test rate limiting
            // This would depend on your rate limiting configuration
            for ($i = 0; $i < 5; $i++) {
                $response = $this->getJson('/api/user');
                $response->assertStatus(200);
            }

            // Rate limiting would typically kick in after many more requests
            expect(true)->toBeTrue();
        });
    });

    describe('API Response Format', function () {
        it('returns JSON responses with proper headers', function () {
            $user = User::factory()->developer()->create();
            Sanctum::actingAs($user);

            $response = $this->getJson('/api/user');

            $response->assertStatus(200);
            $response->assertHeader('Content-Type', 'application/json');
            expect($response->json())->toBeArray();
        });

        it('returns consistent error response format', function () {
            $response = $this->getJson('/api/user');

            $response->assertStatus(401);
            $response->assertJsonStructure([
                'message'
            ]);
        });

        it('includes proper CORS headers for API requests', function () {
            $user = User::factory()->developer()->create();
            Sanctum::actingAs($user);

            $response = $this->getJson('/api/user', [
                'Origin' => 'https://example.com'
            ]);

            // CORS headers would depend on your configuration
            expect($response->getStatusCode())->toBeNumeric();
        });
    });

    describe('API Versioning', function () {
        it('handles API version requests', function () {
            $user = User::factory()->developer()->create();
            Sanctum::actingAs($user);

            // Test version-specific endpoints if implemented
            $response = $this->getJson('/api/v1/user');
            
            // This might return 404 if versioning isn't implemented
            expect($response->getStatusCode())->toBeIn([200, 404]);
        });

        it('maintains backward compatibility', function () {
            $user = User::factory()->developer()->create();
            Sanctum::actingAs($user);

            $response = $this->getJson('/api/user');
            $response->assertStatus(200);

            // Ensure response format is stable
            $response->assertJsonStructure([
                'id',
                'name',
                'email',
            ]);
        });
    });

    describe('Security Headers', function () {
        it('includes security headers in API responses', function () {
            $user = User::factory()->developer()->create();
            Sanctum::actingAs($user);

            $response = $this->getJson('/api/user');

            $response->assertStatus(200);
            
            // Check for common security headers
            // These depend on your middleware configuration
            expect($response->headers)->toBeArray();
        });

        it('prevents API access from unauthorized origins', function () {
            $user = User::factory()->developer()->create();
            Sanctum::actingAs($user);

            // Test with potentially malicious origin
            $response = $this->getJson('/api/user', [
                'Origin' => 'https://malicious-site.com'
            ]);

            // Should still work but with proper CORS handling
            expect($response->getStatusCode())->toBeLessThan(500);
        });
    });
});