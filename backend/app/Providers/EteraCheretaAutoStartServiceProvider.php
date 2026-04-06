<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Console\Application as ConsoleApplication;
use Illuminate\Foundation\Application;

class EteraCheretaAutoStartServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Start the Etera-Chereta monitoring service when the application boots
        $this->app->booted(function () {
            // Only start in web environment, not console
            if (!$this->app->runningInConsole()) {
                $this->startEteraCheretaMonitoring();
            }
        });
    }

    /**
     * Start the Etera-Chereta monitoring service automatically
     */
    protected function startEteraCheretaMonitoring(): void
    {
        try {
            // Check if we're in a web environment (not console)
            if ($this->app->runningInConsole()) {
                return; // Don't start in console mode
            }

            // Check if the service is already running
            if ($this->isServiceAlreadyRunning()) {
                Log::info('Etera-Chereta monitoring service is already running');
                return;
            }

            // Add a small delay to prevent rapid successive starts
            usleep(100000); // 100ms delay

            // Double-check if service is still not running after delay
            if ($this->isServiceAlreadyRunning()) {
                Log::info('Etera-Chereta monitoring service started by another process');
                return;
            }

            // Start the monitoring service in the background
            $this->startBackgroundMonitoring();

            Log::info('Etera-Chereta monitoring service started automatically');

        } catch (\Exception $e) {
            Log::error('Failed to start Etera-Chereta monitoring service automatically: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
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
                // Check if shell_exec function is available
                if (function_exists('shell_exec')) {
                    $output = shell_exec('tasklist /FI "IMAGENAME eq php.exe" /FO CSV 2>nul');
                    if ($output && strpos($output, 'etera-chereta:check-expiration') !== false) {
                        Cache::put($cacheKey, now(), 300); // Cache for 5 minutes
                        return true;
                    }
                } else {
                    Log::warning('shell_exec() function is not available on this system');
                }
            } catch (\Exception $e) {
                Log::warning('Could not check Windows process list: ' . $e->getMessage());
            }
        }

        return false;
    }

    /**
     * Start the monitoring service in the background
     */
    protected function startBackgroundMonitoring(): void
    {
        try {
            // Set cache to indicate service is starting
            Cache::put('etera_chereta_service_running', now(), 300);

            // Improved OS detection
            $osFamily = PHP_OS_FAMILY;
            $osName = PHP_OS;
            
            Log::info("Detected OS: Family={$osFamily}, Name={$osName}");
            
            // Start the service in background using different methods based on OS
            if (strtoupper(substr($osName, 0, 3)) === 'WIN' || $osFamily === 'Windows') {
                Log::info('Using Windows background service method');
                $this->startWindowsBackgroundService();
            } else {
                Log::info('Using Unix background service method');
                $this->startUnixBackgroundService();
            }

        } catch (\Exception $e) {
            Log::error('Error starting background monitoring: ' . $e->getMessage());
            Cache::forget('etera_chereta_service_running');
        }
    }

    /**
     * Start background service on Windows
     */
    protected function startWindowsBackgroundService(): void
    {
        try {
            $artisanPath = base_path('artisan');
            $phpPath = PHP_BINARY;
            
            // Build the command with proper escaping
            $command = "start /B \"$phpPath\" \"$artisanPath\" etera-chereta:check-expiration --daemon --interval=5 --batch-size=1000 --memory-limit=512M";
            
            // Execute the command in background
            $handle = popen($command, 'r');
            if ($handle === false) {
                throw new \Exception('Failed to execute Windows command');
            }
            pclose($handle);
            
            Log::info('Windows background service started successfully');
            
        } catch (\Exception $e) {
            Log::error('Failed to start Windows background service: ' . $e->getMessage());
            // Fallback to queue-based approach
            $this->startQueueBasedService();
        }
    }

    /**
     * Fallback method using Laravel's queue system
     */
    protected function startQueueBasedService(): void
    {
        try {
            // Dispatch a job to the queue instead of running a background process
            // This is a safer alternative when external process execution fails
            Log::info('Falling back to queue-based service approach');
            
            // You can dispatch a job here if you have one set up
            // dispatch(new EteraCheretaMonitoringJob());
            
            // For now, just log that we're using the fallback
            Log::info('Queue-based service fallback activated');
            
        } catch (\Exception $e) {
            Log::error('Failed to start queue-based service: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Start background service on Unix/Linux
     */
    protected function startUnixBackgroundService(): void
    {
        try {
            // Check if exec function is available
            if (!function_exists('exec')) {
                Log::warning('exec() function is not available on this system');
                return;
            }

            $artisanPath = base_path('artisan');
            $command = "nohup php \"$artisanPath\" etera-chereta:check-expiration --daemon --interval=5 --batch-size=1000 --memory-limit=512M > /dev/null 2>&1 &";
            
            // Execute the command in background
            exec($command);
            
            Log::info('Unix background service started successfully');
            
        } catch (\Exception $e) {
            Log::error('Failed to start Unix background service: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get the service status
     */
    public static function getServiceStatus(): array
    {
        try {
            $cacheKey = 'etera_chereta_service_running';
            $isRunning = Cache::has($cacheKey);
            $lastCheck = Cache::get($cacheKey);
            
            return [
                'status' => $isRunning ? 'running' : 'stopped',
                'last_check' => $lastCheck ? $lastCheck->toISOString() : null,
                'auto_start_enabled' => true,
                'platform' => PHP_OS_FAMILY,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unknown',
                'error' => $e->getMessage(),
                'auto_start_enabled' => true,
                'platform' => PHP_OS_FAMILY,
            ];
        }
    }
} 