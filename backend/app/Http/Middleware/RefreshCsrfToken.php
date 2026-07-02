<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RefreshCsrfToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // API routes use Bearer tokens — skip session/CSRF entirely
        if ($request->is('api/*')) {
            return $next($request);
        }

        // Only refresh CSRF token if session is still valid
        // Let AuthenticateUser handle expiration and redirects
        if (!$this->isSessionExpired()) {
            $this->refreshCsrfTokenIfNeeded();
        }

        // Add CSRF token to response headers for AJAX requests
        $response = $next($request);

        if ($request->ajax() || $request->wantsJson()) {
            $response->headers->set('X-CSRF-TOKEN', csrf_token());
        }

        return $response;
    }

    /**
     * Check if the session has expired
     */
    protected function isSessionExpired(): bool
    {
        $lastActivity = Session::get('last_activity');
        // Use config fallback to avoid reading 0 as infinite
        $lifetimeMinutes = config('session.lifetime', 120);
        $lifetime = $lifetimeMinutes * 60; // Convert to seconds

        if ($lastActivity && (time() - $lastActivity) > $lifetime) {
            return true;
        }

        return false;
    }

    /**
     * Handle expired session
     */
    protected function handleExpiredSession(Request $request): void
    {
        // Log the session expiration
        Log::info('Session expired for user', [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->url(),
        ]);

        // If it's an AJAX request, return 419 status
        if ($request->ajax() || $request->wantsJson()) {
            abort(419, 'Session expired. Please refresh the page and try again.');
        }
        // For regular requests, let AuthenticateUser middleware handle redirect and logout cleanly
    }

    /**
     * Refresh CSRF token if needed
     */
    protected function refreshCsrfTokenIfNeeded(): void
    {
        // Update last activity timestamp
        Session::put('last_activity', time());

        // Regenerate CSRF token if it's old (every 30 minutes)
        $tokenAge = Session::get('csrf_token_age', 0);
        if ((time() - $tokenAge) > 1800) { // 30 minutes
            Session::regenerateToken();
            Session::put('csrf_token_age', time());
        }
    }
} 