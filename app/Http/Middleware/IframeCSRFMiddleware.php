<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IframeCSRFMiddleware
{
    /**
     * Handle an incoming request for iframe context.
     * Disables CSRF for iframe requests that are token-authenticated.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if this is an iframe request with token authentication
        $hasIframeToken = $request->has('iframe_token') || $request->header('X-Iframe-Token');
        $isIframeSession = session('is_iframe', false);
        
        if ($hasIframeToken || $isIframeSession) {
            // Temporarily disable CSRF verification for iframe requests
            $request->session()->regenerateToken();
            
            // Add iframe token to all forms in the response
            $response = $next($request);
            
            if ($response instanceof \Illuminate\Http\Response) {
                $content = $response->getContent();
                
                // Inject iframe token into forms
                if ($iframeToken = session('iframe_token')) {
                    $tokenInput = '<input type="hidden" name="iframe_token" value="' . $iframeToken . '">';
                    $content = str_replace('</form>', $tokenInput . '</form>', $content);
                    $response->setContent($content);
                }
            }
            
            return $response;
        }
        
        return $next($request);
    }
}
