<?php

namespace App\Console\Commands;

use App\Services\BillingService;
use Illuminate\Console\Command;

class GenerateBillingStatements extends Command
{
    protected $signature   = 'billing:generate {plan : monthly or weekly}';
    protected $description = 'Generate billing statements for owners on the given plan (monthly|weekly)';

    public function handle(BillingService $billing): int
    {
        $plan = $this->argument('plan');

        if (!in_array($plan, ['monthly', 'weekly'])) {
            $this->error('Plan must be "monthly" or "weekly".');
            return self::FAILURE;
        }

        $this->info("Generating {$plan} billing statements...");
        $count = $billing->generateAllDue($plan);
        $this->info("Done. {$count} statement(s) created.");

        return self::SUCCESS;
    }
}
