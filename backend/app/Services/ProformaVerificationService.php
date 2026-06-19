<?php

namespace App\Services;

use App\Models\{
    Proforma,
    Cost,
    Commission,
    ProformaApplication,
    ProformaInvoice,
    ProformaSelection,
    PaidUser,
    User
};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ProformaVerificationService
{
    public function verify(Proforma $proforma)
    {
        Log::info('Verification started', ['proforma_id' => $proforma->id]);

        $vatRate = 0.15;
        $rows = [];

        // Fetch latest costs
        $latestCost = Cost::latest()->first();
        if (!$latestCost) {
            Log::error('No cost data available');
            throw new \Exception('No cost data available');
        }
        Log::info('Latest cost fetched', ['cost_id' => $latestCost->id]);

        // Determine proforma type
        $requiredShops   = (int) ($proforma->required_number_of_shops ?? 0);
        $requiredGarages = (int) ($proforma->required_number_of_garages ?? 0);

        // Explicit insurance subtypes (set via proforma_type column) always use insurance billing
        if ($proforma->proforma_type && str_starts_with($proforma->proforma_type, 'insurance_')) {
            $type = 'insurance';
        } elseif ($requiredShops > 0 && $requiredGarages == 0) {
            $type = 'regular';
        } elseif ($requiredShops == 3 && $requiredGarages == 3) {
            $type = 'insurance';
        } elseif (($requiredShops + $requiredGarages) == 0) {
            $type = 'etera_chereta';
        } else {
            $type = 'unknown';
        }

        Log::info('Proforma type determined', ['type' => $type]);

        // Load commission rates
        $commissions = Commission::first();
        $shopPay = $commissions->shopPay ?? 0;
        $garagePay = $commissions->garagePay ?? 0;
        $operatorPay = $commissions->operatorPay ?? 0;
        $othersPay = $commissions->othersPay ?? 0;

        Log::info('Commission rates loaded', [
            'shopPay' => $shopPay,
            'garagePay' => $garagePay,
            'operatorPay' => $operatorPay,
            'othersPay' => $othersPay
        ]);

        // Fetch applications
        $applications = ProformaApplication::where('proforma_id', $proforma->id)->get();
        $count = $applications->count();
        Log::info('Proforma applications fetched', ['count' => $count]);

        // -------------------
        // REGULAR PROFORMA
        // -------------------
        if ($type === 'regular') {
            $field = "{$count}_proforma_cost";
            $total = (float) ($latestCost->$field ?? 0);
            $base = $total / (1 + $vatRate);
            $vat = $total - $base;

            $rows[] = [
                'proforma_id'     => $proforma->id,
                'type'            => 'regular',
                'requested_count' => $count,
                'unit_price'      => $base,
                'vat_rate'        => 15,
                'vat_amount'      => $vat,
                'total_amount'    => $total,
                'created_by'      => Auth::id(),
                'created_at'      => now(),
                'updated_at'      => now(),
            ];

            Log::info('Regular proforma invoice row created', ['total' => $total]);
        }

        // -------------------
        // INSURANCE PROFORMA
        // -------------------
        elseif ($type === 'insurance') {
            $total = (float) ($proforma->insured ? 0 : ($latestCost->insurance_proforma ?? 0));
            $base = $total / (1 + $vatRate);
            $vat = $total - $base;

            $rows[] = [
                'proforma_id'     => $proforma->id,
                'type'            => 'insurance',
                'requested_count' => ($requiredShops + $requiredGarages) ?: 6,
                'unit_price'      => $base,
                'vat_rate'        => 15,
                'vat_amount'      => $vat,
                'total_amount'    => $total,
                'is_paid'         => !$proforma->insured,
                'created_by'      => Auth::id(),
                'created_at'      => now(),
                'updated_at'      => now(),
            ];

            Log::info('Insurance proforma invoice row created', ['total' => $total]);

            // Commission for shops/garages (POSITIVE - they earned money)
            foreach ($applications as $app) {
                $user = $app->applicationBy;
                if (!$user) continue;

                $role = $user->role ?? 'unknown';
                $amount = 0;

                if ($role === 'garage') $amount = $garagePay;
                if ($role === 'shop') $amount = $shopPay;

                if ($amount > 0) {
                    $this->createCommissionRecord($user, $amount, $proforma, $app, ucfirst($role) . ' commission');
                }
            }
        }

        // -------------------
        // ETERA CHERETA PROFORMA
        // -------------------
        elseif ($type === 'etera_chereta') {
            $total = (float) ($latestCost->etera_chereta_cost ?? 0);
            $base = $total / (1 + $vatRate);
            $vat = $total - $base;

            $rows[] = [
                'proforma_id'  => $proforma->id,
                'type'         => 'etera_chereta',
                'hourly_price' => $base,
                'hours'        => 1,
                'vat_rate'     => 15,
                'vat_amount'   => $vat,
                'total_amount' => $total,
                'created_by'   => Auth::id(),
                'created_at'   => now(),
                'updated_at'   => now(),
            ];

            Log::info('Etera Chereta invoice row created', ['total' => $total]);
        }

        // -------------------
        // OTHERS/BUSINESS-OWNER COMMISSION (ALL TYPES EXCEPT INSURANCE)
        // -------------------
        if ($type !== 'insurance' && $proforma->poster && in_array($proforma->poster->role, ['others', 'garage']) && $othersPay > 0) {
            $this->createCommissionRecord($proforma->poster, $othersPay, $proforma, null, 'Others commission');
        }
        // -------------------
        // OPERATOR COMMISSION (POSITIVE - they earned money)
        // -------------------
        $selection = ProformaSelection::where('proforma_id', $proforma->id)
            ->where('active', true)
            ->first();

        if ($selection && $selection->employee_id) {
            $operator = User::find($selection->employee_id);
            if ($operator && $operatorPay > 0) {
                // Commission is POSITIVE (user earned money)
                $this->createCommissionRecord($operator, $operatorPay, $proforma, null, 'Operator commission');
                $selection->update(['commission_earned' => $operatorPay]);
            } else {
                Log::warning('Operator user not found or commission zero', [
                    'employee_id' => $selection->employee_id,
                    'operatorPay' => $operatorPay
                ]);
            }
        } else {
            Log::info('No active operator assigned for this proforma', ['proforma_id' => $proforma->id]);
        }

        // -------------------
        // SAVE INVOICE ROWS
        // -------------------
        ProformaInvoice::where('proforma_id', $proforma->id)->delete();
        $savedInvoices = [];
        foreach ($rows as $row) {
            $savedInvoices[] = ProformaInvoice::create($row);
        }
        Log::info('Proforma invoice saved', ['rows' => count($savedInvoices)]);

        // -------------------
        // SEND INVOICE LINK EMAIL
        // -------------------
        if (!empty($savedInvoices)) {
            $invoice = $savedInvoices[0];
            $recipientEmail = $proforma->customer_email ?? $proforma->poster?->email;

            if ($recipientEmail && \App\Models\EmailSetting::isEnabled('proforma_completed')) {
                try {
                    $invoiceUrl = url("/transaction/{$invoice->sku}");

                    \Illuminate\Support\Facades\Mail::raw(
                        "etera – Your Proforma is Complete!\n\n" .
                        "Dear Customer,\n\n" .
                        "Your proforma #{$proforma->file_number} has been completed successfully.\n\n" .
                        "Your invoice (SKU: {$invoice->sku}) is now available.\n" .
                        "View your full invoice here: {$invoiceUrl}\n\n" .
                        "Thank you for using etera!",
                        function ($message) use ($proforma, $recipientEmail, $invoice) {
                            $message->to($recipientEmail)
                                    ->subject("etera – Invoice for Proforma #{$proforma->file_number} (SKU: {$invoice->sku})");
                        }
                    );

                    \App\Models\SentEmail::log(
                        'proforma_completed',
                        $recipientEmail,
                        $proforma->customer_name,
                        $proforma->poster?->id,
                        $proforma->id,
                        "ETERA – Invoice for Proforma #{$proforma->file_number}",
                        'sent'
                    );
                } catch (\Throwable $emailEx) {
                    Log::warning('Failed to send invoice link email', [
                        'proforma_id' => $proforma->id,
                        'error' => $emailEx->getMessage(),
                    ]);
                    try {
                        \App\Models\SentEmail::log(
                            'proforma_completed',
                            $recipientEmail,
                            $proforma->customer_name,
                            $proforma->poster?->id,
                            $proforma->id,
                            "ETERA – Invoice for Proforma #{$proforma->file_number}",
                            'failed',
                            $emailEx->getMessage()
                        );
                    } catch (\Throwable $logEx) {}
                }
            }
        }

        // -------------------
        // WALLET TRANSACTION FOR POSTER (Customer pays - DEBIT for them)
        // -------------------
        $invoiceTotal = collect($rows)->sum('total_amount');
        if ($invoiceTotal > 0 && $proforma->poster) {
            // Customer is charged (DEBIT - negative)
            app(WalletService::class)->processTransaction(
                $proforma->poster,
                $invoiceTotal,
                'invoice',
                'Invoice for Proforma #' . $proforma->id,
                $proforma
            );
            Log::info('Invoice wallet transaction processed for poster', [
                'poster_id' => $proforma->poster->id,
                'total' => $invoiceTotal
            ]);

            // MIRROR: Admin receives revenue (CREDIT - positive)
          
        }

        $proforma->update(['status' => 'completed']);
        $proforma->verify();

        // Notify the poster that results + billing are ready on mobile
        try {
            if ($proforma->poster) {
                $proforma->poster->notify(
                    new \App\Notifications\ProformaResultsReadyNotification($proforma)
                );
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to send results-ready notification to poster', [
                'proforma_id' => $proforma->id,
                'error'       => $e->getMessage(),
            ]);
        }

        Log::info('Verification completed', ['proforma_id' => $proforma->id]);
    }

    /**
     * Create commission record (PaidUser) and wallet transaction
     * 
     * Commission Flow:
     * 1. PaidUser record tracks if commission is paid out or not
     * 2. User gets POSITIVE transaction (credit - they earned money)
     * 3. Admin gets commission_expense recorded (for tracking purposes)
     */
    private function createCommissionRecord(User $user, float $amount, Proforma $proforma, ?ProformaApplication $app, string $description)
    {
        try {
            // 1. Create PaidUser record (tracks payment status)
            $paid = PaidUser::create([
                'user_id' => $user->id,
                'proforma_id' => $proforma->id,
                'application_id' => $app->id ?? null,
                'amount' => $amount,  // Always positive
                'is_paid' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Log::info("PaidUser created for {$description}", [
                'user_id' => $user->id,
                'amount' => $amount,
                'paiduser_id' => $paid->id
            ]);

            // 2. User Transaction: Commission is CREDIT (positive - they earned money)
            app(WalletService::class)->processTransaction(
                $user,
                -$amount,  // POSITIVE - money they earned
                'commission',
                "{$description} for Proforma #{$proforma->id}",
                $paid
            );

            Log::info("Commission (credit) transaction for user", [
                'user_id' => $user->id,
                'amount' => $amount
            ]);

            // 3. Admin MIRROR: Record as future liability (commission to be paid)
           
        } catch (\Exception $e) {
            Log::error("Failed to process {$description}", [
                'user_id' => $user->id,
                'amount' => $amount,
                'error' => $e->getMessage()
            ]);
        }
    }
}

