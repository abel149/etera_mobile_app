<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaidUser;
use App\Models\Proforma;
use App\Models\ProformaInvoice;
use App\Models\WithdrawalRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserBalanceController extends Controller
{
    /**
     * GET /api/v1/garage/balance
     * GET /api/v1/shop/balance
     *
     * Returns balance summary + transaction history + pending withdrawals.
     * Format mirrors InsuranceController::balance() for consistency.
     */
    public function index()
    {
        $user    = Auth::user();
        $ownerId = $user->registered_by ?? $user->id;
        $owner   = \App\Models\User::find($ownerId);

        // ── Incoming: commissions paid by Etera ──────────────────────
        $commissions = PaidUser::where('user_id', $ownerId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($p) => [
                'date'      => $p->created_at->toIso8601String(),
                'type'      => 'commission',
                'reference' => 'Commission from Etera',
                'amount'    => abs((float) $p->amount),
                'is_paid'   => (bool) $p->is_paid,
                'flow'      => 'in',
            ]);

        // ── Outgoing: invoices charged to garage (not shop) ──────────
        $invoices = collect();
        if ($user->role === 'garage') {
            $proformaIds = Proforma::where('poster_id', $ownerId)->pluck('id');
            $invoices = ProformaInvoice::whereIn('proforma_id', $proformaIds)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(fn ($inv) => [
                    'date'      => $inv->created_at->toIso8601String(),
                    'type'      => 'invoice',
                    'reference' => 'Invoice to Etera',
                    'amount'    => abs((float) $inv->total_amount),
                    'is_paid'   => (bool) $inv->is_paid,
                    'flow'      => 'out',
                ]);
        }

        $transactions = $commissions->merge($invoices)->sortByDesc('date')->values();

        // ── Pending withdrawal requests ───────────────────────────────
        $withdrawals = WithdrawalRequest::where('from', $ownerId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($w) => [
                'id'             => $w->id,
                'amount'         => (float) $w->amount,
                'bank_name'      => $w->bank_name,
                'account_number' => $w->account_number,
                'status'         => $w->status,
                'created_at'     => $w->created_at->toIso8601String(),
            ]);

        return response()->json([
            'success' => true,
            'data'    => [
                'balance'             => (float) ($owner->balance ?? 0),
                'summary'             => [
                    'pending_from_etera'      => $commissions->where('is_paid', false)->sum('amount'),
                    'paid_from_etera'         => $commissions->where('is_paid', true)->sum('amount'),
                    'total_earned_from_etera' => $commissions->sum('amount'),
                    'pending_to_etera'        => $invoices->where('is_paid', false)->sum('amount'),
                    'paid_to_etera'           => $invoices->where('is_paid', true)->sum('amount'),
                    'total_paid_to_etera'     => $invoices->sum('amount'),
                ],
                'transactions'        => $transactions,
                'withdrawal_requests' => $withdrawals,
            ],
        ]);
    }
}
