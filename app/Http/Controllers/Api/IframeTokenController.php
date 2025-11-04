<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class IframeTokenController extends Controller
{
    public function generateToken(Request $request)
    {
        // Rate Limiting
        $key = 'iframe-token:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 20)) {
            Log::warning('iFrame token generation rate limit exceeded', [
                'ip' => $request->ip()
            ]);
            return response()->json(['error' => 'Too many requests'], 429);
        }

        // API Key validieren
        if (!$this->validateApiKey($request->header('X-API-Key') ?? $request->input('api_key'))) {
            RateLimiter::hit($key);
            return response()->json(['error' => 'Invalid API key'], 401);
        }

        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'parent_domain' => 'nullable|string|max:255',
            'expires_in' => 'nullable|integer|min:60|max:3600' // 1 Minute bis 1 Stunde
        ]);

        $user = User::with('firma')->find($request->user_id);
        
        if (!$user) {
            RateLimiter::hit($key);
            return response()->json(['error' => 'User not found'], 404);
        }

        if (!$user->firma) {
            return response()->json(['error' => 'User has no firma assigned'], 400);
        }

        // Token generieren (falls noch keiner existiert)
        if (!$user->iframe_user_token) {
            $user->generateIframeUserToken();
        }

        // iFrame URL mit Token + Email Parameter erstellen
        $iframeUrl = config('app.url') . '/iframe/auth?' . http_build_query([
            'token' => $user->iframe_user_token,
            'email' => $user->email,
            'domain' => $request->input('parent_domain', $request->header('Origin', 'unknown'))
        ]);

        Log::info('iFrame token generated', [
            'user_id' => $user->id,
            'email' => $user->email,
            'parent_domain' => $request->input('parent_domain'),
            'ip' => $request->ip()
        ]);

        return response()->json([
            'success' => true,
            'iframe_url' => $iframeUrl,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->value
            ],
            'expires_in' => $request->input('expires_in', 3600)
        ]);
    }

    private function validateApiKey(?string $apiKey): bool
    {
        if (!$apiKey || strlen($apiKey) < 10) {
            return false;
        }
        
        return Hash::check($apiKey, config('app.iframe_api_key'));
    }
}