<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class AutoStartEteraCheretaMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if this is the first request and start the service if needed
        $this->ensureEteraCheretaServiceRunning();

        return $next($request);
    }

    /**
     * Ensure the Etera-Chereta monitoring service is running
     */
    protected function ensureEteraCheretaServiceRunning(): void
    {
        try {
            // Check if service is already running
            if ($this->isServiceAlreadyRunning()) {
                return;
            }

            // Start the service in background
            $this->startBackgroundService();

            Log::info('Etera-Chereta monitoring service auto-started via middleware');

        } catch (\Exception $e) {
            Log::error('Failed to auto-start Etera-Chereta service via middleware: ' . $e->getMessage());
        }
    }

    /**
     * Check if the service is already running
     */
    protected function isServiceAlreadyRunning(): bool
    {
        $cacheKey = 'etera_chereta_service_running';
        
        // Check cache first
        if (Cache::has($cacheKey)) {
            $lastCheck = Cache::get($cacheKey);
            // If checked within last 5 minutes, consider it running
            if (now()->diffInMinutes($lastCheck) < 5) {
                return true;
            }
        }

        // Check if the artisan command is running (Windows specific)
        if (PHP_OS_FAMILY === 'Windows') {
            try {
                if (function_exists('shell_exec')) {
                    $output = shell_exec('tasklist /FI "IMAGENAME eq php.exe" /FO CSV 2>nul');
                    if (strpos($output, 'etera-chereta:check-expiration') !== false) {
                        Cache::put($cacheKey, now(), 300); // Cache for 5 minutes
                        return true;
                    }
                } else {
                    Log::warning('shell_exec() function is not available on this system');
                }
            } catch (\Exception $e) {
                // Ignore errors in process checking
            }
        }

        return false;
    }

    /**
     * Start the background service
     */
    protected function startBackgroundService(): void
    {
        try {
            // Set cache to indicate service is starting
            Cache::put('etera_chereta_service_running', now(), 300);

            $artisanPath = base_path('artisan');
            
            if (PHP_OS_FAMILY === 'Windows') {
                // Windows: Use start command to run in background
                $command = "start /B php \"$artisanPath\" etera-chereta:check-expiration --daemon --interval=5 --batch-size=1000 --memory-limit=512M";
                pclose(popen($command, 'r'));
            } else {
                // Unix/Linux: Use nohup to run in background
                $command = "nohup php \"$artisanPath\" etera-chereta:check-expiration --daemon --interval=5 --batch-size=1000 --memory-limit=512M > /dev/null 2>&1 &";
                if (function_exists('exec')) {
                    exec($command);
                } else {
                    Log::warning('exec() function is not available on this system');
                    throw new \Exception('exec() function is not available on this system');
                }
            }

            Log::info('Background service started successfully via middleware');

        } catch (\Exception $e) {
            Log::error('Error starting background service via middleware: ' . $e->getMessage());
            Cache::forget('etera_chereta_service_running');
            throw $e;
        }
    }
} 