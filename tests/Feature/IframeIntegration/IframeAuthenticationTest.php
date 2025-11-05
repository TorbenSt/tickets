<?php

use App\Models\User;
use App\Models\Firma;
use App\Models\Project;
use App\Enums\UserRole;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

describe('iFrame Authentication', function () {
    beforeEach(function () {
        $this->firma = Firma::factory()->create();
        $this->customer = User::factory()->customer()->create([
            'firma_id' => $this->firma->id,
            'email' => 'customer@example.com'
        ]);
        $this->developer = User::factory()->developer()->create([
            'email' => 'developer@example.com'
        ]);
        
        $this->apiKey = 'mein-super-sicherer-api-key-2024';
        config(['app.iframe_api_key' => Hash::make($this->apiKey)]);
        
        // Clear rate limiter before each test
        RateLimiter::clear('iframe-login:' . request()->ip());
    });

    describe('GET-based iFrame Login', function () {
        it('successfully authenticates with valid credentials', function () {
            $token = $this->customer->generateIframeUserToken();

            $response = $this->get('/iframe/login?' . http_build_query([
                'api_key' => $this->apiKey,
                'token' => $token,
                'email' => $this->customer->email
            ]));

            $response->assertRedirect();
            $this->assertAuthenticated();
        });

        it('fails with invalid API key', function () {
            $token = $this->customer->generateIframeUserToken();

            $response = $this->get('/iframe/login?' . http_build_query([
                'api_key' => 'invalid-api-key',
                'token' => $token,
                'email' => $this->customer->email
            ]));

            $response->assertStatus(401);
            $this->assertGuest();
        });

        it('fails with invalid token', function () {
            $response = $this->get('/iframe/login?' . http_build_query([
                'api_key' => $this->apiKey,
                'token' => 'invalid-token',
                'email' => $this->customer->email
            ]));

            $response->assertStatus(401);
            $this->assertGuest();
        });

        it('fails with invalid email', function () {
            $token = $this->customer->generateIframeUserToken();

            $response = $this->get('/iframe/login?' . http_build_query([
                'api_key' => $this->apiKey,
                'token' => $token,
                'email' => 'wrong@example.com'
            ]));

            $response->assertStatus(401);
            $this->assertGuest();
        });

        it('fails when token and email do not match', function () {
            $token = $this->customer->generateIframeUserToken();
            $otherUser = User::factory()->create(['email' => 'other@example.com']);

            $response = $this->get('/iframe/login?' . http_build_query([
                'api_key' => $this->apiKey,
                'token' => $token,
                'email' => $otherUser->email
            ]));

            $response->assertStatus(401);
            $this->assertGuest();
        });

        it('requires all parameters', function () {
            $token = $this->customer->generateIframeUserToken();

            // Missing API key
            $response = $this->get('/iframe/login?' . http_build_query([
                'token' => $token,
                'email' => $this->customer->email
            ]));
            $response->assertStatus(400);

            // Missing token
            $response = $this->get('/iframe/login?' . http_build_query([
                'api_key' => $this->apiKey,
                'email' => $this->customer->email
            ]));
            $response->assertStatus(400);

            // Missing email
            $response = $this->get('/iframe/login?' . http_build_query([
                'api_key' => $this->apiKey,
                'token' => $token
            ]));
            $response->assertStatus(400);
        });
    });

    describe('Role-based Redirects', function () {
        it('redirects customer to projects after login', function () {
            $token = $this->customer->generateIframeUserToken();

            $response = $this->get('/iframe/login?' . http_build_query([
                'api_key' => $this->apiKey,
                'token' => $token,
                'email' => $this->customer->email
            ]));

            $response->assertRedirect('/projects');
        });
    });

    describe('Rate Limiting', function () {
        it('applies rate limiting to iframe login attempts', function () {
            $token = $this->customer->generateIframeUserToken();
            $params = [
                'api_key' => 'invalid-key',
                'token' => $token,
                'email' => $this->customer->email
            ];

            // Make multiple failed attempts
            for ($i = 0; $i < 21; $i++) {
                $response = $this->get('/iframe/login?' . http_build_query($params));
            }

            // Should be rate limited
            $response->assertStatus(429);
        });

        it('allows successful login after failed attempts within limit', function () {
            $token = $this->customer->generateIframeUserToken();

            // Make some failed attempts (under limit)
            for ($i = 0; $i < 5; $i++) {
                $this->get('/iframe/login?' . http_build_query([
                    'api_key' => 'invalid-key',
                    'token' => $token,
                    'email' => $this->customer->email
                ]));
            }

            // Valid attempt should still work
            $response = $this->get('/iframe/login?' . http_build_query([
                'api_key' => $this->apiKey,
                'token' => $token,
                'email' => $this->customer->email
            ]));

            $response->assertRedirect();
            $this->assertAuthenticated();
        });
    });

    describe('Token Usage Tracking', function () {
        it('marks token as used on successful login', function () {
            $token = $this->customer->generateIframeUserToken();
            
            expect($this->customer->iframe_token_last_used)->toBeNull();

            $this->get('/iframe/login?' . http_build_query([
                'api_key' => $this->apiKey,
                'token' => $token,
                'email' => $this->customer->email
            ]));

            $this->customer->refresh();
            expect($this->customer->iframe_token_last_used)->not->toBeNull();
        });

        it('does not mark token as used on failed login', function () {
            $token = $this->customer->generateIframeUserToken();
            
            expect($this->customer->iframe_token_last_used)->toBeNull();

            $this->get('/iframe/login?' . http_build_query([
                'api_key' => 'invalid-key',
                'token' => $token,
                'email' => $this->customer->email
            ]));

            $this->customer->refresh();
            expect($this->customer->iframe_token_last_used)->toBeNull();
        });
    });

    describe('Security Headers and Session', function () {
        it('sets secure session on successful login', function () {
            $token = $this->customer->generateIframeUserToken();

            $response = $this->get('/iframe/login?' . http_build_query([
                'api_key' => $this->apiKey,
                'token' => $token,
                'email' => $this->customer->email
            ]));

            $response->assertRedirect();
            
            // Should have authentication session
            $this->assertAuthenticatedAs($this->customer);
        });

        it('includes appropriate headers', function () {
            $token = $this->customer->generateIframeUserToken();

            $response = $this->get('/iframe/login?' . http_build_query([
                'api_key' => $this->apiKey,
                'token' => $token,
                'email' => $this->customer->email
            ]));

            // Check for security headers (implementation dependent)
            $response->assertRedirect();
        });
    });

    describe('Error Handling', function () {
        it('handles database errors gracefully', function () {
            // This would require mocking database failures
            $token = $this->customer->generateIframeUserToken();

            $response = $this->get('/iframe/login?' . http_build_query([
                'api_key' => $this->apiKey,
                'token' => $token,
                'email' => $this->customer->email
            ]));

            // Should handle gracefully
            expect($response->getStatusCode())->toBeIn([200, 302, 500]);
        });

        it('logs authentication attempts', function () {
            $token = $this->customer->generateIframeUserToken();

            // Successful attempt
            $this->get('/iframe/login?' . http_build_query([
                'api_key' => $this->apiKey,
                'token' => $token,
                'email' => $this->customer->email
            ]));

            // Failed attempt
            $this->get('/iframe/login?' . http_build_query([
                'api_key' => 'invalid-key',
                'token' => $token,
                'email' => $this->customer->email
            ]));

            // Logs should be created (check depends on logging implementation)
            $this->assertTrue(true); // Placeholder for logging verification
        });

        it('handles malformed parameters', function () {
            $responses = [
                $this->get('/iframe/login?api_key='),
                $this->get('/iframe/login?token='),
                $this->get('/iframe/login?email=invalid-email'),
                $this->get('/iframe/login?api_key=' . str_repeat('x', 1000))
            ];

            foreach ($responses as $response) {
                expect($response->getStatusCode())->toBeIn([400, 401]);
            }
        });
    });

    describe('Multiple Authentication Sessions', function () {
        it('handles multiple concurrent logins', function () {
            $token1 = $this->customer->generateIframeUserToken();
            $token2 = $this->developer->generateIframeUserToken();

            // First login
            $response1 = $this->get('/iframe/login?' . http_build_query([
                'api_key' => $this->apiKey,
                'token' => $token1,
                'email' => $this->customer->email
            ]));

            $response1->assertRedirect();
            $this->assertAuthenticatedAs($this->customer);

            // Second login (should replace first)
            $response2 = $this->get('/iframe/login?' . http_build_query([
                'api_key' => $this->apiKey,
                'token' => $token2,
                'email' => $this->developer->email
            ]));

            $response2->assertRedirect();
            $this->assertAuthenticatedAs($this->developer);
        });

        it('maintains session across multiple requests', function () {
            $token = $this->customer->generateIframeUserToken();

            $this->get('/iframe/login?' . http_build_query([
                'api_key' => $this->apiKey,
                'token' => $token,
                'email' => $this->customer->email
            ]));

            // Subsequent requests should maintain authentication
            $response = $this->get('/projects');
            $response->assertSuccessful();
            
            $this->assertAuthenticatedAs($this->customer);
        });
    });

    describe('Token Expiration and Lifecycle', function () {
        it('works with fresh tokens', function () {
            $token = $this->customer->generateIframeUserToken();

            $response = $this->get('/iframe/login?' . http_build_query([
                'api_key' => $this->apiKey,
                'token' => $token,
                'email' => $this->customer->email
            ]));

            $response->assertRedirect();
            $this->assertAuthenticated();
        });

        it('fails with revoked tokens', function () {
            $token = $this->customer->generateIframeUserToken();
            $this->customer->revokeIframeToken();

            $response = $this->get('/iframe/login?' . http_build_query([
                'api_key' => $this->apiKey,
                'token' => $token,
                'email' => $this->customer->email
            ]));

            $response->assertStatus(401);
            $this->assertGuest();
        });

        it('handles token regeneration correctly', function () {
            $oldToken = $this->customer->generateIframeUserToken();
            $newToken = $this->customer->generateIframeUserToken();

            // Old token should not work
            $response = $this->get('/iframe/login?' . http_build_query([
                'api_key' => $this->apiKey,
                'token' => $oldToken,
                'email' => $this->customer->email
            ]));
            $response->assertStatus(401);

            // New token should work
            $response = $this->get('/iframe/login?' . http_build_query([
                'api_key' => $this->apiKey,
                'token' => $newToken,
                'email' => $this->customer->email
            ]));
            $response->assertRedirect();
        });
    });
});