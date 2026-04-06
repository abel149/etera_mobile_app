<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Proforma;
use App\Jobs\AutoSelectProformaOffers;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class CheckEteraCheretaExpiration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'etera-chereta:check-expiration 
                            {--daemon : Run as a daemon process} 
                            {--interval=5 : Check interval in seconds}
                            {--batch-size=1000 : Number of records to process per batch}
                            {--memory-limit=512M : Memory limit for processing}
                            {--max-execution-time=300 : Maximum execution time in seconds}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'High-performance Etera-Chereta expiration check for web servers handling millions of records';

    /**
     * Performance tracking variables
     */
    private $startTime;
    private $processedCount = 0;
    private $totalRecords = 0;
    private $batchSize;
    private $memoryLimit;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->startTime = microtime(true);
        
        // Set performance parameters
        $this->batchSize = (int) $this->option('batch-size');
        $this->memoryLimit = $this->option('memory-limit');
        
        // Set PHP limits for high-performance processing
        $this->setPerformanceLimits();
        
        $this->info('🚀 Starting High-Performance Etera-Chereta Expiration Check Service...');
        $this->info("📊 Batch Size: {$this->batchSize} records");
        $this->info("💾 Memory Limit: {$this->memoryLimit}");
        
        // Check if database is available and initialized
        if (!$this->checkDatabaseAvailability()) {
            $this->error('❌ Database is not available. Please check your database connection.');
            return 1;
        }

        if (!$this->checkDatabaseTables()) {
            $this->info('🔧 Database tables not found. Attempting to create them...');
            if (!$this->createDatabaseTables()) {
                $this->error('❌ Failed to create database tables. Please run migrations manually.');
                return 1;
            }
        }

        $interval = (int) $this->option('interval');
        $isDaemon = $this->option('daemon');

        if ($isDaemon) {
            $this->info("🔄 Running as daemon with {$interval} second intervals...");
            $this->runAsDaemon($interval);
        } else {
            $this->info("⚡ Running single high-performance check...");
            $this->performExpirationCheck();
        }

        return 0;
    }

    /**
     * Set PHP performance limits for high-volume processing
     */
    private function setPerformanceLimits(): void
    {
        // Set memory limit
        ini_set('memory_limit', $this->memoryLimit);
        
        // Set execution time limit
        $maxExecutionTime = (int) $this->option('max-execution-time');
        set_time_limit($maxExecutionTime);
        
        // Optimize database connections
        config(['database.connections.mysql.options' => [
            PDO::ATTR_PERSISTENT => true,
            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => false,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]]);
        
        // Enable query logging for performance monitoring
        if (config('app.debug')) {
            DB::enableQueryLog();
        }
    }

    /**
     * Check if database is available
     */
    private function checkDatabaseAvailability(): bool
    {
        try {
            DB::connection()->getPdo();
            $this->info('✅ Database connection successful');
            return true;
        } catch (\Exception $e) {
            $this->error('❌ Database connection failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if required database tables exist
     */
    private function checkDatabaseTables(): bool
    {
        $requiredTables = ['proformas', 'proforma_applications', 'proforma_part_prices', 'users'];
        
        foreach ($requiredTables as $table) {
            if (!Schema::hasTable($table)) {
                $this->warn("⚠️ Table '{$table}' not found");
                return false;
            }
        }
        
        $this->info('✅ All required database tables found');
        return true;
    }

    /**
     * Create database tables by running migrations
     */
    private function createDatabaseTables(): bool
    {
        try {
            $this->info('🔧 Running database migrations...');
            
            // Run migrations
            $exitCode = \Artisan::call('migrate', ['--force' => true]);
            
            if ($exitCode === 0) {
                $this->info('✅ Database tables created successfully');
                return true;
            } else {
                $this->error('❌ Failed to create database tables');
                return false;
            }
        } catch (\Exception $e) {
            $this->error('❌ Error creating database tables: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Run the command as a daemon process with high-performance optimizations
     */
    private function runAsDaemon(int $interval): void
    {
        $this->info("🔄 Daemon started. Monitoring every {$interval} seconds. Press Ctrl+C to stop.");
        
        $checkCount = 0;
        $totalProcessed = 0;
        
        while (true) {
            try {
                $checkCount++;
                $this->info("\n🔄 [Check #{$checkCount}] " . now()->format('Y-m-d H:i:s'));
                
                // Check if there's any data to monitor
                if ($this->hasDataToMonitor()) {
                    $processed = $this->performExpirationCheck();
                    $totalProcessed += $processed;
                    
                    // Performance monitoring
                    $this->logPerformanceMetrics($checkCount, $totalProcessed);
                } else {
                    $this->info('⏳ No data to monitor. Waiting for data...');
                    
                    // Check if database needs initialization
                    if (!$this->checkDatabaseTables()) {
                        $this->info('🔧 Database structure changed. Recreating tables...');
                        $this->createDatabaseTables();
                    }
                }
                
                // Memory cleanup after each cycle
                $this->cleanupMemory();
                
                // Wait for next interval
                $this->info("⏰ Waiting {$interval} seconds until next check...");
                sleep($interval);
                
            } catch (\Exception $e) {
                $this->error("❌ Error in daemon loop: " . $e->getMessage());
                Log::error("Etera-Chereta daemon error: " . $e->getMessage());
                
                // Wait a bit longer on error before retrying
                sleep($interval * 2);
            }
        }
    }

    /**
     * Check if there's any data to monitor with performance optimizations
     */
    private function hasDataToMonitor(): bool
    {
        try {
            // Use cached counts for performance
            $cacheKey = 'etera_chereta_data_counts';
            $cachedCounts = Cache::remember($cacheKey, 60, function () {
                return [
                    'proformas' => DB::table('proformas')->count(),
                    'applications' => DB::table('proforma_applications')->count(),
                ];
            });
            
            $proformaCount = $cachedCounts['proformas'];
            $applicationCount = $cachedCounts['applications'];
            
            if ($proformaCount > 0 || $applicationCount > 0) {
                $this->info("📊 Found {$proformaCount} proformas and {$applicationCount} applications to monitor");
                $this->totalRecords = $proformaCount + $applicationCount;
                return true;
            }
            
            $this->info('📭 No proformas or applications found in the system');
            return false;
            
        } catch (\Exception $e) {
            $this->warn("⚠️ Error checking for data: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Perform the actual expiration check with high-performance optimizations
     */
    private function performExpirationCheck(): int
    {
        $this->processedCount = 0;
        
        try {
            // Use chunked processing for millions of records
            $this->info('🔍 Searching for active Etera-Chereta proformas...');
            
            // Get total count first
            $totalActive = Proforma::where('required_number_of_shops', 0)
                ->where('status', '!=', 'closed')
                ->where('status', '!=', 'completed')
                ->whereNotNull('timer_expires_at')
                ->count();
            
            if ($totalActive === 0) {
                $this->info('📭 No active Etera-Chereta proformas found');
                return 0;
            }
            
            $this->info("📊 Found {$totalActive} active Etera-Chereta proformas");
            $this->info("⚡ Processing in batches of {$this->batchSize}...");
            
            // Process in chunks to handle millions of records
            Proforma::where('required_number_of_shops', 0)
                ->where('status', '!=', 'closed')
                ->where('status', '!=', 'completed')
                ->whereNotNull('timer_expires_at')
                ->chunk($this->batchSize, function ($proformas) {
                    foreach ($proformas as $proforma) {
                        $this->checkAndProcessProforma($proforma);
                        $this->processedCount++;
                        
                        // Progress indicator
                        if ($this->processedCount % 100 === 0) {
                            $this->info("📈 Processed {$this->processedCount} proformas...");
                        }
                        
                        // Memory management
                        if ($this->processedCount % $this->batchSize === 0) {
                            $this->cleanupMemory();
                        }
                    }
                });
            
            $this->info("✅ Etera-Chereta expiration check completed. Processed {$this->processedCount} proformas");
            
            // Performance summary
            $this->logPerformanceSummary();
            
            return $this->processedCount;

        } catch (\Exception $e) {
            $this->error("❌ Error during expiration check: " . $e->getMessage());
            Log::error("Etera-Chereta expiration check error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Check and process individual proforma with performance optimizations
     */
    private function checkAndProcessProforma(Proforma $proforma)
    {
        try {
            $now = Carbon::now();
            $expiryTime = Carbon::parse($proforma->timer_expires_at);

            // Check if current time >= creation time + requested hours
            if ($now->gte($expiryTime)) {
                $this->info("⏰ Proforma {$proforma->id} has expired, processing auto-selection...");
                
                // Process auto-selection immediately
                $this->processAutoSelection($proforma);
                
                // Update proforma status to closed
                $proforma->update(['status' => 'closed']);
                
                // Clear inbox records in batches
                $this->clearInboxRecords($proforma);
                
                $this->info("✅ Proforma {$proforma->id} closed successfully");
                
                Log::info("Etera-Chereta proforma {$proforma->id} auto-closed and processed");
            } else {
                $remainingTime = $now->diffInSeconds($expiryTime);
                if ($this->processedCount % 100 === 0) { // Only log every 100th to avoid spam
                    $this->info("⏳ Proforma {$proforma->id} still active, remaining time: {$remainingTime} seconds");
                }
            }

        } catch (\Exception $e) {
            $this->error("❌ Error processing proforma {$proforma->id}: " . $e->getMessage());
            Log::error("Error processing Etera-Chereta proforma {$proforma->id}: " . $e->getMessage());
        }
    }

    /**
     * Clear inbox records in batches for performance
     */
    private function clearInboxRecords(Proforma $proforma): void
    {
        try {
            // Delete inbox records in chunks to avoid memory issues
            $proforma->inboxes()->chunk(1000, function ($inboxes) {
                $inboxIds = $inboxes->pluck('id')->toArray();
                DB::table('inboxes')->whereIn('id', $inboxIds)->delete();
            });
        } catch (\Exception $e) {
            $this->warn("⚠️ Warning: Could not clear all inbox records: " . $e->getMessage());
        }
    }

    /**
     * Process auto-selection for expired proforma with performance optimizations
     */
    private function processAutoSelection(Proforma $proforma)
    {
        try {
            // Load applications with eager loading for performance
            $applications = $proforma->applications()
                ->with(['prices' => function ($query) {
                    $query->select('id', 'application_id', 'part_total');
                }, 'applicationBy:id,name,rating'])
                ->get();

            if ($applications->isEmpty()) {
                $this->info("📭 Proforma {$proforma->id} has no applications, skipping auto-selection");
                return;
            }

            $this->info("🔄 Processing auto-selection for proforma {$proforma->id} with {$applications->count()} applications");

            // Calculate total price for each application efficiently
            $scoredApplications = $applications->map(function ($application) {
                $totalPrice = $application->amount ?? 0;
                
                // If application has per-part pricing, calculate total
                if ($application->prices && $application->prices->count() > 0) {
                    $totalPrice = $application->prices->sum('part_total');
                }

                return [
                    'application' => $application,
                    'total_price' => $totalPrice,
                    'score' => $totalPrice
                ];
            });

            // Sort by lowest price (ascending)
            $scoredApplications = $scoredApplications->sortBy('score');

            // Select top 5 applications
            $selectedCount = min(5, $scoredApplications->count());
            $selectedApplications = $scoredApplications->take($selectedCount);

            $this->info("🎯 Selected {$selectedCount} applications for proforma {$proforma->id}");

            // Batch update selected applications for performance
            $selectedIds = $selectedApplications->pluck('application.id')->toArray();
            $notSelectedIds = $applications->whereNotIn('id', $selectedIds)->pluck('id')->toArray();

            // Update selected applications
            if (!empty($selectedIds)) {
                DB::table('proforma_applications')
                    ->whereIn('id', $selectedIds)
                    ->update([
                        'status' => 'selected',
                        'selected_at' => now(),
                        'selection_method' => 'auto_etera_chereta'
                    ]);
            }

            // Update not selected applications
            if (!empty($notSelectedIds)) {
                DB::table('proforma_applications')
                    ->whereIn('id', $notSelectedIds)
                    ->update([
                        'status' => 'not_selected',
                        'selection_method' => 'auto_etera_chereta'
                    ]);
            }

            $this->info("✅ Auto-selection completed for proforma {$proforma->id}");

        } catch (\Exception $e) {
            $this->error("❌ Error during auto-selection for proforma {$proforma->id}: " . $e->getMessage());
            Log::error("Auto-selection error for proforma {$proforma->id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Clean up memory after processing batches
     */
    private function cleanupMemory(): void
    {
        // Clear query log if enabled
        if (config('app.debug')) {
            DB::flushQueryLog();
        }
        
        // Force garbage collection
        if (function_exists('gc_collect_cycles')) {
            gc_collect_cycles();
        }
        
        // Clear any cached data
        Cache::flush();
    }

    /**
     * Log performance metrics
     */
    private function logPerformanceMetrics(int $checkCount, int $totalProcessed): void
    {
        $memoryUsage = memory_get_usage(true);
        $memoryUsageMB = round($memoryUsage / 1024 / 1024, 2);
        $peakMemoryMB = round(memory_get_peak_usage(true) / 1024 / 1024, 2);
        
        $this->info("📊 Performance Metrics - Check #{$checkCount}:");
        $this->info("   💾 Memory Usage: {$memoryUsageMB}MB (Peak: {$peakMemoryMB}MB)");
        $this->info("   📈 Total Processed: {$totalProcessed} records");
        $this->info("   ⚡ Processing Rate: " . round($totalProcessed / max(1, $checkCount), 2) . " records/check");
    }

    /**
     * Log final performance summary
     */
    private function logPerformanceSummary(): void
    {
        $executionTime = round(microtime(true) - $this->startTime, 2);
        $memoryUsageMB = round(memory_get_usage(true) / 1024 / 1024, 2);
        $peakMemoryMB = round(memory_get_peak_usage(true) / 1024 / 1024, 2);
        
        $this->info("🎯 PERFORMANCE SUMMARY:");
        $this->info("   ⏱️  Execution Time: {$executionTime} seconds");
        $this->info("   📊 Records Processed: {$this->processedCount}");
        $this->info("   💾 Final Memory Usage: {$memoryUsageMB}MB (Peak: {$peakMemoryMB}MB)");
        $this->info("   🚀 Processing Speed: " . round($this->processedCount / max(1, $executionTime), 2) . " records/second");
        
        // Log to file for monitoring
        Log::info("Etera-Chereta performance summary", [
            'execution_time' => $executionTime,
            'records_processed' => $this->processedCount,
            'memory_usage_mb' => $memoryUsageMB,
            'peak_memory_mb' => $peakMemoryMB,
            'processing_speed' => round($this->processedCount / max(1, $executionTime), 2)
        ]);
    }
} 