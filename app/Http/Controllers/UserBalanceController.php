<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\PaidUser;
use App\Models\ProformaInvoice;
use App\Models\Proforma;

class UserBalanceController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $currentYear = date('Y');

        $transactions = collect();

        // 1️⃣ Commissions from Etera (Incoming +)
        $paidUsers = PaidUser::where('user_id', $user->id)
            ->whereYear('created_at', $currentYear)
            ->get();

        foreach ($paidUsers as $p) {
            $transactions->push([
                'date'    => $p->created_at,
                'type'    => 'commission',
                'reason'  => 'Commission',
                'amount'  => abs((float) $p->amount), // always positive
                'is_paid' => $p->is_paid,
                'flow'    => 'in',
            ]);
        }

        // 2️⃣ Outgoing: Invoices (Outgoing -)
        if (in_array($user->role, ['garage', 'insurance'])) {
            if ($user->role === 'garage') {
                $insuredProformas = Proforma::where('insured', 0)
                    ->where('poster_id', $user->id)
                    ->get();

                $invoices = collect();
                foreach ($insuredProformas as $proforma) {
                    $latestInvoice = ProformaInvoice::where('proforma_id', $proforma->id)
                        ->orderByDesc('created_at')
                        ->first();
                    if ($latestInvoice) {
                        $invoices->push($latestInvoice);
                    }
                }
            } else {
                $insuredProformas = Proforma::where('insured', 1)
                    ->where('poster_id', $user->id)
                    ->get();

                $invoices = collect();
                foreach ($insuredProformas as $proforma) {
                    $latestInvoice = ProformaInvoice::where('proforma_id', $proforma->id)
                        ->orderByDesc('created_at')
                        ->first();
                    if ($latestInvoice) {
                        $invoices->push($latestInvoice);
                    }
                }
            }

            foreach ($invoices as $inv) {
                $transactions->push([
                    'date'    => $inv->created_at,
                    'type'    => 'invoice',
                    'reason'  => $user->role === 'insurance' ? 'Insured Proforma Invoice' : 'Invoice to Etera',
                    'amount'  => -abs((float) $inv->total_amount),
                    'is_paid' => $inv->is_paid,
                    'flow'    => 'out',
                ]);
            }
        }

        // Sort transactions by date
        $transactions = $transactions->sortByDesc('date')->values();

        // SUMMARY
        $summary = [
            'pending_from_etera'       => $transactions->where('flow','in')->where('is_paid', false)->sum('amount'),
            'paid_from_etera'          => $transactions->where('flow','in')->where('is_paid', true)->sum('amount'),
            'total_earned_from_etera'  => $transactions->where('flow','in')->sum('amount'),

            'pending_to_etera'         => abs($transactions->where('flow','out')->where('is_paid', false)->sum('amount')),
            'paid_to_etera'            => abs($transactions->where('flow','out')->where('is_paid', true)->sum('amount')),
            'total_paid_to_etera'      => abs($transactions->where('flow','out')->sum('amount')),

            'wallet_balance'           => $user->wallet_balance,
        ];

        if (in_array($user->role, ['shop', 'operator'])) {
            $summary['pending_to_etera'] = 0;
            $summary['paid_to_etera'] = 0;
            $summary['total_paid_to_etera'] = 0;
        }

        // Convert transactions for JS (done in controller, not Blade)
        $transactionsArray = $transactions->map(function($t) use ($user) {
            return [
                'date' => $t['date']->format('Y-m-d H:i:s'),
                'type' => $t['type'],
                'reference' => $t['reason'],
                'user' => $user->name ?? '',
                'amount' => $t['amount'],
                'is_paid' => $t['is_paid'],
            ];
        })->toArray();

        // Role-based view
        if($user->role === 'insurance'){
            return view('insurance.balance', compact('user', 'transactionsArray', 'summary'));
        } elseif($user->role === 'operator'){
            return view('operator.balance', compact('user', 'transactionsArray', 'summary'));
        } else {
            return view('spare-part.balance', compact('user', 'transactionsArray', 'summary'));
        }
    }
}
