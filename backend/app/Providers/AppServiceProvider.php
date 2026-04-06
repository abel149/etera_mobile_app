<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Schema::defaultStringLength(191);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $handler = $this->app->make(\Illuminate\Contracts\Debug\ExceptionHandler::class);

        // Handle unauthenticated exceptions - redirect to login
        $handler->renderable(function (AuthenticationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthenticated.', 'redirect' => '/login'], 401);
            }
            return redirect()->guest('/login')->with('error', 'Please login again!');
        });

        // Handle 419 CSRF token mismatch - redirect back instead of error page
        $handler->renderable(function (TokenMismatchException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Session expired. Please refresh the page.'], 419);
            }
            return redirect()->back()
                ->withInput($request->except('_token', 'password', 'password_confirmation'))
                ->with('error', 'Session expired. Please try again.');
        });

        // Handle model not found - redirect to dashboard
        $handler->renderable(function (ModelNotFoundException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Record not found.'], 404);
            }
            $dashboard = $this->getDashboardUrl();
            return redirect($dashboard)->with('error', 'The requested record was not found.');
        });

        // Handle 403/404/500 HTTP errors - redirect to dashboard with toast
        $handler->renderable(function (HttpException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['error' => $e->getMessage() ?: 'An error occurred.'], $e->getStatusCode());
            }
            $code = $e->getStatusCode();
            // Let 4xx error page handle 404s naturally if user is not logged in
            if (!auth()->check()) {
                return null; // fall through to default handler
            }
            $dashboard = $this->getDashboardUrl();
            $message = match ($code) {
                403 => 'You do not have permission to access this page.',
                404 => 'Page not found.',
                500 => 'Connection error. Please try again.',
                default => $e->getMessage() ?: 'An error occurred.',
            };
            return redirect($dashboard)->with('error', $message);
        });
    }

    /**
     * Get the appropriate dashboard URL based on the authenticated user's role.
     */
    private function getDashboardUrl(): string
    {
        $user = auth()->user();
        if (!$user) return '/login';

        return match ($user->role) {
            'admin'          => '/admin',
            'shop'           => '/spare-part-shops/proformas',
            'garage'         => '/garage/proformas',
            'insurance'      => '/insurance',
            'business-owner' => '/business-owner',
            'accountant'     => '/accountant',
            'marketer'       => '/marketer',
            'employee'       => '/employee',
            'manager'        => '/manager',
            'operator'       => '/operator',
            default          => '/login',
        };
    }
}
