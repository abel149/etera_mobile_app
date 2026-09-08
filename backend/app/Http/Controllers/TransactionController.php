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
        $query = Transaction::with('user')
            ->orderBy('created_at', 'desc');

        // Server-side date filtering
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', Carbon::parse($request->from)->startOfDay());
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', Carbon::parse($request->to)->endOfDay());
        }

        // Server-side name / phone search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        $transactionModels = $query->get();

        // Batch-load PaidUser statuses to avoid N+1
        $paidUserIds = $transactionModels
            ->where('reference_type', PaidUser::class)
            ->pluck('reference_id');

        $paidUsersMap = $paidUserIds->isNotEmpty()
            ? PaidUser::whereIn('id', $paidUserIds)->pluck('is_paid', 'id')
            : collect();

        // Batch-load latest ProformaInvoice statuses to avoid N+1
        $proformaIds = $transactionModels
            ->where('reference_type', \App\Models\Proforma::class)
            ->pluck('reference_id');

        $invoicesMap = $proformaIds->isNotEmpty()
            ? ProformaInvoice::whereIn('proforma_id', $proformaIds)
                ->orderByDesc('created_at')
                ->get()
                ->unique('proforma_id')
                ->pluck('is_paid', 'proforma_id')
            : collect();

        $transactions = $transactionModels->map(function ($t) use ($paidUsersMap, $invoicesMap) {
            $isPaid = null;

            if ($t->reference_type === PaidUser::class) {
                $isPaid = $paidUsersMap->get($t->reference_id);
            } elseif ($t->reference_type === \App\Models\Proforma::class) {
                $isPaid = $invoicesMap->get($t->reference_id);
            }

            return [
                'date'          => $t->created_at,
                'type'          => strtolower($t->type),
                'user'          => $t->user->name ?? 'N/A',
                'user_phone'    => $t->user->phone_number ?? null,
                'user_role'     => $t->user->role ?? null,
                'amount'        => (float) $t->amount,
                'reference'     => $t->description,
                'balance_after' => (float) $t->balance_after,
                'is_paid'       => $isPaid,
            ];
        });

        return view('admin.transactions.index', [
            'transactions' => $transactions,
            'filters'      => [
                'from'   => $request->from ?? '',
                'to'     => $request->to ?? '',
                'search' => $request->search ?? '',
            ],
        ]);
    }
}
