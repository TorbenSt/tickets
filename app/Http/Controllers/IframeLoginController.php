<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class IframeLoginController extends Controller
{
    /**
     * GET-basierte iFrame-Authentifizierung für maximale Einfachheit
     * URL: /iframe/login?token=xxx&email=xxx&api_key=xxx&redirect=/projects
     */
    public function login(Request $request)
    {
        // Rate Limiting pro IP
        $key = 'iframe-login:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 20)) {
            Log::warning('iFrame login rate limit exceeded', [
                'ip' => $request->ip()
            ]);
            return response('Too many requests', 429);
        }

        // API Key validieren
        if (!$this->validateApiKey($request->get('api_key'))) {
            RateLimiter::hit($key);
            Log::warning('Invalid API key for iframe login', [
                'ip' => $request->ip()
            ]);
            return response('Unauthorized', 401);
        }

        // Basic parameter validation
        if (!$request->token || !$request->email) {
            RateLimiter::hit($key);
            return response('Missing required parameters', 400);
        }

        // Token format validation - return 401 for invalid tokens
        if (strlen($request->token) !== 64 || !preg_match('/^[a-zA-Z0-9]+$/', $request->token)) {
            RateLimiter::hit($key);
            Log::warning('Invalid token format for iframe login', [
                'token_preview' => substr($request->token, 0, 8) . '...',
                'ip' => $request->ip()
            ]);
            return response('Unauthorized', 401);
        }

        // Email validation
        if (!filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
            RateLimiter::hit($key);
            return response('Invalid email format', 400);
        }

        // User finden
        $user = User::where('iframe_user_token', $request->token)
                   ->where('email', $request->email)
                   ->whereNotNull('iframe_user_token')
                   ->with('firma')
                   ->first();

        if (!$user) {
            // In local mode allow a permissive fallback so the
            // static `public/integration-test.php` can authenticate a test user
            // by email (useful for manual integration tests). This is only
            // enabled for the local environment to avoid accidental
            // exposure in production or during automated tests.
            if (app()->environment('local')) {
                $fallbackUser = User::where('email', $request->email)->with('firma')->first();
                if ($fallbackUser) {
                    Log::info('Iframe login fallback used (local/debug) for user', [
                        'user_id' => $fallbackUser->id,
                        'email' => $fallbackUser->email,
                        'ip' => $request->ip(),
                    ]);

                    // Mark token used if method exists (no-op if not)
                    if (method_exists($fallbackUser, 'markIframeTokenUsed')) {
                        try {
                            $fallbackUser->markIframeTokenUsed();
                        } catch (\Throwable $e) {
                            Log::debug('markIframeTokenUsed failed in fallback: ' . $e->getMessage());
                        }
                    }

                    $user = $fallbackUser;
                }
            }

            if (!$user) {
                RateLimiter::hit($key);
                Log::warning('Failed iframe login attempt - user not found', [
                    'email' => $request->email,
                    'token_preview' => substr($request->token, 0, 8) . '...',
                    'ip' => $request->ip()
                ]);
                return response('Unauthorized', 401);
            }
        }

        if (!$user->firma) {
            Log::error('User without firma tried iframe login', [
                'user_id' => $user->id,
                'ip' => $request->ip()
            ]);
            return response('User configuration error', 500);
        }

        // Erfolgreiche Authentifizierung
        Log::info('Successful iframe login', [
            'user_id' => $user->id,
            'email' => $user->email,
            'firma' => $user->firma->name,
            'ip' => $request->ip()
        ]);

        // Token-Usage tracken
        $user->markIframeTokenUsed();

        // Rate Limit zurücksetzen bei erfolgreichem Login
        RateLimiter::clear($key);

        // User einloggen
        Auth::login($user, true);

        // Weiterleitung basierend auf Rolle
        $redirectUrl = $request->get('redirect');
        if (!$redirectUrl) {
            $redirectUrl = $user->role->isDeveloper() ? '/tickets' : '/projects';
        }

        return redirect($redirectUrl);
    }

    private function validateApiKey(?string $apiKey): bool
    {
        if (!$apiKey || strlen($apiKey) < 10) {
            return false;
        }
        
        // In lokaler Entwicklung: Direkter String-Vergleich für einfache Integration
        if (app()->environment('local') && $apiKey === 'mein-super-sicherer-api-key-2024') {
            return true;
        }
        
        // Production: Hash-basierte Validierung
        return Hash::check($apiKey, config('app.iframe_api_key'));
    }
}