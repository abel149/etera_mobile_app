<?php

namespace App\Services;

use App\Models\BillingStatement;
use App\Models\Cost;
use App\Models\ProformaApplication;
use App\Models\ProformaInvoice;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BillingService
{
    private const VAT_RATE = 0.15;

    // =====================================================================
    // Plan helpers
    // =====================================================================

    /**
     * Get the period boundaries (start, end) for a given billing plan
     * based on now() or an explicit reference date.
     */
    public function getCurrentPeriod(string $plan, Carbon $reference = null): array
    {
        $ref = $reference ?? now();

        if ($plan === 'weekly') {
            return [
                'start' => $ref->copy()->startOfWeek(),  // Monday
                'end'   => $ref->copy()->endOfWeek(),    // Sunday
            ];
        }

        // monthly
        return [
            'start' => $ref->copy()->startOfMonth(),
            'end'   => $ref->copy()->endOfMonth(),
        ];
    }

    /**
     * Get the previous completed period (what we should bill).
     */
    public function getPreviousPeriod(string $plan, Carbon $reference = null): array
    {
        $ref = $reference ?? now();

        if ($plan === 'weekly') {
            $prev = $ref->copy()->subWeek();
            return [
                'start' => $prev->copy()->startOfWeek(),
                'end'   => $prev->copy()->endOfWeek(),
            ];
        }

        $prev = $ref->copy()->subMonth();
        return [
            'start' => $prev->copy()->startOfMonth(),
            'end'   => $prev->copy()->endOfMonth(),
        ];
    }

    // =====================================================================
    // Statement generation
    // =====================================================================

    /**
     * Generate a billing statement for one owner for a specific period.
     * Covers the owner AND all their employees (registered_by = owner_id).
     *
     * Returns the created BillingStatement or null if nothing to bill.
     */
    public function generateStatement(User $owner, string $plan, Carbon $periodStart, Carbon $periodEnd): ?BillingStatement
    {
        if (in_array($owner->billing_plan, ['per_invoice'])) {
            return null; // per-invoice owners don't get statements
        }

        // Avoid duplicate statements for the same period
        $existing = BillingStatement::where('owner_id', $owner->id)
            ->where('period_start', $periodStart->toDateString())
            ->where('period_end', $periodEnd->toDateString())
            ->first();

        if ($existing) {
            Log::info("BillingService: statement already exists for owner {$owner->id} period {$periodStart->toDateString()}");
            return $existing;
        }

        // Collect all poster_ids: owner + their employees
        $accountIds = User::where(function ($q) use ($owner) {
            $q->where('id', $owner->id)
              ->orWhere('registered_by', $owner->id);
        })->pluck('id');

        // Fetch all proforma invoices in this period for this account
        $invoices = ProformaInvoice::whereHas('proforma', function ($q) use ($accountIds, $periodStart, $periodEnd) {
            $q->whereIn('poster_id', $accountIds)
              ->whereBetween('created_at', [
                  $periodStart->copy()->startOfDay(),
                  $periodEnd->copy()->endOfDay(),
              ]);
        })->get();

        if ($invoices->isEmpty()) {
            return null; // nothing to bill
        }

        $subtotal   = $invoices->sum(fn($inv) => (float) $inv->total_amount / (1 + self::VAT_RATE));
        $vatAmount  = $invoices->sum(fn($inv) => (float) $inv->total_amount) - $subtotal;
        $totalAmount = $subtotal + $vatAmount;

        DB::beginTransaction();
        try {
            $statement = BillingStatement::create([
                'owner_id'       => $owner->id,
                'period_type'    => $plan,
                'period_start'   => $periodStart->toDateString(),
                'period_end'     => $periodEnd->toDateString(),
                'proforma_count' => $invoices->count(),
                'subtotal'       => round($subtotal, 2),
                'vat_amount'     => round($vatAmount, 2),
                'total_amount'   => round($totalAmount, 2),
                'status'         => 'pending',
            ]);

            DB::commit();
            Log::info("BillingService: statement {$statement->sku} created for owner {$owner->id}");
            return $statement;

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("BillingService: failed to create statement for owner {$owner->id}", ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Run at period end — generate statements for all eligible owners.
     * Call this from the scheduled command.
     */
    public function generateAllDue(string $plan): int
    {
        $period = $this->getPreviousPeriod($plan);
        $generated = 0;

        $owners = User::where('billing_plan', $plan)
            ->whereIn('role', ['garage', 'business_owner', 'others'])
            ->where('approved', true)
            ->get();

        foreach ($owners as $owner) {
            $stmt = $this->generateStatement($owner, $plan, $period['start'], $period['end']);
            if ($stmt) {
                $generated++;
            }
        }

        Log::info("BillingService: generated {$generated} {$plan} statements");
        return $generated;
    }

    // =====================================================================
    // Current period summary (for the Flutter dashboard)
    // =====================================================================

    /**
     * Summarise usage in the current open period (not yet billed).
     */
    public function currentPeriodSummary(User $owner): array
    {
        if ($owner->billing_plan === 'per_invoice') {
            return ['billing_plan' => 'per_invoice'];
        }

        $period     = $this->getCurrentPeriod($owner->billing_plan);
        $accountIds = User::where(function ($q) use ($owner) {
            $q->where('id', $owner->id)->orWhere('registered_by', $owner->id);
        })->pluck('id');

        $invoices = ProformaInvoice::whereHas('proforma', function ($q) use ($accountIds, $period) {
            $q->whereIn('poster_id', $accountIds)
              ->whereBetween('created_at', [
                  $period['start']->copy()->startOfDay(),
                  $period['end']->copy()->endOfDay(),
              ]);
        })->get();

        $subtotal    = $invoices->sum(fn($inv) => (float) $inv->total_amount / (1 + self::VAT_RATE));
        $vatAmount   = $invoices->sum(fn($inv) => (float) $inv->total_amount) - $subtotal;
        $totalAmount = $subtotal + $vatAmount;

        return [
            'billing_plan'    => $owner->billing_plan,
            'period_start'    => $period['start']->toDateString(),
            'period_end'      => $period['end']->toDateString(),
            'proforma_count'  => $invoices->count(),
            'subtotal'        => round($subtotal, 2),
            'vat_amount'      => round($vatAmount, 2),
            'total_amount'    => round($totalAmount, 2),
        ];
    }
}
