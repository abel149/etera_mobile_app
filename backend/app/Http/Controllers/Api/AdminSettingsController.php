<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cost;
use App\Models\Commission;
use App\Models\EmailSetting;

class AdminSettingsController extends Controller
{
    /**
     * Show settings page with cost and commission data
     */
    public function index()
    {
        $costs = Cost::orderBy('created_at', 'desc')->get();
        $currentCost = $costs->first();
        $commission = Commission::first();
        $emailSettings = EmailSetting::orderBy('key')->get();

         return response()->json([
            'success' => true,
            'costs' => $costs,
            'currentCost' => $currentCost,
            'commission' => $commission,
            'emailSettings' => $emailSettings
        ]);
        
        }

    /**
     * Toggle an email setting on/off (AJAX)
     */
    public function toggleEmail(Request $request)
    {
        $request->validate(['key' => 'required|string|exists:email_settings,key']);

        $setting = EmailSetting::where('key', $request->key)->firstOrFail();
        $setting->toggle();

        return response()->json([
            'success' => true,
            'key' => $setting->key,
            'enabled' => $setting->enabled,
            'message' => $setting->description . ' is now ' . ($setting->enabled ? 'enabled' : 'disabled'),
        ]);
    }

    /**
     * Store a new cost record
     */
    public function storeCost(Request $request)
    {
        $request->validate([
            '1_proforma_cost' => 'required|numeric|min:0',
            '2_proforma_cost' => 'required|numeric|min:0',
            '3_proforma_cost' => 'required|numeric|min:0',
            '4_proforma_cost' => 'required|numeric|min:0',
            'etera_chereta_cost' => 'required|numeric|min:0',
            'insurance_proforma' => 'nullable|numeric|min:0',
        ]);

        Cost::create($request->all());

        return respose()->json([
           'success' => true,
           'message' => 'cost saved successfully'
        
        ]);
           
        }

    /**
     * Delete a cost record
     */
    public function destroyCost(Cost $cost)
    {
        $cost->delete();
        return respose()->json([
           'success' => true,
           'message' => 'cost deleted successfully'
        
        ]);
    }

    /**
     * Store or update commission values
     */
    public function storeCommission(Request $request)
    {
        $request->validate([
            'shopPay' => 'required|numeric|min:0',
            'garagePay' => 'required|numeric|min:0',
            'insurancePay' => 'required|numeric|min:0',
            'othersPay' => 'required|numeric|min:0',
        ]);

        $commission = Commission::first();

        if ($commission) {
            $commission->update($request->all());
        } else {
            Commission::create($request->all());
        }

        return respose()->json([
           'success' => true,
           'message' => 'cost commission saved successfully'
        
        ]);
            }
}
