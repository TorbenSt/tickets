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

        $request->validate([
            'token' => 'required|string|size:64|regex:/^[a-zA-Z0-9]+$/',
            'email' => 'required|email|max:255',
            'redirect' => 'nullable|string|max:255'
        ]);

        // User finden
        $user = User::where('iframe_user_token', $request->token)
                   ->where('email', $request->email)
                   ->whereNotNull('iframe_user_token')
                   ->with('firma')
                   ->first();

        if (!$user) {
            RateLimiter::hit($key);
            Log::warning('Failed iframe login attempt - user not found', [
                'email' => $request->email,
                'token_preview' => substr($request->token, 0, 8) . '...',
                'ip' => $request->ip()
            ]);
            return response('User not found', 404);
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
        
        return Hash::check($apiKey, config('app.iframe_api_key'));
    }
}