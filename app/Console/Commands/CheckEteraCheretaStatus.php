<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CheckEteraCheretaStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'etera-chereta:status {--start : Start the service if not running}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check the status of Etera-Chereta monitoring service and optionally start it';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Checking Etera-Chereta monitoring service status...');
        
        $status = $this->getServiceStatus();
        
        $this->displayStatus($status);
        
        // If --start flag is provided and service is not running, start it
        if ($this->option('start') && $status['status'] !== 'running') {
            $this->startService();
        }
        
        return 0;
    }

    /**
     * Get the current service status
     */
    private function getServiceStatus(): array
    {
        try {
            $cacheKey = 'etera_chereta_service_running';
            $isRunning = Cache::has($cacheKey);
            $lastCheck = Cache::get($cacheKey);
            
            // Check if the artisan command is actually running
            $processRunning = $this->isProcessRunning();
            
            return [
                'status' => $isRunning && $processRunning ? 'running' : 'stopped',
                'last_check' => $lastCheck ? $lastCheck->toISOString() : null,
                'auto_start_enabled' => true,
                'platform' => PHP_OS_FAMILY,
                'process_running' => $processRunning,
                'cache_status' => $isRunning ? 'active' : 'inactive',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unknown',
                'error' => $e->getMessage(),
                'auto_start_enabled' => true,
                'platform' => PHP_OS_FAMILY,
                'process_running' => false,
                'cache_status' => 'error',
            ];
        }
    }

    /**
     * Check if the process is actually running
     */
    private function isProcessRunning(): bool
    {
        try {
            if (PHP_OS_FAMILY === 'Windows') {
                if (function_exists('shell_exec')) {
                    $output = shell_exec('tasklist /FI "IMAGENAME eq php.exe" /FO CSV 2>nul');
                    return strpos($output, 'etera-chereta:check-expiration') !== false;
                } else {
                    Log::warning('shell_exec() function is not available on this system');
                    return false;
                }
            } else {
                if (function_exists('shell_exec')) {
                    $output = shell_exec('ps aux | grep "etera-chereta:check-expiration" | grep -v grep');
                    return !empty($output);
                } else {
                    Log::warning('shell_exec() function is not available on this system');
                    return false;
                }
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Display the service status
     */
    private function displayStatus(array $status): void
    {
        $this->info('📊 Service Status Information:');
        $this->info('================================');
        
        // Status
        $statusColor = $status['status'] === 'running' ? 'green' : 'red';
        $this->line("🔄 Status: <fg={$statusColor}>{$status['status']}</>");
        
        // Platform
        $this->line("💻 Platform: {$status['platform']}");
        
        // Auto-start
        $this->line("🚀 Auto-start: " . ($status['auto_start_enabled'] ? 'Enabled' : 'Disabled'));
        
        // Process status
        $processColor = $status['process_running'] ? 'green' : 'red';
        $this->line("⚙️  Process: <fg={$processColor}>" . ($status['process_running'] ? 'Running' : 'Not Running') . "</>");
        
        // Cache status
        $cacheColor = $status['cache_status'] === 'active' ? 'green' : 'red';
        $this->line("💾 Cache: <fg={$cacheColor}>{$status['cache_status']}</>");
        
        // Last check
        if ($status['last_check']) {
            $this->line("⏰ Last Check: {$status['last_check']}");
        }
        
        // Error if any
        if (isset($status['error'])) {
            $this->error("❌ Error: {$status['error']}");
        }
        
        $this->info('================================');
        
        // Recommendations
        if ($status['status'] !== 'running') {
            $this->warn('💡 Recommendation: Use --start flag to start the service');
            $this->warn('   Example: php artisan etera-chereta:status --start');
        } else {
            $this->info('✅ Service is running normally');
        }
    }

    /**
     * Start the monitoring service
     */
    private function startService(): void
    {
        $this->info('🚀 Starting Etera-Chereta monitoring service...');
        
        try {
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
            
            // Set cache to indicate service is running
            Cache::put('etera_chereta_service_running', now(), 300);
            
            $this->info('✅ Service started successfully!');
            $this->info('💡 Use "php artisan etera-chereta:status" to check status again');
            
        } catch (\Exception $e) {
            $this->error('❌ Failed to start service: ' . $e->getMessage());
            Log::error('Failed to start Etera-Chereta service: ' . $e->getMessage());
        }
    }
} 