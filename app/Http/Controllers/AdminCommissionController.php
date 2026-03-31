<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Commission;

class CommissionController extends Controller
{
    /**
     * Display the commission settings.
     */
    public function index()
    {
        $commission = Commission::first();
        return view('admin.settings.index', compact('commission'));
    }

    /**
     * Store or update commission values.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'shopPay' => 'required|numeric|min:0',
            'garagePay' => 'required|numeric|min:0',
            'insurancePay' => 'required|numeric|min:0',
            'operatorPay' => 'required|numeric|min:0',
        ]);

        $commission = Commission::first();

        if ($commission) {
            $commission->update($validated);
        } else {
            Commission::create($validated);
        }

        return redirect()->back()->with('success', 'Commission values saved successfully.');
    }
    
    public function markPaid($userId)
{
    $userPayments = \DB::table('paid_users')
        ->where('user_id', $userId)
        ->where('is_paid', 0)
        ->update(['is_paid' => 1]);

    if ($userPayments) {
        return redirect()->back()->with('success', 'User marked as fully paid successfully.');
    }

    return redirect()->back()->with('warning', 'No pending balance found for this user.');
}

}
