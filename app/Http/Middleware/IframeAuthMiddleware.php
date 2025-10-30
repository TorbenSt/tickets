<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

class IframeAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Check for iframe token from URL parameter or header
        $token = $request->get('iframe_token') ?? $request->header('X-Iframe-Token');
        
        if ($token && !Auth::check()) {
            $accessToken = PersonalAccessToken::findToken($token);
            
            if ($accessToken && $accessToken->can('iframe:access')) {
                // Check if token is not expired
                if ($accessToken->expires_at && $accessToken->expires_at->isPast()) {
                    return response()->json(['error' => 'Token expired'], 401);
                }
                
                $user = $accessToken->tokenable;
                
                // Validate domain if specified
                $allowedDomain = collect($accessToken->abilities)
                    ->first(fn($ability) => str_starts_with($ability, 'domain:'));
                
                if ($allowedDomain) {
                    $domain = str_replace('domain:', '', $allowedDomain);
                    $referer = $request->header('referer');
                    
                    if ($referer && !str_contains($referer, $domain)) {
                        return response()->json(['error' => 'Invalid domain'], 403);
                    }
                }
                
                // Start fresh session for iframe
                $request->session()->invalidate();
                $request->session()->regenerate();
                
                // Log the user in for this request
                Auth::login($user);
                
                // Set iframe session flag
                session(['is_iframe' => true, 'iframe_token' => $token]);
                
                return $next($request);
            } else {
                return response()->json(['error' => 'Invalid or expired token'], 401);
            }
        }
        
        // If no iframe token but user is authenticated, proceed
        if (Auth::check()) {
            return $next($request);
        }
        
        // No authentication available - redirect to login
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
        
        return redirect()->guest(route('login'));
    }
}
