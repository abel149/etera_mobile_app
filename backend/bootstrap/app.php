<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use App\Mail\ServerErrorMail;
use App\Models\AppError;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        then: function () {
            // Versioned mobile API: /api/v1/...
            Route::middleware('api')
                ->prefix('api/v1')
                ->group(base_path('routes/api_v1.php'));
        },
    )

    // =========================
    // MIDDLEWARE
    // =========================
    ->withMiddleware(function (Middleware $middleware) {

        // Auto-start Etera-Chereta service
        $middleware->append(
            \App\Http\Middleware\AutoStartEteraCheretaMiddleware::class
        );

        // Refresh CSRF token
        $middleware->append(
            \App\Http\Middleware\RefreshCsrfToken::class
        );

        // Prevent caching for authenticated users (fix back-button showing private pages after logout)
        $middleware->append(
            \App\Http\Middleware\NoCacheAuthenticated::class
        );

        // Route middleware aliases
        $middleware->alias([
            'auth.user' => \App\Http\Middleware\AuthenticateUser::class,
            'guest'     => \App\Http\Middleware\RedirectIfAuthenticated::class,
        ]);
    })

    // =========================
    // EXCEPTIONS (Laravel 11)
    // =========================
    ->withExceptions(function (Exceptions $exceptions) {

        /*
        |--------------------------------------------------------------------------
        | INLINE DEVELOPER LIST
        |--------------------------------------------------------------------------
        */
        $developers = [
            ['name' => 'Abel Ashenafi dev', 'email' => 'ashenafiabel@gmail.com'],
            ['name' => 'Beemnet Abraham dev', 'email' => 'beemnetabraham1@gmail.com'],
            ['name' => 'Husni owner',   'email' => 'hsherif77@gmail.com'],
        ];

        /*
        |--------------------------------------------------------------------------
        | RENDER EXCEPTIONS (ERROR PAGES)
        |--------------------------------------------------------------------------
        */
        $exceptions->renderable(function (\Throwable $e, $request) use ($developers) {

            $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;

            // =========================
            // REPORT 500+ ERRORS (DB + EMAIL + LOG)
            // =========================
            if ($status >= 500) {

                // Log to laravel.log
                \Log::error('ðŸ”¥ 500 ERROR DETECTED', [
                    'exception' => $e->getMessage(),
                    'user'      => auth()->user()?->email ?? 'Guest',
                    'url'       => request()->fullUrl(),
                ]);

                // Hash to prevent duplicate reporting
                $hash = md5($e->getMessage() . $e->getFile() . $e->getLine());

                if (!AppError::where('hash', $hash)->where('fixed', false)->exists()) {

                    // Save to database
                    AppError::create([
                        'user_id'     => auth()->id(),
                        'url'         => request()->fullUrl(),
                        'method'      => request()->method(),
                        'status_code' => $status,
                        'message'     => $e->getMessage(),
                        'trace'       => $e->getTraceAsString(),
                        'hash'        => $hash,
                        'fixed'       => false,
                    ]);

                    // Prepare user array
                    $userData = ['email' => auth()->user()?->email ?? 'Guest'];

                    // Email developers
                    foreach ($developers as $dev) {
                        try {
                            Mail::to($dev['email'])->send(new ServerErrorMail([
                                'errorMessage' => (string)$e->getMessage(),
                                'status'  => $status,
                                'url'     => request()->fullUrl(),
                                'method'  => request()->method(),
                                'trace'   => (string)$e->getTraceAsString(),
                                'user'    => $userData,
                            ]));
                        } catch (\Throwable $mailError) {
                            \Log::error('Failed to send server error email', [
                                'original_exception' => $e->getMessage(),
                                'mail_exception' => $mailError->getMessage(),
                                'developer_email' => $dev['email'],
                            ]);
                        }
                    }
                }
            }

            // =========================
            // JSON FOR API REQUESTS
            // =========================
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e instanceof HttpExceptionInterface
                        ? $e->getMessage() ?: 'Error'
                        : 'Server error',
                    'errors'  => config('app.debug') ? ['exception' => $e->getMessage()] : [],
                ], $status);
            }

            // =========================
            // RENDER ERROR PAGES (web only)
            // =========================

            // 419 - Page Expired
            if ($e instanceof TokenMismatchException) {
                return response()->view('errors.419', [], 419);
            }

            // HTTP exceptions (4xx / 5xx)
            if ($e instanceof HttpExceptionInterface) {
                if ($status >= 400 && $status < 500) {
                    return response()->view('errors.4xx', ['exception' => $e], $status);
                }
                if ($status >= 500) {
                    return response()->view('errors.5xx', ['exception' => $e], $status);
                }
            }

            // Non-HTTP exceptions → 500
            return response()->view('errors.5xx', ['exception' => $e], 500);
        });

        /*
        |--------------------------------------------------------------------------
        | DO NOT REPORT
        |--------------------------------------------------------------------------
        */
        $exceptions->dontReport([
            TokenMismatchException::class,
        ]);
    })

    ->create();
