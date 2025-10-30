<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'api/*',
        'iframe/*',  // Exclude all iframe routes from CSRF
    ];

    /**
     * Determine if the request has a URI that should pass through CSRF verification.
     */
    protected function inExceptArray($request)
    {
        // Check if request is iframe context
        if ($request->has('iframe_token') || session('is_iframe')) {
            return true; // Skip CSRF for iframe requests
        }

        return parent::inExceptArray($request);
    }
}
