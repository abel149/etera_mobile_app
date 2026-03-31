<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class NoCacheAuthenticated
{
    /**
     * Add no-cache headers for authenticated users to prevent browser back button
     * from showing protected pages after logout.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (Auth::check()) {
            try {
                $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
                $response->headers->set('Pragma', 'no-cache');
                $response->headers->set('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
            } catch (\Throwable $e) {
                // Ignore header failures to avoid breaking the request flow.
            }
        }

        return $response;
    }
}
