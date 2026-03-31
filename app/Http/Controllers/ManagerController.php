<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Proforma;

use App\Services\ProformaVerificationService;
use App\Models\ProformaSelection;

use Illuminate\Support\Facades\Log;


class ManagerController extends Controller
{
    /**
     * Display manager dashboard
     */
    public function index()
    {
        $manager = auth()->user();
        
        if (!$manager->isManager()) {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }
        
        $operators = $manager->getOperators();
        $operatorIds = $operators->pluck('id')->toArray();
        
        // Get closed proformas processed by operators managed by this manager
        // Pending: closed proformas that haven't been reviewed yet
        $pendingFilesCount = Proforma::where('status', 'closed')
            ->whereHas('selections', function($q) use ($operatorIds) {
                $q->whereIn('employee_id', $operatorIds)
                  ->where(function($subQ) {
                      $subQ->whereNull('review_status')
                           ->orWhere('review_status', 'pending');
                  });
            })
            ->count();
        
        // Approved: closed proformas that have been approved
        $approvedFilesCount = Proforma::where('status', 'closed')
            ->whereHas('selections', function($q) use ($operatorIds) {
                $q->whereIn('employee_id', $operatorIds)
                  ->where('review_status', 'approved');
            })
            ->count();
            
        // Rejected: closed proformas that have been rejected
        $rejectedFilesCount = Proforma::where('status', 'closed')
            ->whereHas('selections', function($q) use ($operatorIds) {
                $q->whereIn('employee_id', $operatorIds)
                  ->where('review_status', 'rejected');
            })
            ->count();
        
        return view('manager.dashboard', compact(
            'operators',
            'pendingFilesCount',
            'approvedFilesCount',
            'rejectedFilesCount'
        ));
    }
    
    /**
     * View assigned operators
     */
    public function viewAssignedOperators()
    {
        $manager = auth()->user();
        
        if (!$manager->isManager()) {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }
        
        $operators = $manager->getOperators();
        
        // Add statistics for each operator
        $operators = $operators->map(function ($operator) {
            $operator->total_quota = $operator->file_quota ?? 0;
            $operator->used_quota = $operator->proformaSelections()->where('active', true)->count();
            $operator->available_quota = $operator->getAvailableFileQuota();
            $operator->total_commissions = $operator->getEarnedCommissions();
            $operator->pending_commissions = $operator->getPendingCommissions();
            $operator->approved_commissions = $operator->getApprovedCommissions();
            
            return $operator;
        });
        
        return view('manager.operators.index', compact('operators'));
    }
    
    /**
     * View files processed by specific operator
     */
    public function viewOperatorFiles($operatorId)
    {
        $manager = auth()->user();
        $operator = User::findOrFail($operatorId);
        
        // Verify this operator is managed by this manager
        if (!$manager->isManager() || !$manager->getOperators()->contains($operator)) {
            return redirect()->route('manager.dashboard')->with('error', 'Unauthorized access');
        }
        
        // Get closed proformas processed by this operator
        $processedFiles = Proforma::where('status', 'closed')
            ->whereHas('selections', function($q) use ($operatorId) {
                $q->where('employee_id', $operatorId);
            })
            ->with(['poster', 'brand', 'selections' => function($q) use ($operatorId) {
                $q->where('employee_id', $operatorId);
            }])
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('manager.operators.files', compact('operator', 'processedFiles'));
    }
    
    /**
     * View pending files for review
     */
    public function reviewPendingFiles()
    {
        $manager = auth()->user();
        
        if (!$manager->isManager()) {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }
        
        $operators = $manager->getOperators();
        $operatorIds = $operators->pluck('id')->toArray();
        
        // Get closed proformas with pending review status
        $pendingSelections = ProformaSelection::whereIn('employee_id', $operatorIds)
            ->whereHas('proforma', function($q) {
                $q->where('status', 'payment collected');
            })
            ->pendingReview()
            ->with(['proforma.poster', 'proforma.brand', 'operator'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('manager.review.pending', compact('pendingSelections'));
    }
    
    /**
     * Approve a file
     */

public function approveFile(Request $request, $selectionId)
{
    $manager   = auth()->user();
    $selection = ProformaSelection::with('proforma', 'operator')->findOrFail($selectionId);

    // Authorization
    if (
        !$manager->isManager() ||
        !$manager->getOperators()->contains($selection->operator)
    ) {
        return redirect()->back()->with('error', 'Unauthorized action');
    }

    // Approve selection
    $selection->markAsApproved($manager->id);

    // 🔥 RUN FULL VERIFICATION AFTER APPROVAL
    try {
        app(ProformaVerificationService::class)
            ->verify($selection->proforma);
    } catch (\Exception $e) {
        Log::error('Approval verification failed', [
            'error' => $e->getMessage(),
            'line'  => $e->getLine(),
        ]);

        return redirect()->back()
            ->with('error', 'Approved but verification failed');
    }

    return redirect()->back()->with('success', 'File approved and processed successfully');
}

    /**
     * Reject a file
     */
    public function rejectFile(Request $request, $selectionId)
    {
        $request->validate([
            'rejection_reason' => 'required|string|min:10|max:500',
        ]);
        
        $manager = auth()->user();
        $selection = ProformaSelection::findOrFail($selectionId);
        
        // Verify authorization - check if operator is managed by this manager
        if (!$manager->isManager() || !$manager->getOperators()->contains($selection->operator)) {
            return redirect()->back()->with('error', 'Unauthorized action');
        }
        
        // Mark as rejected
        $selection->markAsRejected($manager->id, $request->rejection_reason);
        
        return redirect()->back()->with('success', 'File rejected successfully');
    }
    
    /**
     * Send proforma back to user
     */
    public function sendBackToUser($proformaId)
    {
        $manager = auth()->user();
        $proforma = Proforma::findOrFail($proformaId);
        
        if (!$manager->isManager()) {
            return redirect()->back()->with('error', 'Unauthorized action');
        }
        
        $proforma->update(['status' => 'returned']);

        // Log Activity
        \App\Models\ProformaActivityLog::create([
            'proforma_id' => $proforma->id,
            'user_id' => auth()->id(),
            'action' => 'returned',
            'details' => 'Proforma sent back to owner by ' . auth()->user()->name,
        ]);

        // Send database notification (bell icon) to poster
        try {
            if ($proforma->poster) {
                $proforma->poster->notify(
                    new \App\Notifications\ProformaSentToOwnerNotification($proforma)
                );
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Send-back notification failed', ['error' => $e->getMessage()]);
        }

        // Send Telegram notification to poster
        try {
            if ($proforma->poster && !empty($proforma->poster->telegram_chat_id)) {
                (new \App\Services\TelegramService())->sendSentToOwnerNotification(
                    $proforma->poster->telegram_chat_id,
                    $proforma
                );
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Telegram send-back notification failed', [
                'proforma_id' => $proforma->id,
                'error' => $e->getMessage(),
            ]);
        }
        
        return redirect()->back()->with('success', 'Proforma sent back to user successfully');
    }
    /**
 * View all proformas under manager's operators
 */
public function proformasIndex()
{
    $manager = auth()->user();

    if (!$manager->isManager()) {
        return redirect()->route('login')->with('error', 'Unauthorized access');
    }

    $operatorIds = $manager->getOperators()->pluck('id');

    $proformas = Proforma::whereHas('selections', function ($q) use ($operatorIds) {
            $q->whereIn('employee_id', $operatorIds);
        })
        ->with([
            'poster',
            'brand',
            'selections.employee'
        ])
        ->orderBy('created_at', 'desc')
        ->get();

    return view('manager.proformas.index', compact('proformas'));
}

}
