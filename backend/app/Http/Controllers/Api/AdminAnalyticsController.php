<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\PaidUser;
use App\Models\ProformaInvoice;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
class AdminAnalyticsController extends Controller
{
    // ---------------------------
    // ADMIN ANALYTICS PAGE
    // ---------------------------
public function index()
{
    $currentUser = auth()->user();

    $users = User::whereIn('role', ['garage', 'shop', 'insurance', 'operator'])->get();

    // Process users by type
    $garageShopUsers = $this->processUsers(
        $users->whereIn('role', ['garage', 'shop'])
    );

    $insuranceUsers = $this->processUsers(
        $users->where('role', 'insurance')
    );

    $operatorUsers = $this->processUsers(
        $users->where('role', 'operator')
    );

    $allUsers = $garageShopUsers
        ->merge($insuranceUsers)
        ->merge($operatorUsers)
        ->keyBy(fn ($u) => $u->user->id);

    // Return view based on requester role
    switch ($currentUser->role) {
        case 'admin':
               return response()->json([
                'success'=>true,
               'data'=>[
                'garageShopUsers' => $garageShopUsers,
                'insuranceUsers' =>$insuranceUsers,
                'operatorUsers' =>$operatorUsers,
                'allUsers' => $allUsers
               ]
               
            ]);
        default:
            // Admin or accountant sees everything
             return response()->json([
                'success'=>true,
               'data'=>[
                'garageShopUsers' => $garageShopUsers,
                'insuranceUsers' =>$insuranceUsers,
                'operatorUsers' =>$operatorUsers,
                'allUsers' => $allUsers
               ]
               
            ]);
    }
}


    // ---------------------------
    // MARK USER AS PAID
    // ---------------------------
    public function markPaid($userId)
    {
        $unpaid = PaidUser::where('user_id', $userId)
            ->where('is_paid', false)
            ->get();

        if ($unpaid->isEmpty()) {
            return response()->json([
            'success' =>true,
            'message'=> 'no remaning balace left'
        ]);  
        }

        foreach ($unpaid as $row) {
            $row->markAsPaid();
        }

       return response()->json([
            'success' =>true,
            'message'=> 'successfully marked as paid'
        ]);  
    }

public function receivePayment($userId)
{
    // Get all unpaid insurance invoices for this user
    $unpaid = ProformaInvoice::whereHas('proforma', function ($q) use ($userId) {
        $q->where('poster_id', $userId);
    })->where('is_paid', false)->get();

    if ($unpaid->isEmpty()) {
         return response()->json([
            'success' =>true,
            'message'=> 'no remaning balace left'
        ]);  
          }

    foreach ($unpaid as $row) {
        $row->markAsPaid(); // your existing model function
    }

    return response()->json([
            'success' =>true,
          
        ]);  
        }


    // ---------------------------
    // PROCESS USERS
    // ---------------------------
    private function processUsers($users)
    {
        return $users->map(function ($user) {

            /* ================= PaidUser ================= */
            $paidUsers = PaidUser::where('user_id', $user->id)->get();

            $totalEarned = $paidUsers->sum('amount');
            $totalPaid   = $paidUsers->where('is_paid', true)->sum('amount');
            $remaining   = $totalEarned - $totalPaid;

            /* ================= Insurance Proformas ================= */
            $invoiceCount = 0;
            $invoiceTotal = 0;
            $invoicePaid  = 0;
            $invoiceUnpaid = 0;
            $invoices = collect();

            if ($user->role === 'insurance') {
               $invoices = ProformaInvoice::whereHas('proforma', function ($q) use ($user) {
    $q->where('poster_id', $user->id)
      ->where('insured', true); // <-- only insured proformas
})->get();

                
                
    \Log::info("Invoices for user {$user->id} ({$user->name}):", $invoices->toArray());

                $invoiceCount = $invoices->count();
                $invoiceTotal = $invoices->sum('total_amount');
                $invoicePaid  = $invoices->where('is_paid', true)->sum('total_amount');
                $invoiceUnpaid = $invoiceTotal - $invoicePaid;
            }

            return response()->json([
                'success'=> true,
                'data'=>  [
                    'user' => $user,
                    'role' => $user->role,

                    'filled_applications' => $paidUsers->whereNotNull('application_id')->count(),
                    'filled_proformas'    => $paidUsers->whereNotNull('proforma_id')->count(),

                    'total_earned' => $totalEarned,
                    'total_paid'   => $totalPaid,
                    'remaining'    => $remaining,

                    // Insurance only
                    'insurance_proforma_count'  => $invoiceCount,
                    'insurance_proforma_total'  => $invoiceTotal,
                    'insurance_proforma_paid'   => $invoicePaid,
                    'insurance_proforma_unpaid' => $invoiceUnpaid,

                    'invoices' => $invoices,
                    'transactions' => $paidUsers,
            ]
            ]);
    });
    }
}
