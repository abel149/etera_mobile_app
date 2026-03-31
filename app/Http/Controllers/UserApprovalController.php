<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Mail;
use App\Mail\UserApprovedMail;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\BrandUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Notifications\DatabaseNotification;

class UserApprovalController extends Controller
{
    /**
     * Display the user approval page with two tables and filtering.
     */
    public function index(Request $request)
    {
        if (!auth()->user()->canViewUsers()) {
            abort(403, 'Unauthorized action.');
        }

        // Base query for non-superadmin users, with relationships
        $baseQuery = User::with(['brands', 'level', 'bankAccounts'])
        ->where('role', '!=', 'superadmin')
        ->where('id', '!=', auth()->id());


        // =======================================================
        // 1. PENDING / UNAPPROVED USERS FILTERING
        // =======================================================

        // Start with the base query for unapproved users
        $unapprovedQuery = clone $baseQuery;
        $unapprovedQuery->where(function ($q) {
            // Include users where 'approved' is false or null/empty string (Laravel's interpretation of false/unapproved)
            $q->where('approved', false)->orWhereNull('approved')->orWhere('approved', '');
        });

        // Role Filter for Unapproved
        if ($request->filled('role_unapproved')) {
            $unapprovedQuery->where('role', $request->role_unapproved);
        }

        // Search Filter for Unapproved (Name, Email, Phone)
        if ($request->filled('search_unapproved')) {
            $search = '%' . $request->search_unapproved . '%';
            $unapprovedQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)
                  ->orWhere('email', 'like', $search)
                  ->orWhere('phone_number', 'like', $search);
            });
        }

        // Fetch paginated results for unapproved
        $unapprovedUsers = $unapprovedQuery
            ->orderBy('created_at', 'desc')
            ->paginate(25, ['*'], 'unapproved_page')
            // This is crucial to keep other filters/searches when paginating
            ->withQueryString(); 


        // =======================================================
        // 2. APPROVED USERS FILTERING
        // =======================================================

        // Start with the base query for approved users
        $approvedQuery = clone $baseQuery;
        $approvedQuery->where('approved', true);

        // Role Filter for Approved
        if ($request->filled('role_approved')) {
            $approvedQuery->where('role', $request->role_approved);
        }

        // Search Filter for Approved (Name, Email, Phone)
        if ($request->filled('search_approved')) {
            $search = '%' . $request->search_approved . '%';
            $approvedQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)
                  ->orWhere('email', 'like', $search)
                  ->orWhere('phone_number', 'like', $search);
            });
        }

        // Fetch paginated results for approved
        $approvedUsers = $approvedQuery
            ->orderBy('approved_at', 'desc')
            ->paginate(25, ['*'], 'approved_page')
            // This is crucial to keep other filters/searches when paginating
            ->withQueryString();

        return view('admin.user-approval.index', compact('unapprovedUsers', 'approvedUsers'));
    }

    /**
     * Approve a user (POST request)
     */
    public function approve(User $user)
    {
        if (!auth()->user()->canApproveUsers()) {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        try {
            DB::beginTransaction();
            $user->update([
                'approved' => true,
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'rejected_at' => null, 
                'rejection_reason' => null
            ]);

            // Clear approval-pending notifications related to this user from admin inbox.
            try {
                DatabaseNotification::query()
                    ->whereIn('data->type', ['approval_pending_signup', 'approval_pending_login'])
                    ->where(function ($q) use ($user) {
                        $q->where('data->user_id', (int) $user->id)
                          ->orWhere('data->user_id', (string) $user->id);
                    })
                    ->delete();
            } catch (\Throwable $e) {
                Log::warning('[UserApproval] Failed to clear approval pending notifications', [
                    'approved_user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }

            Log::info('[UserApproval] User approved', ['target_user_id' => $user->id, 'admin_id' => auth()->id()]);
            DB::commit();
            
            if (\App\Models\EmailSetting::isEnabled('user_approved')) {
                Mail::to($user->email)->send(new UserApprovedMail($user));
            }

            return redirect()->back()->with('success', "User {$user->name} approved successfully!");

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('[UserApproval] Approval failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Approval failed. Please try again.');
        }
    }

    /**
     * Reject a user (POST request)
     */
    public function reject(Request $request, User $user)
    {
        if (!auth()->user()->canApproveUsers()) {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        try {
            DB::beginTransaction();
            $user->update([
                'approved' => false,
                'rejected_by' => auth()->id(),
                'rejected_at' => now(),
                'rejection_reason' => $request->rejection_reason,
            ]);

            Log::info('[UserApproval] User rejected', ['target_user_id' => $user->id, 'admin_id' => auth()->id()]);
            DB::commit();

            return redirect()->back()->with('success', "User {$user->name} rejected.");

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('[UserApproval] Rejection failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Rejection failed. Please try again.');
        }
    }

    /**
     * View user details (Fallback if needed, though modals are inline now)
     */
    public function show(User $user)
    {
        if (!auth()->user()->canViewUsers()) {
            abort(403);
        }
        return view('admin.user-approval.show', compact('user'));
    }

    // The routes array provided by the user is not PHP code that executes, 
    // it's a structure commonly found in web.php. I'm leaving it as is.
    // However, I will add the missing route definitions from the user's request, assuming they are required.
}
// Note: Route definitions belong in a routes file (e.g., web.php), not the controller. 
// I am including them here as the user requested to "apply the updates on these files" 
// and provided the routes alongside the controller code.

/*
|--------------------------------------------------------------------------
| Custom Route Definitions (Included from User Request)
|--------------------------------------------------------------------------
|
*/

// Assuming this block should be used to replace the existing routes that manage user approval
// If this block is not needed in the controller file, please ignore this comment.
/*
Route::middleware(['auth.user'])->group(function () {
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/user-approval', [\App\Http\Controllers\UserApprovalController::class, 'index'])->name('user-approval.index');
        Route::get('/user-approval/{user}', [\App\Http\Controllers\UserApprovalController::class, 'show'])->name('user-approval.show');
        Route::post('/user-approval/{user}/approve', [\App\Http\Controllers\UserApprovalController::class, 'approve'])->name('user-approval.approve');
        Route::post('/user-approval/{user}/reject', [\App\Http\Controllers\UserApprovalController::class, 'reject'])->name('user-approval.reject');
        Route::get('/user-approval/{user}/edit', [\App\Http\Controllers\UserApprovalController::class, 'edit'])->name('user-approval.edit');
        Route::put('/user-approval/{user}', [\App\Http\Controllers\UserApprovalController::class, 'update'])->name('user-approval.update');
        Route::delete('/user-approval/{user}', [\App\Http\Controllers\UserApprovalController::class, 'destroy'])->name('user-approval.destroy');
        Route::get('/user-approval/ajax/users', [\App\Http\Controllers\UserApprovalController::class, 'getUsers'])->name('user-approval.ajax.users');
    });
});
*/