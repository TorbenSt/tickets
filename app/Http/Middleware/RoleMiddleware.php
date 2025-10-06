<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        if ($role === 'customer' && !$user->role->isCustomer()) {
            abort(403, 'Access denied. Customer role required.');
        }
        
        if ($role === 'developer' && !$user->role->isDeveloper()) {
            abort(403, 'Access denied. Developer role required.');
        }

        return $next($request);
    }
}