<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // If user is already authenticated, redirect to appropriate dashboard
        if (Auth::check()) {
            $user = Auth::user();
            
            // Check if user account is approved
            if (!$user->approved) {
                Auth::logout();
                return redirect('/login')->with('error', 'Your account is pending approval. Please wait for admin approval.');
            }

            // Check if user has a valid role
            if (empty($user->role)) {
                Auth::logout();
                return redirect('/login')->with('error', 'Please login again!');
            }

            // Redirect based on user role
            $redirectUrl = $this->getRedirectUrlForRole($user->role);
            
            return redirect($redirectUrl);
        }

        return $next($request);
    }

    /**
     * Get redirect URL based on user role
     */
    protected function getRedirectUrlForRole(?string $role): string
    {
        return match ($role) {
            'admin', 'superadmin' => '/admin',
            'insurance' => '/insurance',
            'others' => '/business-owner',
            'business_owner' => '/business-owner',
            'garage' => '/garage/proformas',
            'shop' => '/spare-part-shops/proformas',
            'marketer' => '/marketer',
            'employee' => '/employee',
            'manager' => '/manager/dashboard',
            'operator' => '/operator/dashboard',
            'accountant' => '/finance',
            default => '/login',
        };
    }
} 