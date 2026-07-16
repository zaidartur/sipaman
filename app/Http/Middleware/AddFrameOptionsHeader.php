<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AddFrameOptionsHeader
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Prevent Clickjacking
        $response->headers->set('X-Frame-Options', 'DENY');
        
        // Prevent MIME type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // ALTERNATIVE: Use 'SAMEORIGIN' if you want to allow your own site 
        // to embed itself in an iframe (e.g., for live preview features)
        // $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        return $response;
    }
}
