<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;

class IframeController extends Controller
{
    public function authenticate(Request $request)
    {
        // Rate Limiting pro IP (max 10 Versuche pro Minute)
        $key = 'iframe-auth:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 10)) {
            Log::warning('iFrame auth rate limit exceeded', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
            return response()->json(['error' => 'Too many attempts'], 429);
        }

        // API Key validieren
        if (!$this->validateApiKey($request->header('X-API-Key'))) {
            RateLimiter::hit($key);
            Log::warning('Invalid API key for iframe auth', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
            return response()->json(['error' => 'Invalid API key'], 401);
        }

        $request->validate([
            'token' => 'required|string|size:64|regex:/^[a-zA-Z0-9]+$/', // Nur alphanumerisch
            'email' => 'required|email|max:255'
        ]);

        // User mit Token + Email finden (doppelte Validierung)
        $user = User::findByIframeCredentials($request->token, $request->email);

        if (!$user) {
            RateLimiter::hit($key);
            Log::warning('Failed iframe authentication attempt', [
                'email' => $request->email,
                'token_preview' => substr($request->token, 0, 8) . '...',
                'ip' => $request->ip()
            ]);
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        // Zusätzliche Sicherheitschecks
        if (!$user->firma) {
            Log::error('User without firma tried iframe auth', ['user_id' => $user->id]);
            return response()->json(['error' => 'User configuration error'], 500);
        }

        // Erfolgreiche Authentifizierung loggen
        Log::info('Successful iframe authentication', [
            'user_id' => $user->id,
            'email' => $user->email,
            'firma' => $user->firma->name,
            'ip' => $request->ip()
        ]);

        // Token-Usage tracken
        $user->markIframeTokenUsed();

        // Rate Limit zurücksetzen bei erfolgreichem Login
        RateLimiter::clear($key);

        // Laravel Session erstellen
        auth()->login($user, true);

        // Prüfen ob das ein AJAX-Request ist (dann JSON zurückgeben)
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role->value,
                    'firma' => [
                        'id' => $user->firma->id,
                        'name' => $user->firma->name
                    ]
                ],
                'redirect_url' => $user->role->isDeveloper() ? '/tickets' : '/projects'
            ]);
        }

        // Für direkte iFrame-Aufrufe: Weiterleitung zur entsprechenden Seite
        $redirectUrl = $user->role->isDeveloper() ? '/tickets' : '/projects';
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