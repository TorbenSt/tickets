<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class IframeController extends Controller
{
    /**
     * Generate iframe token for external integration
     */
    public function generateToken(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'api_key' => 'required|string',
            'parent_domain' => 'required|string',
            'expires_in' => 'nullable|integer|min:1|max:1440'
        ]);

        // API-Key aus Environment laden
        $storedApiKey = config('services.iframe.api_key');
        
        if (!$storedApiKey) {
            return response()->json(['error' => 'API key not configured'], 500);
        }

        // API-Key validieren
        if (!Hash::check($request->api_key, $storedApiKey)) {
            return response()->json(['error' => 'Invalid API key'], 401);
        }

        $user = User::findOrFail($request->user_id);
        $domain = parse_url($request->parent_domain, PHP_URL_HOST) ?? $request->parent_domain;
        $expiresIn = $request->expires_in ?? 60; // Default 1 hour

        // Token erstellen mit Domain-Beschränkung
        $token = $user->createToken('iframe-access', [
            'iframe:access',
            'domain:' . $domain
        ]);
        
        // Expiration setzen
        $token->accessToken->expires_at = now()->addMinutes($expiresIn);
        $token->accessToken->save();

        return response()->json([
            'token' => $token->plainTextToken,
            'iframe_url' => route('iframe.dashboard') . '?iframe_token=' . $token->plainTextToken,
            'expires_at' => $token->accessToken->expires_at->toISOString()
        ]);
    }

    /**
     * iframe dashboard - redirect based on role
     */
    public function dashboard(Request $request)
    {
        // Check if user is authenticated (should be handled by middleware)
        if (!Auth::check()) {
            return response()->view('iframe.login-error', [
                'message' => 'Authentication failed. Please check your token.'
            ], 401);
        }

        $user = Auth::user();
        
        // Preserve iframe_token in redirects
        $token = $request->get('iframe_token') ?? session('iframe_token');
        $tokenParam = $token ? '?iframe_token=' . $token : '';

        return $user->role->isDeveloper() 
            ? redirect()->to(route('iframe.tickets.index') . $tokenParam)
            : redirect()->to(route('iframe.projects.index') . $tokenParam);
    }

    /**
     * Debug endpoint for iframe authentication
     */
    public function debug(Request $request)
    {
        return response()->json([
            'authenticated' => Auth::check(),
            'user' => Auth::user()?->only(['id', 'name', 'email', 'role']),
            'session_data' => [
                'is_iframe' => session('is_iframe'),
                'iframe_token' => session('iframe_token') ? 'present' : 'missing'
            ],
            'request_data' => [
                'has_token_param' => $request->has('iframe_token'),
                'token_header' => $request->header('X-Iframe-Token') ? 'present' : 'missing',
                'csrf_token' => csrf_token()
            ]
        ]);
    }
}
