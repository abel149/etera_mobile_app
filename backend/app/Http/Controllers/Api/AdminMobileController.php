<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Proforma;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AdminMobileController extends Controller
{
    private function isSuperAdmin(): bool
    {
        $user = auth()->user();
        return $user->role === 'superadmin' || (bool) $user->is_superadmin;
    }

    // =========================================================================
    // GET /api/v1/admin-mobile/dashboard
    // =========================================================================
    public function dashboard()
    {
        $user         = auth()->user();
        $isSuperAdmin = $this->isSuperAdmin();

        // ── Proforma pipeline — all proformas for both admin and superadmin ──
        $base = Proforma::query();

        $proformaPending   = (clone $base)->where('status', 'pending')->count();
        $proformaPublished = (clone $base)->where('status', 'published')->count();
        $proformaClosed    = (clone $base)->where('status', 'closed')->count();
        $proformaCompleted = (clone $base)->where('status', 'completed')->count();

        // ── Insurance & Others stats (matching web admin dashboard) ──
        $insBase = Proforma::fromInsurances();
        $othBase = Proforma::fromOthers();

        $insuranceTotal     = (clone $insBase)->count();
        $insuranceCompleted = (clone $insBase)->where('status', 'completed')->count();
        $othersTotal        = (clone $othBase)->count();
        $othersCompleted    = (clone $othBase)->where('status', 'completed')->count();

        // ── Pending approvals ──
        $pendingApprovals = User::whereIn('role', ['others', 'business_owner', 'garage', 'shop'])
            ->where(function ($q) {
                $q->where('approved', false)->orWhereNull('approved');
            })->count();

        $data = [
            'is_superadmin'      => $isSuperAdmin,
            'proforma_pending'   => $proformaPending,
            'proforma_published' => $proformaPublished,
            'proforma_closed'    => $proformaClosed,
            'proforma_completed' => $proformaCompleted,
            'insurance_total'    => $insuranceTotal,
            'insurance_completed'=> $insuranceCompleted,
            'others_total'       => $othersTotal,
            'others_completed'   => $othersCompleted,
            'pending_approvals'  => $pendingApprovals,
        ];

        // ── Superadmin-only user counts ──
        if ($isSuperAdmin) {
            $data['total_users']    = User::count();
            $data['admin_count']    = User::where('role', 'admin')->count();
            $data['insurance_users']= User::where('role', 'insurance')->count();
            $data['others_users']   = User::where('role', 'others')->count();
            $data['garage_users']   = User::where('role', 'garage')->count();
            $data['shop_users']     = User::where('role', 'shop')->count();
        }

        return response()->json(['success' => true, 'data' => $data]);
    }

    // =========================================================================
    // GET /api/v1/admin-mobile/proformas   ?status=pending&page=1
    // =========================================================================
    public function proformas(Request $request)
    {
        $user        = auth()->user();
        $isSuperAdmin = $this->isSuperAdmin();

        $query = Proforma::with(['poster:id,name,role'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $proformas = $query->paginate(15);

        return response()->json([
            'success' => true,
            'data'    => $proformas->map(fn($p) => [
                'id'            => $p->id,
                'file_number'   => $p->file_number ?? 'N/A',
                'status'        => $p->status ?? 'pending',
                'customer_name' => $p->customer_name ?? 'N/A',
                'model'         => $p->model ?? '',
                'year'          => $p->year ?? '',
                'from'          => $p->poster ? ucfirst(str_replace('_', ' ', $p->poster->role)) : 'Unknown',
                'created_at'    => $p->created_at?->toIso8601String(),
            ]),
            'pagination' => [
                'current_page' => $proformas->currentPage(),
                'last_page'    => $proformas->lastPage(),
                'total'        => $proformas->total(),
            ],
        ]);
    }

    // =========================================================================
    // POST /api/v1/admin-mobile/proformas/{id}/float
    // =========================================================================
    public function floatProforma($id)
    {
        $proforma = Proforma::find($id);

        if (!$proforma) {
            return response()->json(['success' => false, 'message' => 'Proforma not found'], 404);
        }

        if ($proforma->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Only pending proformas can be floated'], 422);
        }

        $proforma->update(['status' => 'published', 'processed_by' => auth()->id()]);

        try {
            event(new \App\Events\ProformaPublished($proforma));
        } catch (\Throwable $e) {
            Log::warning('ProformaPublished event failed', ['error' => $e->getMessage()]);
        }

        return response()->json(['success' => true, 'message' => 'Proforma floated successfully']);
    }

    // =========================================================================
    // POST /api/v1/admin-mobile/proformas/{id}/close
    // =========================================================================
    public function closeProforma($id)
    {
        $proforma = Proforma::find($id);

        if (!$proforma) {
            return response()->json(['success' => false, 'message' => 'Proforma not found'], 404);
        }

        if (in_array($proforma->status, ['closed', 'completed'])) {
            return response()->json(['success' => false, 'message' => 'Proforma is already closed/completed'], 422);
        }

        $proforma->update(['status' => 'closed']);

        // Notify poster that proforma is closed and billing is ready
        try {
            if ($proforma->poster) {
                $proforma->poster->notify(
                    new \App\Notifications\ProformaResultsReadyNotification($proforma)
                );
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to send billing notification on close', [
                'proforma_id' => $proforma->id,
                'error'       => $e->getMessage(),
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Proforma closed successfully']);
    }

    // =========================================================================
    // GET /api/v1/admin-mobile/approvals   ?role=garage&page=1
    // =========================================================================
    public function pendingApprovals(Request $request)
    {
        $query = User::whereIn('role', ['others', 'business_owner', 'garage', 'shop'])
            ->where(function ($q) {
                $q->where('approved', false)->orWhereNull('approved');
            })
            ->orderBy('created_at', 'desc');

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->paginate(20);

        return response()->json([
            'success' => true,
            'data'    => $users->map(fn($u) => [
                'id'           => $u->id,
                'name'         => $u->name,
                'role'         => $u->role,
                'phone_number' => $u->phone_number,
                'email'        => $u->email,
                'tin_number'   => $u->tin_number,
                'location'     => $u->location,
                'created_at'   => $u->created_at?->toIso8601String(),
            ]),
            'pagination' => [
                'current_page' => $users->currentPage(),
                'last_page'    => $users->lastPage(),
                'total'        => $users->total(),
            ],
        ]);
    }

    // =========================================================================
    // PUT /api/v1/admin-mobile/approvals/{id}/approve
    // =========================================================================
    public function approveUser($id)
    {
        $user = User::findOrFail($id);
        $user->update(['approved' => true]);

        try {
            DatabaseNotification::query()
                ->whereIn('data->type', ['approval_pending_signup', 'approval_pending_login'])
                ->where('data->user_id', $user->id)
                ->delete();
        } catch (\Throwable $e) {
            Log::warning('Failed to clear approval notifications', ['error' => $e->getMessage()]);
        }

        return response()->json(['success' => true, 'message' => 'User approved successfully']);
    }

    // =========================================================================
    // PUT /api/v1/admin-mobile/approvals/{id}/reject
    // =========================================================================
    public function rejectUser($id)
    {
        $user = User::findOrFail($id);
        $user->update(['approved' => false]);

        return response()->json(['success' => true, 'message' => 'User rejected successfully']);
    }

    // =========================================================================
    // GET /api/v1/admin-mobile/admins   (superadmin only)
    // =========================================================================
    public function admins()
    {
        if (!$this->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Superadmin access required'], 403);
        }

        $admins = User::whereIn('role', ['admin', 'superadmin'])
            ->orderBy('role', 'asc')
            ->orderBy('name', 'asc')
            ->get(['id', 'name', 'email', 'phone_number', 'role', 'created_at']);

        return response()->json([
            'success' => true,
            'data'    => $admins,
        ]);
    }

    // =========================================================================
    // POST /api/v1/admin-mobile/admins   (superadmin only)
    // =========================================================================
    public function createAdmin(Request $request)
    {
        if (!$this->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Superadmin access required'], 403);
        }

        $validated = $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'regex:/^\d{10}$/', 'unique:users,phone_number'],
            'email'        => ['nullable', 'email', 'unique:users,email'],
        ]);

        $admin = User::create([
            'name'          => $validated['name'],
            'phone_number'  => $validated['phone_number'],
            'email'         => $validated['email'] ?? null,
            'password'      => Hash::make('123456'),
            'role'          => 'admin',
            'approved'      => true,
            'registered_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Admin created. Default password: 123456',
            'data'    => [
                'id'           => $admin->id,
                'name'         => $admin->name,
                'phone_number' => $admin->phone_number,
                'email'        => $admin->email,
                'role'         => $admin->role,
            ],
        ], 201);
    }

    // =========================================================================
    // DELETE /api/v1/admin-mobile/admins/{id}   (superadmin only)
    // =========================================================================
    public function deleteAdmin($id)
    {
        if (!$this->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Superadmin access required'], 403);
        }

        $admin = User::findOrFail($id);

        if ($admin->role === 'superadmin') {
            return response()->json(['success' => false, 'message' => 'Cannot delete a superadmin'], 422);
        }

        if ($admin->id === auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Cannot delete your own account'], 422);
        }

        $admin->delete();

        return response()->json(['success' => true, 'message' => 'Admin deleted successfully']);
    }

    // =========================================================================
    // GET /api/v1/admin-mobile/users   (superadmin only)
    // Returns ALL users (approved + pending) with role & status filters
    // =========================================================================
    public function allUsers(Request $request)
    {
        if (!$this->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Superadmin access required'], 403);
        }

        $query = User::whereIn('role', ['others', 'business_owner', 'garage', 'shop', 'insurance'])
            ->with('brands')
            ->orderBy('created_at', 'desc');

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            if ($request->status === 'pending') {
                $query->where(function ($q) {
                    $q->where('approved', false)->orWhereNull('approved');
                });
            } elseif ($request->status === 'approved') {
                $query->where('approved', true);
            }
        }

        $users = $query->paginate(20);

        return response()->json([
            'success' => true,
            'data'    => $users->map(fn($u) => [
                'id'           => $u->id,
                'name'         => $u->name,
                'role'         => $u->role,
                'phone_number' => $u->phone_number,
                'email'        => $u->email,
                'tin_number'   => $u->tin_number,
                'location'     => $u->location,
                'approved'     => (bool) $u->approved,
                'store_id'     => $u->store_id,
                'created_at'   => $u->created_at?->toIso8601String(),
                'brands'       => $u->brands->pluck('name'),
            ]),
            'pagination' => [
                'current_page' => $users->currentPage(),
                'last_page'    => $users->lastPage(),
                'total'        => $users->total(),
            ],
        ]);
    }

    // =========================================================================
    // GET /api/v1/admin-mobile/proformas/{id}
    // Full proforma detail: parts, applications, available shops/garages for inbox
    // =========================================================================
    public function showProforma($id)
    {
        $proforma = Proforma::with([
            'poster:id,name,role,phone_number',
            'brand:id,name',
            'parts',
            'applications.applicationBy:id,name,role,phone_number,location',
            'applications.prices',
            'inboxes.user:id,name,role',
        ])->find($id);

        if (!$proforma) {
            return response()->json(['success' => false, 'message' => 'Proforma not found'], 404);
        }

        $applications = $proforma->applications->map(function ($app) {
            if ($app->from === 'shop' && $app->prices->isNotEmpty()) {
                $subtotal = $app->prices->sum('part_total');
                $discount = (float) ($app->discount ?? 0);
                $app->final_price = $subtotal - ($subtotal * $discount / 100);
            } else {
                $app->final_price = (float) ($app->amount ?? 0);
            }
            return $app;
        })->sortBy('final_price')->values();

        $shops   = User::where('role', 'shop')->where('approved', true)->get(['id', 'name', 'phone_number', 'location']);
        $garages = User::where('role', 'garage')->where('approved', true)->get(['id', 'name', 'phone_number', 'location']);

        // Billing amount — shown when status=closed so admin knows what to collect
        $billingAmount = null;
        if ($proforma->status === 'closed') {
            $closer  = new \App\Services\ProformaClosingService();
            $billing = $closer->calculateBilling($proforma);
            if ($billing) {
                $total    = (float) $billing['total'];
                $subtotal = round($total / 1.15, 2);
                $billingAmount = [
                    'subtotal'     => $subtotal,
                    'vat_amount'   => round($total - $subtotal, 2),
                    'total_amount' => $total,
                ];
            }
        }

        // Existing invoice — shown when status=completed
        $invoice = null;
        if ($proforma->status === 'completed') {
            $inv = \App\Models\ProformaInvoice::where('proforma_id', $proforma->id)
                ->orderByDesc('created_at')->first();
            if ($inv) {
                $invTotal    = (float) $inv->total_amount;
                $invSubtotal = round($invTotal / 1.15, 2);
                $invoice = [
                    'sku'          => $inv->sku,
                    'type'         => $inv->type,
                    'subtotal'     => $invSubtotal,
                    'vat_amount'   => round($invTotal - $invSubtotal, 2),
                    'total_amount' => $invTotal,
                    'is_paid'      => (bool) $inv->is_paid,
                    'created_at'   => $inv->created_at?->toDateTimeString(),
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id'               => $proforma->id,
                'file_number'      => $proforma->file_number ?? 'N/A',
                'status'           => $proforma->status ?? 'pending',
                'close_request'    => (bool) $proforma->close_request,
                'customer_name'    => $proforma->customer_name,
                'customer_phone'   => $proforma->customer_phone_number,
                'model'            => $proforma->model ?? '',
                'year'             => $proforma->year ?? '',
                'car_type'         => $proforma->car_type ?? '',
                'brand'            => $proforma->brand?->name ?? '',
                'from'             => $proforma->poster ? ucfirst(str_replace('_', ' ', $proforma->poster->role)) : 'Unknown',
                'poster_name'      => $proforma->poster?->name,
                'poster_phone'     => $proforma->poster?->phone_number,
                'required_shops'   => (int) ($proforma->required_number_of_shops ?? 0),
                'required_garages' => (int) ($proforma->required_number_of_garages ?? 0),
                'proforma_type'    => $proforma->proforma_type,
                'created_at'       => $proforma->created_at?->toIso8601String(),
                'parts' => $proforma->parts->map(fn($p) => [
                    'number'    => $p->number,
                    'name'      => $p->name,
                    'grade'     => $p->grade,
                    'condition' => $p->condition,
                    'country'   => $p->country,
                    'quantity'  => $p->quantity ?? 1,
                    'component' => $p->component,
                ]),
                'applications' => $applications->map(fn($a) => [
                    'id'                => $a->id,
                    'from'              => $a->from,
                    'applicant_name'    => $a->applicationBy?->name,
                    'applicant_phone'   => $a->applicationBy?->phone_number,
                    'applicant_location'=> $a->applicationBy?->location,
                    'amount'            => (float) ($a->amount ?? 0),
                    'discount'          => (float) ($a->discount ?? 0),
                    'final_price'       => (float) ($a->final_price ?? 0),
                    'status'            => $a->status,
                    'prices'            => $a->prices->map(fn($p) => [
                        'quantity'   => $p->quantity,
                        'unit_price' => (float) $p->unit_price,
                        'part_total' => (float) $p->part_total,
                    ]),
                ]),
                'inboxed_user_ids'  => $proforma->inboxes->pluck('user_id')->values(),
                'available_shops'   => $shops->map(fn($u) => ['id' => $u->id, 'name' => $u->name, 'phone' => $u->phone_number, 'location' => $u->location]),
                'available_garages' => $garages->map(fn($u) => ['id' => $u->id, 'name' => $u->name, 'phone' => $u->phone_number, 'location' => $u->location]),
                'billing_amount'    => $billingAmount,
                'invoice'           => $invoice,
            ],
        ]);
    }

    // =========================================================================
    // POST /api/v1/admin-mobile/proformas/{id}/inbox-shops
    // Send proforma to specific spare-part shops (inbox)
    // Body: { "user_ids": [1,2,3] }
    // =========================================================================
    public function inboxShops(Request $request, $id)
    {
        $proforma = Proforma::find($id);
        if (!$proforma) {
            return response()->json(['success' => false, 'message' => 'Proforma not found'], 404);
        }

        $userIds = $request->input('user_ids', []);
        $service = new \App\Services\InboxNotificationService();
        $result  = $service->sendToSparePartUsers($proforma, $userIds);

        return response()->json(
            $result['success']
                ? ['success' => true,  'message' => "Sent to {$result['count']} shop(s)"]
                : ['success' => false, 'message' => $result['message']],
            $result['success'] ? 200 : 500
        );
    }

    // =========================================================================
    // POST /api/v1/admin-mobile/proformas/{id}/inbox-garages
    // Send proforma to specific garages (inbox)
    // Body: { "user_ids": [1,2,3] }
    // =========================================================================
    public function inboxGarages(Request $request, $id)
    {
        $proforma = Proforma::find($id);
        if (!$proforma) {
            return response()->json(['success' => false, 'message' => 'Proforma not found'], 404);
        }

        $userIds = $request->input('user_ids', []);
        $service = new \App\Services\InboxNotificationService();
        $result  = $service->sendToGarageUsers($proforma, $userIds);

        return response()->json(
            $result['success']
                ? ['success' => true,  'message' => "Sent to {$result['count']} garage(s)"]
                : ['success' => false, 'message' => $result['message']],
            $result['success'] ? 200 : 500
        );
    }

    // =========================================================================
    // POST /api/v1/admin-mobile/proformas/{id}/send-to-owner
    // Mark proforma as closed (sends billing email + notifies poster)
    // =========================================================================
    public function sendToOwner($id)
    {
        $proforma = Proforma::find($id);
        if (!$proforma) {
            return response()->json(['success' => false, 'message' => 'Proforma not found'], 404);
        }

        if ($proforma->status === 'completed') {
            return response()->json(['success' => false, 'message' => 'Results have already been sent to the owner'], 422);
        }

        if (!in_array($proforma->status, ['closed', 'published'])) {
            return response()->json(['success' => false, 'message' => 'Proforma must be closed before sending to owner'], 422);
        }

        try {
            $verifier = new \App\Services\ProformaVerificationService();
            $verifier->verify($proforma);

            return response()->json([
                'success' => true,
                'message' => 'Proforma sent to owner successfully',
            ]);
        } catch (\Throwable $e) {
            Log::error('sendToOwner verify failed', [
                'proforma_id' => $proforma->id,
                'error'       => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to send to owner: ' . $e->getMessage(),
            ], 500);
        }
    }

    // =========================================================================
    // POST /api/v1/admin-mobile/proformas/{id}/reject
    // Reject a pending proforma and notify the poster
    // =========================================================================
    public function rejectProforma(Request $request, $id)
    {
        $proforma = Proforma::with('poster')->find($id);
        if (!$proforma) {
            return response()->json(['success' => false, 'message' => 'Proforma not found'], 404);
        }

        if ($proforma->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Only pending proformas can be rejected'], 422);
        }

        $reason = $request->input('reason', 'Your proforma has been rejected by the admin.');

        $proforma->update(['status' => 'rejected']);

        \App\Models\ProformaActivityLog::create([
            'proforma_id' => $proforma->id,
            'user_id'     => auth()->id(),
            'action'      => 'rejected',
            'details'     => 'Rejected by ' . auth()->user()->name . '. Reason: ' . $reason,
        ]);

        try {
            if ($proforma->poster) {
                $proforma->poster->notify(
                    new \App\Notifications\ProformaRejectedNotification($proforma, $reason)
                );
            }
        } catch (\Throwable $e) {
            Log::warning('rejectProforma: notification failed', ['error' => $e->getMessage()]);
        }

        return response()->json(['success' => true, 'message' => 'Proforma rejected successfully']);
    }

    // =========================================================================
    // DELETE /api/v1/admin-mobile/users/{id}   (superadmin only)
    // =========================================================================
    public function deleteUser($id)
    {
        if (!$this->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Superadmin access required'], 403);
        }

        $user = User::findOrFail($id);

        if (in_array($user->role, ['admin', 'superadmin'])) {
            return response()->json(['success' => false, 'message' => 'Use the Admins endpoint to manage admin users'], 422);
        }

        $user->delete();

        return response()->json(['success' => true, 'message' => 'User deleted successfully']);
    }
}
