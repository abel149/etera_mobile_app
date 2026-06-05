<?php

namespace  App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
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
use App\Http\Controllers\Api\ProformaPublished;




class AdminController extends Controller
{

    public function userApprovals(Request $request)
    {
        $query = User::whereIn('role', ['others','business_owner', 'garage', 'shop']);

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
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
                $query->where(function ($q) {
                    $q->where('approved', false)
                        ->orWhereNull('approved');
                });
            } elseif ($request->get('status') === 'approved') {
                $query->where('approved', true);
            }
        }

        $users = $query->with('brands')->orderBy('created_at', 'desc')->paginate(20);

        // Get statistics - pending includes both null and false

        $pending = User::whereIn('role', ['business_owner', 'garage', 'shop'])
            ->where(function ($q) {
                $q->where('approved', false)
                    ->orWhereNull('approved');
            })->count();
        $approved = User::whereIn('role', ['business_owner', 'garage', 'shop'])->where('approved', true)->count();
        $business_owners = User::where('role', 'business_owner')->count();
        $garages_shops = User::whereIn('role', ['garage', 'shop'])->count();


        return response()->json([
            'success' => true,
            'data' => [
                'Pending' => $pending,
                'approved' => $approved,
                'business_owners' => $garages_shops,
            ]

        ], 200);
    }
    /**
     * Approve a user (superadmin only)
     */
    public function approveUser($id)
    {
        if (!auth()->user()->isSuperAdmin()) {
            return response->json([
                'success' => false,
                'message' => 'Unauthorized action. Only superadmin can approve users.'

            ], 403);
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

        return response->json([
            'success' => true,
            'message' => 'User approved successfully!'

        ]);
    }
    /**
     * Revoke user approval (superadmin only)
     */
    public function revokeUser($id)
    {
        if (!auth()->user()->isSuperAdmin()) {
            return response->json([
                'success' => false,
                'message' => 'Unauthorized action. Only superadmin can approve users.'

            ]);
        }

        $user = User::findOrFail($id);
        $user->update([
            'approved' => false,
            'approved_at' => null
        ]);

        return response->json([
            'success' => true,
            'message' => 'User rejected successfully!'

        ]);
    }

    /**
     * View user details
     */
    public function viewUser($id)
    {
        $user = User::with('brands')->findOrFail($id);
        return response->json([
            'success' => true,
            'data' => [
                'user' => $user
            ]
        ]);
    }
    public function othersProforma(){
        $proformas = \App\Models\Proforma::fromOthers()
                ->orderBy('created_at', 'desc')
                ->get();
        return response->json([
            'success' => true,
            'data' => [
                'user' => $proformas
            ]
        ]);
    }
    /**
     * Display proforma details for admin
     */
    public function proformaDetails($id)
    {
        $proforma = \App\Models\Proforma::with([
            'file_number',
            'poster',
            'brand',
            'parts',
            'applications.applicationBy',
            'applications.prices.part',
            'inboxes.user',
        ])->findOrFail($id);

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

        return response->json([
            'success' => true,
            'data' => [
                'proforma' => $proforma,
                'applications' => $applications,
                'shops' => $shops,
                'garages' => $garages,

            ]
        ]);
    }
    /**
     * Approve proforma (superadmin only)
     */
    public function float($id)
    {
        // Require Telegram connection for non-superadmin admins
        $user = auth()->user();
        if ($user->role === 'admin' && !$user->is_superadmin && empty($user->telegram_chat_id)) {
            return redirect('/telegram-connect')->with('error', 'Please connect your Telegram before you process a file!');
        }

        $proforma = \App\Models\Proforma::find($request->query($id));


        if (! $proforma || $proforma?->status != 'pending') {
            return response->json([
                'success' => false,
                'message' => 'Proforma already floated'
            ]);
        }

        $requiredShops = (int) ($proforma->required_number_of_shops ?? 0);
        if ($requiredShops > 0) {
            $shopInboxCount = \App\Models\Inbox::where('proforma_id', $proforma->id)
                ->whereHas('user', fn($q) => $q->where('role', 'shop'))
                ->count();
            if ($shopInboxCount >= $requiredShops) {
                return ressponse->json([
                    'success' => false,
                    'message' => 'This proforma already has all requested shop slots inboxed. You cannot float it.'
                ]);
            }
        }

        $proforma->update(['status' => 'published', 'processed_by' => auth()->id()]);
        $poster = \App\Models\User::find($proforma->poster_id);
        if ($poster && $poster->telegram_chat_id) {
            (new \App\Services\TelegramService())->sendProformaFloatedNotification($poster->telegram_chat_id, $poster);
        }
        // Log Activity
        \App\Models\ProformaActivityLog::create([
            'proforma_id' => $proforma->id,
            'user_id' => auth()->id(),
            'action' => 'floated',
            'details' => 'Proforma floated (published) by ' . auth()->user()->name,
        ]);

        // 🔥 Fire Event
        event(new ProformaPublished($proforma));

        return response->json([
            'success' => true,
            'message' => 'Proforma published successfully'
        ]);
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
            return response->json([
                'success' => true,
                'message' => 'Unauthorized action. Only superadmin can delete users.',
            ]);
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

        return response->json([
           'success'=>true,
           'message'=> 'User deleted successfully!'
        ]);
    }
    // API endpoint for admin dashboard real-time polling
    function dashboard() {
        if (!auth()->check() || !in_array(auth()->user()->role, ['admin', 'superadmin'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $user = auth()->user();

        $data = Cache::remember('admin_proformas_data', 10, function () {
            $proformasQuery = \App\Models\Proforma::with('poster')
                ->whereHas('poster')
                ->orderBy('created_at', 'desc');

            if (!(auth()->user()->is_superadmin == 1)) {
                $proformasQuery->where(function ($q) {
                    $q->whereNull('processed_by')->orWhere('processed_by', auth()->id());
                });
            }

            $proformas = $proformasQuery
                ->limit(100)
                ->get()
                ->map(function ($p) {
                    $label = $p->poster ? ($p->poster->role == 'business_owner' ? 'Business Owner' : ucfirst($p->poster->role)) : 'Unknown';
                    return [
                        'id' => $p->id,
                        'file_number' => $p->file_number ?? 'N/A',
                        'from' => $label,
                        'customer_name' => $p->customer_name ?? 'N/A',
                        'garage_count' => $p->applicationsFromGarages ? $p->applicationsFromGarages->count() : 0,
                        'shop_count' => $p->applicationsFromShops ? $p->applicationsFromShops->count() : 0,
                        'status' => $p->status ?? 'pending',
                        'is_from_others' => $p->poster ? $p->isFromOthers() : false,
                        'is_etera_chereta' => $p->isEteraCheretaMode(),
                        'remaining_time' => $p->isEteraCheretaMode() ? $p->getFormattedRemainingTime() : 'N/A',
                        'timer_expires_at' => $p->timer_expires_at ? $p->timer_expires_at->toISOString() : null,
                        'created_at' => $p->created_at ? $p->created_at->format('D M d, Y h:i A') : 'N/A',
                    ];
                });

            return [
                'stats' => [
                    'insurance_total' => \App\Models\Proforma::fromInsurances()->count(),
                    'insurance_completed' => \App\Models\Proforma::fromInsurances()->where('status', 'completed')->count(),
                    'others_total' => \App\Models\Proforma::fromOthers()->count(),
                    'others_completed' => \App\Models\Proforma::fromOthers()->where('status', 'completed')->count(),
                ],
                'proformas' => $proformas,
            ];
        });

        return response()->json($data);
    }
    
// API endpoint for notification bell polling (all roles)
    function  notifications() {
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $user = auth()->user();
        $unread = $user->unreadNotifications()->limit(20)->get()->map(function ($n) {
            return [
                'id' => $n->id,
                'message' => $n->data['message'] ?? 'New notification',
                'type' => $n->data['type'] ?? 'general',
                'file_number' => $n->data['file_number'] ?? null,
                'proforma_id' => $n->data['proforma_id'] ?? null,
                'created_at' => $n->created_at->diffForHumans(),
            ];
        });

        return response()->json([
            'unread_count' => $user->unreadNotifications()->count(),
            'notifications' => $unread,
        ]);
    }
    // Mark notifications as read
 function markasread() {
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $user = auth()->user();

        // Keep approval-pending notifications unread until the related user is approved.
        $excludeTypes = ['approval_pending_signup', 'approval_pending_login'];
        $user->unreadNotifications()
            ->whereNotIn('data->type', $excludeTypes)
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'unread_count' => $user->unreadNotifications()->count(),
        ]);
    }
   function proformaStatus(){
     $proformas = \App\Models\Proforma::with(['brand', 'processedBy'])
                 ->whereNotNull('processed_by')
                 ->orderBy('updated_at', 'desc')
                 ->get();

             $admins = \App\Models\User::whereIn('role', ['admin', 'superadmin'])->orderBy('name')->get();
             
        return response()->json([
            'success' => true,
            'data'=>[
                'proformas' => $proformas,
                'admins' => $admins
            ]
        ]);

   }

   /*
    admin managment
    */
   function createAdmin(Request $request){
     $request->validate([
                'name' => 'required|string|max:255',
                'phone_number' => 'required',
                'email' => 'nullable|email',
            ]);

            // Check if phone number already exists
            $existingUser = User::where('phone_number', $request->phone_number)->first();
            if ($existingUser) {
                return redirect()->back()
                    ->with('admin_error', 'The phone number already exists. Please try again.')
                    ->withInput();
            }

            // Check if email already exists
            if ($request->filled('email')) {
                $existingEmail = User::where('email', $request->email)->first();
                if ($existingEmail) {
                    return redirect()->back()
                        ->with('admin_error', 'The email already exists. Please try again.')
                        ->withInput();
                }
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'password' => bcrypt('123456'),
                'role' => 'admin',
                'approved' => true,
                'registered_by' => auth()->id(),
            ]);
             return response()->json([
            'success' => true,
            'message' => 'admin_created ,created successfully! Default password: 123456',
            'data'=>[
                'admin' => $user->name
            ]
        ]);
            
   }
   function viewAdmins(){
     $admins = \App\Models\User::whereIn('role', ['admin', 'superadmin'])->get();

     return response()->json([
            'success' => true,
            'data'=>[
                'admins' => $admins
            ]
        ]);
   }
   function updateAdmins($id){
    if (auth()->user()->role !== 'superadmin') {
                abort(403);
            }
            $admin = \App\Models\User::findOrFail($id);
            if ($admin->role === 'superadmin') {
                return redirect()->back()->withErrors(['error' => 'Cannot edit superadmin.']);
            }

            $request->validate([
                'name' => 'required|string|max:255',
                'phone_number' => 'required',
                'email' => 'nullable|email|unique:users,email,' . $admin->id,
            ]);

            // Check phone uniqueness manually
            $existingPhone = User::where('phone_number', $request->phone_number)->where('id', '!=', $admin->id)->first();
            if ($existingPhone) {
                return redirect()->back()->withErrors(['phone_number' => 'The phone number already exists.'])->withInput();
            }

            $admin->update([
                'name' => $request->name,
                'phone_number' => $request->phone_number,
                'email' => $request->email,
            ]);

            return response()->json([
               'success'=>true,
               'message'=> 'Admin updated successfully!'
            ]);
        
   }
   function deleteAdmin($id){
    if (!auth()->user()->is_superadmin) {
                abort(403);
            }
            $admin = \App\Models\User::findOrFail($id);
            if ($admin->role === 'superadmin' || $admin->id === auth()->id()) {
                return redirect()->back()->withErrors(['error' => 'Cannot delete this admin.']);
            }
            $admin->delete();
             return response()->json([
               'success'=>true,
               'message'=> 'Admin deleted successfully!'
            ]);
   }
    /*
    insurance view
    */
   function insurances(){
     $insurances = \App\Models\User::where('role', 'insurance')
            ->where('registered_by', auth()->id())
            ->get();
       return response()->json([
               'success'=>true,
               'data'=> [
                  'insurances' => $insurances,
               ]
            ]);
   }

   /*
    spare part shop managment
    */
   function spareparts(){
     $query = \App\Models\User::where('role', 'shop')->with('brands');

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('phone_number', 'like', "%{$search}%")
                      ->orWhere('tin_number', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            if ($request->filled('brand_id')) {
                $query->whereHas('brands', function ($q) use ($request) {
                    $q->where('brands.id', $request->brand_id);
                });
            }

            $shops  = $query->orderBy('name', 'asc')->get();
            $brands = \App\Models\Brand::orderBy('name', 'asc')->get();
        return response()->json([
               'success'=>true,
               'data'=> [
                  'shops' => $shops,
                  'brands' => $brands,
               ]
            ]);
  
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

        return response()->json([
            'success' => true,
            'data'=>[
                'operators'=> $operators,
                'managers'=>$managers
            ]
        ]);
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

        return response()->json([
            'success'=>true,
            'data'=> [
                'commissionData'=>$commissionData
            ]
        ]);
    }


   public function garages(){
         $query = \App\Models\User::where('role', 'garage');

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('phone_number', 'like', "%{$search}%")
                      ->orWhere('tin_number', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            $garages = $query->orderBy('name', 'asc')->get();
       return response()->json([
        'success' => true,
        'data' => [
            'garages' => $garages
        ]
       ]);

    }
    

    //marketers managment
   public function marketers(){
         $marketers = \App\Models\User::where('role', 'marketer')->get();

            return response()->json([
                'marketers' => $marketers,
            ]);
    }


}
