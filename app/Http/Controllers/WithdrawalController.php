<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WithdrawalRequest;

class WithdrawalController extends Controller
{
    public function index()
    {
        $requests = WithdrawalRequest::latest()->paginate(100);
        return view("admin.withdraw-request.view", [
          'withdrawals' => $requests
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
          'amount' => 'required|min:1|max:100000',
          'bank_name' => 'required',
          'account_number' => 'required',
        ]);

        WithdrawalRequest::create([
          'from' => auth()->user()->id,
          'amount' => $request->amount,
      'bank_name' => $request->bank_name,
      "account_number" => $request->account_number
        ]);

        return redirect()->back()->with('success', 'Withdrawal request has been sent successfully.');
    }

    public function approve(Request $request, $id)
    {
        $request = WithdrawalRequest::findOrFail($request->_id);
        $request->approve();
        return redirect()->back()->with('success', 'Withdrawal request has been approved successfully.');
    }

    public function reject(Request $request, $id)
    {
        $request = WithdrawalRequest::findOrFail($request->_id);
        $request->reject();
        return redirect()->back()->with('success', 'Withdrawal request has been rejected successfully.');
    }
}
