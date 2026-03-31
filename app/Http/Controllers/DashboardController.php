<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Proforma;

class DashboardController extends Controller
{
    public function index()
    {
        // User role counts
        $roleCounts = User::whereIn('role', [
            'admin', 'employee', 'insurance', 'business_owner', 'marketer', 'garage', 'shop'
        ])->selectRaw('role, COUNT(*) as count')->groupBy('role')->pluck('count', 'role');

        $totalUsers = User::count();
        $totalAdmins = $roleCounts['admin'] ?? 0;
        $totalOperators = $roleCounts['employee'] ?? 0;
        $totalInsurances = $roleCounts['insurance'] ?? 0;
        $totalBusinessOwners = $roleCounts['business_owner'] ?? 0;
        $totalMarketers = $roleCounts['marketer'] ?? 0;
        $totalGarages = $roleCounts['garage'] ?? 0;
        $totalShops = $roleCounts['shop'] ?? 0;

        // Proforma statistics
        $totalRequestedFiles = Proforma::count();
        $totalCompletedFiles = Proforma::where('status', 'completed')->count();
        $totalRequestedOtherProformas = Proforma::where('type', 'other')->count();
        $totalCompletedOtherProformas = Proforma::where('type', 'other')->where('status', 'completed')->count();

        // Fetch latest proformas with relationships
        $latestProformas = Proforma::with(['poster', 'applicationsFromGarages', 'applicationsFromShops'])
            ->latest()
            ->limit(10)
            ->get();

            return view('admin.index', compact(
                'totalUsers', 'totalAdmins', 'totalOperators', 'totalInsurances',
                'totalBusinessOwners', 'totalMarketers', 'totalGarages', 'totalShops',
                'totalRequestedFiles', 'totalCompletedFiles', 'totalRequestedOtherProformas',
                'totalCompletedOtherProformas', 'latestProformas'
            ));
            
    }
}
