<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Partner;

class PartnerController extends Controller
{
    public function index()
    {
    }

    public function store(Request $request)
    {

        foreach ($request->partners as $partner) {
            Partner::create([
              'insurance_id' => auth()->id(),
              'partner_id' => $partner
            ]);
        }
        return redirect()->back();
    }

    public function destroy($partnerId)
{
    $partner = Partner::findOrFail($partnerId);
    $partner->delete();

    return redirect()->back()->with('success', 'Partner removed successfully.');
}

}
