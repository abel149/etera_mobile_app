<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Proforma;
use App\Services\ProformaClosingService;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CheckProformaClosing extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'proforma:check-closing';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and close proformas based on Etera-Chereta timer and application requirements';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting proforma closing check...');
        
        try {
            $closingService = new ProformaClosingService();
            $processedCount = 0;
            $closedCount = 0;
            $errorCount = 0;

            // Get all pending/opened proformas
            $proformas = Proforma::whereIn('status', ['pending', 'opened'])
                ->with(['applications', 'poster'])
                ->get();

            $this->info("Found {$proformas->count()} proformas to check");

            foreach ($proformas as $proforma) {
                try {
                    $processedCount++;
                    
                    // Check if proforma should be auto-closed
                    if ($closingService->shouldAutoClose($proforma)) {
                        $this->info("Processing proforma {$proforma->id} for auto-closing");
                        
                        if ($proforma->isEteraCheretaMode()) {
                            // Handle Etera-Chereta expiration
                            $result = $closingService->handleExpiredProforma($proforma);
                        } else {
                            // Handle regular proforma closing
                            $result = $closingService->closeProforma($proforma);
                        }
                        
                        if ($result['success']) {
                            $closedCount++;
                            $this->info("✓ Proforma {$proforma->id} processed successfully");
                        } else {
                            $errorCount++;
                            $this->error("✗ Error processing proforma {$proforma->id}: {$result['message']}");
                        }
                    } else {
                        // Log status for monitoring
                        $statusSummary = $closingService->getStatusSummary($proforma);
                        Log::info("Proforma {$proforma->id} status check", $statusSummary);
                    }

                } catch (\Exception $e) {
                    $errorCount++;
                    $this->error("✗ Exception processing proforma {$proforma->id}: " . $e->getMessage());
                    Log::error("Exception in proforma closing check for proforma {$proforma->id}", [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }

            // Clean up expired temporary files
            $this->cleanupTemporaryFiles();

            $this->info("Proforma closing check completed:");
            $this->info("- Processed: {$processedCount}");
            $this->info("- Closed: {$closedCount}");
            $this->info("- Errors: {$errorCount}");

            Log::info("Proforma closing check completed", [
                'processed' => $processedCount,
                'closed' => $closedCount,
                'errors' => $errorCount,
                'timestamp' => now()
            ]);

        } catch (\Exception $e) {
            $this->error("Fatal error in proforma closing check: " . $e->getMessage());
            Log::error("Fatal error in proforma closing check", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Clean up expired temporary files
     */
    private function cleanupTemporaryFiles()
    {
        try {
            $tempService = new \App\Services\TemporaryFileService();
            $tempService->cleanupExpiredFiles(24); // Clean files older than 24 hours
            $this->info("Temporary files cleanup completed");
        } catch (\Exception $e) {
            $this->error("Error cleaning up temporary files: " . $e->getMessage());
            Log::error("Error cleaning up temporary files: " . $e->getMessage());
        }
    }
}
