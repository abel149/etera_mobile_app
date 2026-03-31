<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HandleErrors
{
    /**
     * Wrap each request in a try/catch.
     * On any unhandled exception → redirect to role-appropriate dashboard with error toast.
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            return $next($request);
        } catch (\Throwable $e) {
            // Let JSON / API requests bubble up normally
            if ($request->expectsJson()) {
                throw $e;
            }

            \Illuminate\Support\Facades\Log::error('HandleErrors middleware caught: ' . $e->getMessage(), [
                'url'   => $request->fullUrl(),
                'trace' => $e->getTraceAsString(),
            ]);

            $dashboard = $this->getDashboardUrl();
            $message   = $e->getMessage() ?: 'Connection error. Please try again.';

            return redirect($dashboard)->with('error', $message);
        }
    }

    /**
     * Determine the dashboard URL for the current user's role.
     */
    private function getDashboardUrl(): string
    {
        $user = auth()->user();
        if (!$user) {
            return '/login';
        }

        return match ($user->role) {
            'admin', 'superadmin' => '/admin',
            'shop'                => '/spare-part-shops/proformas',
            'garage'              => '/garage/proformas',
            'insurance'           => '/insurance',
            'business-owner',
            'others'              => '/business-owner',
            'accountant'          => '/finance',
            'marketer'            => '/marketer',
            'employee'            => '/employee',
            'manager'             => '/manager/dashboard',
            'operator'            => '/operator/dashboard',
            default               => '/login',
        };
    }
}
