<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class IframeAuthMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check for iframe token
        $token = $request->get('iframe_token') ?? $request->header('X-Iframe-Token');
        
        if ($token) {
            $accessToken = PersonalAccessToken::findToken($token);
            
            if ($accessToken && $accessToken->can('iframe:access')) {
                $user = $accessToken->tokenable;
                
                // Validate domain if specified
                $allowedDomain = collect($accessToken->abilities)
                    ->first(fn($ability) => str_starts_with($ability, 'domain:'));
                
                if ($allowedDomain) {
                    $domain = str_replace('domain:', '', $allowedDomain);
                    $referer = $request->header('referer');
                    
                    if (!$referer || !str_contains($referer, $domain)) {
                        abort(403, 'Invalid domain');
                    }
                }
                
                Auth::login($user);
                
                // Set iframe session flag
                session(['is_iframe' => true]);
                
                return $next($request);
            }
        }
        
        // Fall back to normal authentication
        return $next($request);
    }
}
