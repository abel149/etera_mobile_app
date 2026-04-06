<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Proforma;
use App\Services\ProformaClosingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProformaController extends Controller
{
    protected $closingService;

    public function __construct(ProformaClosingService $closingService)
    {
        $this->closingService = $closingService;
    }

    public function changeStatus()
    {
        $currentUserLevel = auth()->user()->level;
    }

    public function closeProforma($id)
    {
        $proforma = Proforma::findOrFail($id);

        if (auth()->user()->role !== 'admin') {
            return back()->with('error', 'Unauthorized');
        }

        $result = $this->closingService->closeProforma($proforma, auth()->id());

        if ($result['success']) {
            return back()->with('success', $result['message']);
        } else {
            return back()->with('error', $result['message']);
        }
    }
   
       public function paymentCollected($id)
    {
        $proforma = Proforma::findOrFail($id);


        $result = $this->closingService->paymentCollected($proforma, auth()->id());

        if ($result['success']) {
            return back()->with('success', $result['message']);
        } else {
            return back()->with('error', $result['message']);
        }
    }
  public function reject(Request $request, $proformaId)
{
    $request->validate([
        'confirmation' => ['required', 'in:reject'], // Must type "reject"
    ]);

    $proforma = \App\Models\Proforma::findOrFail($proformaId);
    $proforma->status = 'rejected';
    $proforma->save();

    return redirect()->back()->with('success', 'Proforma rejected successfully.');
}



    /**
     * Get proforma status summary
     */
    public function getStatusSummary($id)
    {
        $proforma = Proforma::findOrFail($id);
        $summary = $this->closingService->getStatusSummary($proforma);

        return response()->json($summary);
    }

    /**
     * Check if proforma should be auto-closed
     */
    public function checkAutoClose($id)
    {
        $proforma = Proforma::findOrFail($id);
        $shouldClose = $this->closingService->shouldAutoClose($proforma);

        if ($shouldClose) {
            $result = $this->closingService->handleExpiredProforma($proforma);
            return response()->json($result);
        }

        return response()->json([
            'success' => true,
            'message' => 'Proforma does not need to be closed',
            'should_close' => false
        ]);
    }
}
