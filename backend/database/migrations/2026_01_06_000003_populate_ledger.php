<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\PaidUser;
use App\Models\ProformaInvoice;
use App\Services\WalletService;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $walletService = new WalletService();

        // 1. Migrate PaidUser (Commissions/Payouts)
        // We need to process them in chronological order.
        // PaidUser represents "Money In" (Commission earned) AND "Money Out" (Payout).
        // Actually, PaidUser record creation = Commission Earned (Liability).
        // PaidUser marked as paid = Payout (Liability Settled).
        
        // Strategy:
        // 1. Iterate all PaidUsers.
        // 2. Create "Commission Earned" transaction for each (Credit).
        // 3. If is_paid, create "Payout" transaction (Debit) at paid_at time.
        
        // 2. Migrate ProformaInvoice (Charges)
        // ProformaInvoice = Money Out (Charge for service).
        // Create "Invoice" transaction (Debit).
        
        // We need to merge all these events and process them chronologically per user to get accurate running balance.
        
        $users = User::all();
        
        foreach ($users as $user) {
            $events = collect();
            
            // A. Commissions (PaidUser created)
            $commissions = PaidUser::where('user_id', $user->id)->get();
            foreach ($commissions as $comm) {
                $events->push([
                    'date' => $comm->created_at,
                    'type' => 'commission',
                    'amount' => $comm->amount, // Positive
                    'description' => 'Commission for Proforma #' . $comm->proforma_id,
                    'reference' => $comm,
                ]);
                
                if ($comm->is_paid) {
                    $events->push([
                        'date' => $comm->paid_at ?? $comm->updated_at,
                        'type' => 'payout',
                        'amount' => -$comm->amount, // Negative (Debit)
                        'description' => 'Payout',
                        'reference' => $comm,
                    ]);
                }
            }
            
            // B. Invoices (ProformaInvoice created)
            // Only if user is the poster (Insurance/Garage)
            // Wait, ProformaInvoice `created_by` is the user who created it?
            // Or `proforma->poster`?
            // TransactionController says: 
            // $proformas = ProformaInvoice::with(['createdBy', 'proforma.poster'])...
            // And assigns it to `createdBy`.
            // Let's assume `created_by` is the payer.
            
            $invoices = ProformaInvoice::where('created_by', $user->id)->get();
            foreach ($invoices as $inv) {
                $events->push([
                    'date' => $inv->created_at,
                    'type' => 'invoice',
                    'amount' => -$inv->total_amount, // Negative (Debit)
                    'description' => 'Invoice for Proforma #' . $inv->proforma_id,
                    'reference' => $inv,
                ]);
            }
            
            // Sort by date
            $sortedEvents = $events->sortBy('date');
            
            // Process
            foreach ($sortedEvents as $event) {
                $walletService->processTransaction(
                    $user,
                    $event['amount'],
                    $event['type'],
                    $event['description'],
                    $event['reference']
                );
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('transactions')->truncate();
        DB::table('users')->update(['wallet_balance' => 0]);
    }
};
