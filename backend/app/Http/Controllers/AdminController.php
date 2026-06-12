<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Proforma;
use App\Services\SendToOwnerNotificationService;
use App\Services\InboxNotificationService;
use App\Notifications\ProformaFloatedNotification;
use App\Notifications\ProformaRejectedNotification;
use App\Services\TelegramService;
use App\Events\ProformaStatusChanged;
use App\Events\NotificationSent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Notifications\DatabaseNotification;

class AdminController extends Controller
{
   public function login(Request $request)
   {
   } 
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Display user approval management page
     */
    public function userApprovals(Request $request)
    {
        $query = User::whereIn('role', ['business_owner', 'garage', 'shop']);

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%")
                  ->orWhere('tin_number', 'like', "%{$search}%");
            });
        }

        // Apply role filter
        if ($request->filled('role')) {
            $query->where('role', $request->get('role'));
        }

        // Apply status filter
        if ($request->filled('status')) {
            if ($request->get('status') === 'pending') {
                // Pending includes both null and false
                $query->where(function($q) {
                    $q->where('approved', false)
                      ->orWhereNull('approved');
                });
            } elseif ($request->get('status') === 'approved') {
                $query->where('approved', true);
            }
        }

        $users = $query->with('brands')->orderBy('created_at', 'desc')->paginate(20);

        // Get statistics - pending includes both null and false
        $stats = [
            'pending' => User::whereIn('role', ['business_owner', 'garage', 'shop'])
                ->where(function($q) {
                    $q->where('approved', false)
                      ->orWhereNull('approved');
                })->count(),
            'approved' => User::whereIn('role', ['business_owner', 'garage', 'shop'])->where('approved', true)->count(),
            'business_owners' => User::where('role', 'business_owner')->count(),
            'garages_shops' => User::whereIn('role', ['garage', 'shop'])->count(),
        ];

        return view('admin.users.approvals', compact('users', 'stats'));
    }

    /**
     * Approve a user (superadmin only)
     */
    public function approveUser($id)
    {
        if (!auth()->user()->isSuperAdmin()) {
            return redirect()->back()->with('error', 'Unauthorized action. Only superadmin can approve users.');
        }

        $user = User::findOrFail($id);
        $user->update([
            'approved' => true,
            'approved_at' => now()
        ]);

        // Clear approval-pending notifications related to this user from admin inbox.
        try {
            DatabaseNotification::query()
                ->whereIn('data->type', ['approval_pending_signup', 'approval_pending_login'])
                ->where('data->user_id', $user->id)
                ->delete();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to clear approval pending notifications', [
                'approved_user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }

        return redirect()->back()->with('success', 'User approved successfully!');
    }

    /**
     * Revoke user approval (superadmin only)
     */
    public function revokeUser($id)
    {
        if (!auth()->user()->isSuperAdmin()) {
            return redirect()->back()->with('error', 'Unauthorized action. Only superadmin can revoke user approvals.');
        }

        $user = User::findOrFail($id);
        $user->update([
            'approved' => false,
            'approved_at' => null
        ]);

        return redirect()->back()->with('success', 'User approval revoked successfully!');
    }

    /**
     * View user details
     */
    public function viewUser($id)
    {
        $user = User::with('brands')->findOrFail($id);
        return view('admin.users.view', compact('user'));
    }

    /**
     * Display proforma details for admin
     */
    public function proformaDetails($id)
    {
        $proforma = \App\Models\Proforma::with([
            'poster',
            'brand',
            'parts',
            'applications.applicationBy',
            'applications.prices.part',
            'inboxes.user',
        ])
            ->findOrFail($id);

        // Sort applications by amount ascending (lowest price first)
        $applications = $proforma->applications->sortBy('amount');

        // For non-Etera Chereta, show only the requested number of applications
        $requiredShops = (int) ($proforma->required_number_of_shops ?? 0);
        if ($requiredShops > 0) {
            $applications = $applications->take($requiredShops);
        }

        // Shops and garages for the send-to-inbox form
        $shops = \App\Models\User::where('role', 'shop')->where('approved', true)->orderBy('name')->get();
        $garages = \App\Models\User::where('role', 'garage')->where('approved', true)->orderBy('name')->get();

        return view('admin.proforma.details', compact('proforma', 'applications', 'shops', 'garages'));
    }

    /**
     * Delete proforma (superadmin only)
     */
    public function deleteProforma($id)
    {
        if (!auth()->user()->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action'], 403);
        }

        $proforma = \App\Models\Proforma::findOrFail($id);
        $proforma->delete();

        return response()->json(['success' => true, 'message' => 'Proforma deleted successfully']);
    }

    /**
     * Approve proforma (superadmin only)
     */
    public function approveProforma($id)
    {
        if (!auth()->user()->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action'], 403);
        }

        $proforma = \App\Models\Proforma::findOrFail($id);
        $proforma->update(['status' => 'published']);

        // Notify shops whose brands match this proforma's brand (skip for garage-only)
        try {
            if ($proforma->proforma_type !== 'insurance_garage_only') {
                $matchingShops = User::where('role', 'shop')
                    ->where('approved', true)
                    ->whereHas('brands', function ($q) use ($proforma) {
                        $q->where('brand_id', $proforma->car_brand_id);
                    })->get();

                if ($matchingShops->isNotEmpty()) {
                    Notification::send($matchingShops, new ProformaFloatedNotification($proforma));
                }
            }

            // Notify garages if proforma is from insurance AND not shop-only
            if ($proforma->poster && $proforma->poster->role === 'insurance'
                && $proforma->proforma_type !== 'insurance_shop_only') {
                $garages = User::where('role', 'garage')->where('approved', true)->get();
                if ($garages->isNotEmpty()) {
                    Notification::send($garages, new ProformaFloatedNotification($proforma));
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to send float notifications', ['error' => $e->getMessage()]);
        }

        // Telegram: notify matching shops/garages
        try {
            $telegram = new TelegramService();
            if ($telegram->isConfigured()) {
                $allNotified = $matchingShops ?? collect();
                if (isset($garages)) $allNotified = $allNotified->merge($garages);
                foreach ($allNotified->filter(fn($u) => !empty($u->telegram_chat_id)) as $user) {
                    $telegram->sendProformaNotification($user->telegram_chat_id, $proforma);
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Telegram float notification failed', ['error' => $e->getMessage()]);
        }

        // Broadcast via Reverb to all listening clients
        try {
            event(new ProformaStatusChanged($proforma, 'floated'));

            // Send NotificationSent to each notified user's private channel
            $allNotified = ($matchingShops ?? collect());
            if (isset($garages)) $allNotified = $allNotified->merge($garages);
            foreach ($allNotified as $user) {
                $unreadCount = $user->unreadNotifications()->count();
                event(new NotificationSent($user->id, [
                    'message' => 'New proforma #' . $proforma->file_number . ' is available',
                    'type' => 'proforma_floated',
                    'file_number' => $proforma->file_number,
                    'proforma_id' => $proforma->id,
                ], $unreadCount));
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Broadcast float event failed', ['error' => $e->getMessage()]);
        }

        return response()->json(['success' => true, 'message' => 'Proforma approved successfully']);
    }

    /**
     * Accept application (superadmin only)
     */
    public function acceptApplication($id)
    {
        if (!auth()->user()->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action'], 403);
        }

        $application = \App\Models\ProformaApplication::findOrFail($id);
        $application->update(['status' => 'accepted']);

        return response()->json(['success' => true, 'message' => 'Application accepted successfully']);
    }

    /**
     * Reject application (superadmin only)
     */
    public function rejectApplication($id)
    {
        if (!auth()->user()->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action'], 403);
        }

        $application = \App\Models\ProformaApplication::findOrFail($id);
        $application->update(['status' => 'rejected']);

        // Notify the applicant (shop/garage who applied)
        try {
            $applicant = $application->applicationBy;
            $proforma = $application->proforma;
            if ($applicant && $proforma) {
                $applicant->notify(new ProformaRejectedNotification($proforma));

                // Telegram
                if (!empty($applicant->telegram_chat_id)) {
                    (new TelegramService())->sendRejectedNotification($applicant->telegram_chat_id, $proforma);
                }

                // Broadcast rejection to user's private channel
                event(new ProformaStatusChanged($proforma, 'rejected', $applicant->id));
                event(new NotificationSent($applicant->id, [
                    'message' => 'Your application for proforma #' . $proforma->file_number . ' was rejected',
                    'type' => 'proforma_rejected',
                    'file_number' => $proforma->file_number,
                    'proforma_id' => $proforma->id,
                ], $applicant->unreadNotifications()->count()));
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to send rejection notification', ['error' => $e->getMessage()]);
        }

        return response()->json(['success' => true, 'message' => 'Application rejected successfully']);
    }

    /**
     * Close proforma (admin only)
     */
    public function closeProforma($id)
    {
        if (!auth()->user()->isSuperAdmin() && auth()->user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized action'], 403);
        }

        $proforma = Proforma::findOrFail($id);
        $proforma->update(['status' => 'closed']);

        // Notify poster via database + Telegram
        try {
            if ($proforma->poster) {
                $proforma->poster->notify(new \App\Notifications\ProformaSentToOwnerNotification($proforma));

                if (!empty($proforma->poster->telegram_chat_id)) {
                    (new TelegramService())->sendClosedNotification($proforma->poster->telegram_chat_id, $proforma);
                }

                // Broadcast close event
                event(new ProformaStatusChanged($proforma, 'closed', $proforma->poster_id));
                event(new NotificationSent($proforma->poster->id, [
                    'message' => 'Proforma #' . $proforma->file_number . ' has been closed',
                    'type' => 'proforma_closed',
                    'file_number' => $proforma->file_number,
                    'proforma_id' => $proforma->id,
                ], $proforma->poster->unreadNotifications()->count()));
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to send close notification', ['error' => $e->getMessage()]);
        }

        // Notify the admin who floated this proforma via Telegram
        try {
            if ($proforma->processedBy && !empty($proforma->processedBy->telegram_chat_id)) {
                (new TelegramService())->sendFloaterClosedNotification(
                    $proforma->processedBy->telegram_chat_id,
                    $proforma
                );
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to send floater close notification', ['error' => $e->getMessage()]);
        }

        return response()->json(['success' => true, 'message' => 'Proforma closed successfully']);
    }

    /**
     * Delete a user (superadmin only)
     */
    public function deleteUser($id)
    {
        if (!auth()->user()->isSuperAdmin()) {
            return redirect()->back()->with('error', 'Unauthorized action. Only superadmin can delete users.');
        }

        $user = User::findOrFail($id);
        
        // Delete associated files if they exist
        if ($user->license_image && \Storage::exists($user->license_image)) {
            \Storage::delete($user->license_image);
        }
        if ($user->stamp_image && \Storage::exists($user->stamp_image)) {
            \Storage::delete($user->stamp_image);
        }
        
        $user->delete();

        return redirect()->back()->with('success', 'User deleted successfully!');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Send proforma to garage users
     */
    public function sendToGarage(Request $request, $id)
    {
        try {
            Log::info('AdminController@sendToGarage: started', [
                'proforma_id' => $id,
                'admin_user_id' => auth()->id(),
                'user_ids_count' => is_array($request->input('user_ids', [])) ? count($request->input('user_ids', [])) : null,
            ]);

            $proforma = Proforma::findOrFail($id);
            $userIds = $request->input('user_ids', []);
            
            $service = new InboxNotificationService();
            $result = $service->sendToGarageUsers($proforma, $userIds);

            Log::info('AdminController@sendToGarage: completed', [
                'proforma_id' => $proforma->id,
                'success' => (bool) ($result['success'] ?? false),
                'count' => $result['count'] ?? null,
            ]);
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => "Proforma sent to {$result['count']} garage users successfully"
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 500);
            }
            
        } catch (\Exception $e) {
            Log::error('AdminController@sendToGarage failed', [
                'proforma_id' => $id,
                'admin_user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error sending proforma to garage users: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send proforma to insurance users
     */
    public function sendToInsurance(Request $request, $id)
    {
        try {
            $proforma = Proforma::findOrFail($id);
            $userIds = $request->input('user_ids', []);
            
            $service = new SendToOwnerNotificationService();
            $result = $service->sendToInsuranceUsers($proforma, $userIds);
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => "Proforma sent to {$result['count']} insurance users successfully"
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 500);
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error sending proforma to insurance users: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send proforma to business owner users
     */
    public function sendToBusinessOwner(Request $request, $id)
    {
        try {
            $proforma = Proforma::findOrFail($id);
            $userIds = $request->input('user_ids', []);
            
            $service = new SendToOwnerNotificationService();
            $result = $service->sendToBusinessOwnerUsers($proforma, $userIds);
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => "Proforma sent to {$result['count']} business owner users successfully"
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 500);
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error sending proforma to business owner users: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send proforma to spare-part users (inbox notification)
     */
    public function sendToSparePart(Request $request, $id)
    {
        try {
            Log::info('AdminController@sendToSparePart: started', [
                'proforma_id' => $id,
                'admin_user_id' => auth()->id(),
                'user_ids_count' => is_array($request->input('user_ids', [])) ? count($request->input('user_ids', [])) : null,
            ]);

            $proforma = Proforma::findOrFail($id);
            $userIds = $request->input('user_ids', []);
            
            $service = new InboxNotificationService();
            $result = $service->sendToSparePartUsers($proforma, $userIds);

            Log::info('AdminController@sendToSparePart: completed', [
                'proforma_id' => $proforma->id,
                'success' => (bool) ($result['success'] ?? false),
                'count' => $result['count'] ?? null,
            ]);
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => "Proforma sent to {$result['count']} spare-part users successfully"
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 500);
            }
            
        } catch (\Exception $e) {
            Log::error('AdminController@sendToSparePart failed', [
                'proforma_id' => $id,
                'admin_user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error sending proforma to spare-part users: ' . $e->getMessage()
            ], 500);
        }
    }

    // =====================
    // Operator Management
    // =====================

    /**
     * List all operators with stats
     */
    public function listOperators()
    {
        if (!auth()->user()->isSuperAdmin()) {
            return redirect()->back()->with('error', 'Unauthorized access');
        }

        $operators = User::where('role', User::ROLE_OPERATOR)
            ->with(['myManager.manager'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Add statistics for each operator
        $operators = $operators->map(function ($operator) {
            $operator->total_quota = $operator->file_quota ?? 0;
            $operator->used_quota = $operator->proformaSelections()->where('active', true)->count();
            $operator->available_quota = $operator->getAvailableFileQuota();
            $operator->total_commissions = $operator->getEarnedCommissions();
            
            return $operator;
        });

        $managers = User::where('role', User::ROLE_MANAGER)->get();

        return view('admin.operators.index', compact('operators', 'managers'));
    }

    /**
     * Assign operator to manager
     */
    public function assignOperatorToManager(Request $request, $operatorId)
    {
        if (!auth()->user()->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'manager_id' => 'required|exists:users,id',
        ]);

        $operator = User::findOrFail($operatorId);
        $manager = User::findOrFail($request->manager_id);

        if (!$operator->isOperator()) {
            return response()->json(['success' => false, 'message' => 'User is not an operator'], 400);
        }

        if (!$manager->isManager()) {
            return response()->json(['success' => false, 'message' => 'Selected user is not a manager'], 400);
        }

        // Create or update the employee-manager relationship
        \App\Models\EmployeeManager::updateOrCreate(
            ['employee_id' => $operatorId],
            ['manager_id' => $request->manager_id]
        );

        return response()->json([
            'success' => true,
            'message' => 'Operator assigned to manager successfully'
        ]);
    }

    /**
     * Set operator file quota
     */
    public function setOperatorQuota(Request $request, $operatorId)
    {
        if (!auth()->user()->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'file_quota' => 'required|integer|min:0|max:1000',
        ]);

        $operator = User::findOrFail($operatorId);

        if (!$operator->isOperator()) {
            return response()->json(['success' => false, 'message' => 'User is not an operator'], 400);
        }

        $operator->update(['file_quota' => $request->file_quota]);

        return response()->json([
            'success' => true,
            'message' => 'File quota updated successfully',
            'quota' => $request->file_quota
        ]);
    }

    /**
     * Set operator commission per file
     */
    public function setOperatorCommission(Request $request, $operatorId)
    {
        if (!auth()->user()->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'commission_per_file' => 'required|numeric|min:0|max:100000',
        ]);

        $operator = User::findOrFail($operatorId);

        if (!$operator->isOperator()) {
            return response()->json(['success' => false, 'message' => 'User is not an operator'], 400);
        }

        $operator->update(['commission_per_file' => $request->commission_per_file]);

        return response()->json([
            'success' => true,
            'message' => 'Commission rate updated successfully',
            'commission' => $request->commission_per_file
        ]);
    }

    /**
     * View all commissions overview
     */
    public function viewAllCommissions()
    {
        if (!auth()->user()->isSuperAdmin()) {
            return redirect()->back()->with('error', 'Unauthorized access');
        }

        $operators = User::where('role', User::ROLE_OPERATOR)->get();
        
        $commissionData = $operators->map(function ($operator) {
            return [
                'operator' => $operator,
                'pending' => $operator->getPendingCommissions(),
                'approved' => $operator->getApprovedCommissions(),
                'total_earned' => $operator->getEarnedCommissions(),
            ];
        });

        return view('admin.commissions.index', compact('commissionData'));
    }
}
