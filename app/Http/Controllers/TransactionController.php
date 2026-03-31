<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\PaidUser;
use App\Models\ProformaInvoice;
use Carbon\Carbon;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $transactions = Transaction::with('user')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($t) {

                $isPaid = null;

                // Commission → PaidUser reference
                if ($t->reference_type === PaidUser::class) {
                    $paidUser = PaidUser::find($t->reference_id);
                    $isPaid = $paidUser?->is_paid;
                } 
                // Proforma → Latest invoice
                elseif ($t->reference_type === \App\Models\Proforma::class) {
                    $invoice = ProformaInvoice::where('proforma_id', $t->reference_id)
                        ->orderByDesc('created_at')
                        ->first();

                    $isPaid = $invoice?->is_paid;
                }

                return [
                    'date' => $t->created_at,
                    'type' => strtolower($t->type), // invoice | commission
                    'user' => $t->user->name ?? 'N/A',
                    'user_role' => $t->user->role ?? null, // 👈 ADDED
                    'amount' => (float) $t->amount,
                    'reference' => $t->description,
                    'balance_after' => (float) $t->balance_after,
                    'is_paid' => $isPaid, // true | false | null
                ];
            });

        return view('admin.transactions.index', [
            'transactions' => $transactions,
        ]);
    }
}
