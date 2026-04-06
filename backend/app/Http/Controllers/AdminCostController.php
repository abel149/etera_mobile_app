<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cost;
use App\Models\Commission;

class AdminCostController extends Controller
{
    public function index()
    {
        // Costs
        $costs = Cost::orderBy('created_at', 'desc')->get();
        $currentCost = $costs->first();

        // Commission
        $commission = Commission::latest()->first();

        return view('admin.settings', compact('costs', 'currentCost', 'commission'));
    }

    public function store(Request $request)
    {
        $request->validate([
            '1_proforma_cost' => 'required|numeric',
            '2_proforma_cost' => 'required|numeric',
            '3_proforma_cost' => 'required|numeric',
            '4_proforma_cost' => 'required|numeric',
            'etera_chereta_cost' => 'required|numeric',
            'insurance_proforma' => 'required|numeric',
            'insured_cost' => 'required|numeric',
        ]);

        $latestCost = Cost::orderBy('created_at', 'desc')->first();

        $cost = new Cost();
        $cost->{'1_proforma_cost'} = $request->input('1_proforma_cost');
        $cost->{'2_proforma_cost'} = $request->input('2_proforma_cost');
        $cost->{'3_proforma_cost'} = $request->input('3_proforma_cost');
        $cost->{'4_proforma_cost'} = $request->input('4_proforma_cost');
        $cost->etera_chereta_cost = $request->input('etera_chereta_cost');
        $cost->insurance_proforma = $request->input('insurance_proforma');
        $cost->insured_cost = $request->input('insured_cost');
        $cost->cost_id = $latestCost ? $latestCost->id : null;
        $cost->save();

        return redirect()->back()->with('success', 'Cost data recorded successfully!');
    }

    public function destroy(Cost $cost)
    {
        $cost->delete();
        return redirect()->back()->with('success', 'Cost deleted successfully!');
    }
}
