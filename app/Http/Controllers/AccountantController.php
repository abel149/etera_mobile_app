<?php

namespace App\Http\Controllers;

use App\Models\PaidUser;
use App\Models\Proforma;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AccountantController extends Controller
{
    public function dashboard(Request $request)
    {
        // ---------------------------------------------
        // 🔍 Date Filter Handling
        // ---------------------------------------------
        $filter = $request->get('filter', '1-month');

        $startDate = match ($filter) {
            '1-week'   => now()->subWeek(),
            '2-weeks'  => now()->subWeeks(2),
            '1-month'  => now()->subMonth(),
            '3-months' => now()->subMonths(3),
            '6-months' => now()->subMonths(6),
            default    => now()->subMonth(),
        };

        $endDate = now();

        // ---------------------------------------------
        // 🧾 Load all PaidUser records with user + proforma
        // ---------------------------------------------
        $paidUsers = PaidUser::with(['user', 'proforma'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        // ---------------------------------------------
        // 🏭 Garages & Shops Summary
        // ---------------------------------------------
        $garageShopUsers = $paidUsers
            ->filter(fn($p) =>
                in_array(optional($p->user)->role, ['garage', 'shop'])
            )
            ->groupBy('user_id')
            ->map(function ($group) {
                $user = $group->first()->user;

                return (object)[
                    'user' => $user,
                    'role' => $user->role,
                    'filled_applications' => $group->unique('application_id')->count(),
                    'total_earned' => $group->sum('amount'),
                    'total_paid' => $group->where('is_paid', true)->sum('amount'),
                    'remaining' => $group->where('is_paid', false)->sum('amount'),
                ];
            })
            ->values();

        // ---------------------------------------------
        // 🏥 Insurance Summary
        // ---------------------------------------------
        $insuranceUsers = $paidUsers
            ->filter(fn($p) => optional($p->user)->role === 'insurance')
            ->groupBy('user_id')
            ->map(function ($group) {
                $user = $group->first()->user;

                return (object)[
                    'user' => $user,
                    'role' => $user->role,
                    'filled_proformas' => $group->unique('proforma_id')->count(),
                    'total_earned' => $group->sum('amount'),
                    'total_paid' => $group->where('is_paid', true)->sum('amount'),
                    'remaining' => $group->where('is_paid', false)->sum('amount'),
                ];
            })
            ->values();

        // ---------------------------------------------
        // 💳 All Transactions Grouped by User
        // ---------------------------------------------
        $transactionsByUser = $paidUsers
            ->groupBy('user_id')
            ->map(function ($records) {
                return $records->map(function ($item) {
                    return [
                        'amount' => $item->amount,
                        'type'   => $item->is_paid ? 'Paid' : 'Unpaid',
                        'created_at' => $item->created_at->format('Y-m-d H:i'),
                    ];
                });
            });

        // ---------------------------------------------
        // 📤 Send All Data to View
        // ---------------------------------------------
        return view('accountant.dashboard', compact(
            'garageShopUsers',
            'insuranceUsers',
            'transactionsByUser',
            'startDate',
            'endDate',
            'filter'
        ));
    }

    // ---------------------------------------------
    // ✔ Mark All User Records as Paid
    // ---------------------------------------------
    public function markPaid($userId)
    {
        PaidUser::where('user_id', $userId)
            ->where('is_paid', false)
            ->update([
                'is_paid' => true,
                'paid_at' => now(),
            ]);

        return redirect()->back()->with('success', 'User marked as paid.');
    }
}
