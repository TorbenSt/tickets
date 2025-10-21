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
     * 
     * POST /api/iframe/token
     */
    public function generateToken(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'api_key' => 'required|string',
            'parent_domain' => 'required|string',
            'expires_in' => 'nullable|integer|min:1|max:1440' // Max 24 hours
        ]);

        // Validate API key (you should store this in .env)
        $expectedApiKey = config('app.iframe_api_key');
        if (!$expectedApiKey || !Hash::check($request->api_key, $expectedApiKey)) {
            abort(401, 'Invalid API key');
        }

        $user = User::findOrFail($request->user_id);
        $expiresIn = $request->expires_in ?? 60; // Default 1 hour

        // Create token with expiration
        $token = $user->createIframeToken($request->parent_domain, $expiresIn);

        return response()->json([
            'token' => $token,
            'iframe_url' => route('iframe.dashboard') . '?iframe_token=' . $token,
            'expires_at' => now()->addMinutes($expiresIn)->toISOString(),
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->value
            ]
        ]);
    }

    /**
     * iframe dashboard - redirect based on role
     */
    public function dashboard()
    {
        if (!Auth::check()) {
            abort(401, 'Authentication required');
        }
        
        return Auth::user()->role->isDeveloper() 
            ? redirect()->route('iframe.tickets.index')
            : redirect()->route('iframe.projects.index');
    }
}
