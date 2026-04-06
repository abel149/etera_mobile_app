<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return $this->redirectToLogin($request);
        }

        // Check if session is expired
        if ($this->isSessionExpired()) {
            $this->handleSessionExpiration($request);
            return $this->redirectToLogin($request);
        }

        // Check if user account is approved
        if (!$this->isUserApproved()) {
            $this->handleUnapprovedUser($request);
            return $this->redirectToLogin($request);
        }

        // Check if user has a valid role
        if (empty(Auth::user()->role)) {
            $this->handleNullRole($request);
            return $this->redirectToLogin($request);
        }

        // Update last activity
        Session::put('last_activity', time());

        $response = $next($request);

        // Prevent caching of authenticated pages so that after logout the browser back button
        // won't display protected content from cache.
        try {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
        } catch (\Throwable $e) {
            // Ignore header failures to avoid breaking the request flow.
        }

        return $response;
    }

    /**
     * Check if the session has expired
     */
    protected function isSessionExpired(): bool
    {
        $lastActivity = Session::get('last_activity');
        $lifetime = 30 * 60; // 30 minutes in seconds

        if ($lastActivity && (time() - $lastActivity) > $lifetime) {
            return true;
        }

        return false;
    }

    /**
     * Check if user account is approved
     */
    protected function isUserApproved(): bool
    {
        $user = Auth::user();
        return $user && $user->approved;
    }

    /**
     * Handle session expiration
     */
    protected function handleSessionExpiration(Request $request): void
    {
        $user = Auth::user();
        
        // Log the session expiration
        Log::info('Session expired for user', [
            'user_id' => $user ? $user->id : 'unknown',
            'email' => $user ? $user->email : 'unknown',
            'ip' => $request->ip(),
            'url' => $request->url(),
        ]);

        // Set session_id to null in users table
        if ($user) {
            $user->session_id = null;
            $user->save();
        }


        // Clear the session and logout user
        Session::flush();
        Auth::logout();

        // Flash message about session expiration
        Session::flash('error', 'Your session has expired. Please log in again.');
    }

    /**
     * Handle unapproved user
     */
    protected function handleUnapprovedUser(Request $request): void
    {
        $user = Auth::user();
        
        // Log the unapproved access attempt
        Log::warning('Unapproved user access attempt', [
            'user_id' => $user ? $user->id : 'unknown',
            'email' => $user ? $user->email : 'unknown',
            'ip' => $request->ip(),
            'url' => $request->url(),
        ]);

        // Clear the session and logout user
        Session::flush();
        Auth::logout();

        // Flash message about account approval
        Session::flash('error', 'Your account is pending approval. Please wait for admin approval.');
    }

    /**
     * Handle user with null role
     */
    protected function handleNullRole(Request $request): void
    {
        $user = Auth::user();
        
        Log::warning('User with null role attempted access', [
            'user_id' => $user ? $user->id : 'unknown',
            'email' => $user ? $user->email : 'unknown',
            'ip' => $request->ip(),
            'url' => $request->url(),
        ]);

        Session::flush();
        Auth::logout();

        Session::flash('error', 'Your account does not have a valid role assigned. Please contact the administrator.');
    }

    /**
     * Redirect to login page
     */
    protected function redirectToLogin(Request $request): Response
    {
        // Store intended URL for redirect after login
        // Skip API routes so login doesn't redirect to /api/notifications etc.
        if ($request->isMethod('get') && !str_starts_with($request->path(), 'api/')) {
            Session::put('url.intended', $request->url());
        }

        // If it's an AJAX request, return 401 status
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'error' => 'Authentication required',
                'redirect' => '/login'
            ], 401);
        }

        // Redirect to login with appropriate message
        return redirect('/login');
    }
} 