<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Proforma;
use App\Jobs\AutoSelectProformaOffers;
use Illuminate\Support\Facades\Log;

class CloseExpiredProformas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'proformas:close-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Close expired proformas and trigger auto-selection for Etera-Chereta mode';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to process expired proformas...');

        // Find all expired proformas that are still open
        $expiredProformas = Proforma::where('status', '!=', 'closed')
            ->where('status', '!=', 'completed')
            ->whereNotNull('timer_expires_at')
            ->where('timer_expires_at', '<', now())
            ->get();

        $this->info("Found {$expiredProformas->count()} expired proformas");

        foreach ($expiredProformas as $proforma) {
            $this->processExpiredProforma($proforma);
        }

        $this->info('Finished processing expired proformas');
    }

    /**
     * Process a single expired proforma
     */
    private function processExpiredProforma(Proforma $proforma)
    {
        $this->info("Processing proforma ID: {$proforma->id}");

        try {
            if ($proforma->isEteraCheretaMode()) {
                // For Etera-Chereta mode, trigger auto-selection
                $this->info("Proforma {$proforma->id} is in Etera-Chereta mode, triggering auto-selection");
                
                // Dispatch the auto-selection job immediately
                AutoSelectProformaOffers::dispatch($proforma->id);
                
                Log::info("Auto-selection job dispatched for expired Etera-Chereta proforma {$proforma->id}");
            } else {
                // For regular proformas, just close them
                $this->info("Proforma {$proforma->id} is a regular proforma, closing it");
                
                $proforma->update(['status' => 'closed']);
                
                // Clear any remaining inbox records
                $proforma->inboxes()->delete();
                
                Log::info("Regular proforma {$proforma->id} closed due to expiration");
            }

            $this->info("Successfully processed proforma {$proforma->id}");

        } catch (\Exception $e) {
            $this->error("Error processing proforma {$proforma->id}: " . $e->getMessage());
            Log::error("Error processing expired proforma {$proforma->id}: " . $e->getMessage());
        }
    }
} 