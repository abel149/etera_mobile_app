<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\PaidUser;
use App\Models\ProformaInvoice;
use App\Models\Proforma;
use App\Models\User;

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
        if (in_array($user->role, ['garage', 'insurance', 'insurance_agent'])) {
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

        // Aggregated agent balances for parent insurance users only
        $agents = collect();
        $companyTotals = [
            'pending_to_etera' => (float) ($summary['pending_to_etera'] ?? 0),
            'paid_to_etera'    => (float) ($summary['paid_to_etera'] ?? 0),
        ];

        if ($user->role === 'insurance') {
            $agentUsers = \App\Models\User::where('parent_insurance_id', $user->id)
                ->where('role', 'insurance_agent')
                ->orderBy('name')
                ->get();

            $agents = $agentUsers->map(function ($agent) {
                $agentSummary = $this->computeInsuranceToEteraTotals($agent);

                return [
                    'id'               => $agent->id,
                    'name'             => $agent->name,
                    'phone_number'     => $agent->phone_number,
                    'pending_to_etera' => $agentSummary['pending_to_etera'],
                    'paid_to_etera'    => $agentSummary['paid_to_etera'],
                ];
            });

            $companyTotals['pending_to_etera'] += (float) $agents->sum('pending_to_etera');
            $companyTotals['paid_to_etera']    += (float) $agents->sum('paid_to_etera');
        }

        // Role-based view
        if(in_array($user->role, ['insurance', 'insurance_agent'])){
            return view('insurance.balance', compact('user', 'transactionsArray', 'summary', 'agents', 'companyTotals'));
        } elseif($user->role === 'operator'){
            return view('operator.balance', compact('user', 'transactionsArray', 'summary'));
        } else {
            return view('spare-part.balance', compact('user', 'transactionsArray', 'summary'));
        }
    }

    /**
     * Compute the pending and paid "to Etera" invoice totals for a single
     * insurance/insurance_agent user, based on their insured proformas.
     */
    private function computeInsuranceToEteraTotals(User $user): array
    {
        $pending = 0.0;
        $paid = 0.0;

        $insuredProformas = Proforma::where('insured', 1)
            ->where('poster_id', $user->id)
            ->get();

        foreach ($insuredProformas as $proforma) {
            $latestInvoice = ProformaInvoice::where('proforma_id', $proforma->id)
                ->orderByDesc('created_at')
                ->first();

            if (!$latestInvoice) {
                continue;
            }

            $amount = abs((float) $latestInvoice->total_amount);

            if ($latestInvoice->is_paid) {
                $paid += $amount;
            } else {
                $pending += $amount;
            }
        }

        return [
            'pending_to_etera' => $pending,
            'paid_to_etera'    => $paid,
        ];
    }
}
