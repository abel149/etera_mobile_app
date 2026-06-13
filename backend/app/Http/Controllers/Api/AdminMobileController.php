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

        // ── Proforma pipeline (own proformas for admin, all for superadmin) ──
        $base = Proforma::query();
        if (!$isSuperAdmin) {
            $base->where(function ($q) use ($user) {
                $q->whereNull('processed_by')->orWhere('processed_by', $user->id);
            });
        }

        $proformaPending   = (clone $base)->where('status', 'pending')->count();
        $proformaPublished = (clone $base)->where('status', 'published')->count();
        $proformaClosed    = (clone $base)->where('status', 'closed')->count();
        $proformaCompleted = (clone $base)->where('status', 'completed')->count();

        // ── Insurance & Others stats (matching web admin dashboard) ──
        $insBase = Proforma::fromInsurances();
        $othBase = Proforma::fromOthers();
        if (!$isSuperAdmin) {
            $insBase->where('processed_by', $user->id);
            $othBase->where('processed_by', $user->id);
        }

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

        if (!$isSuperAdmin) {
            $query->where(function ($q) use ($user) {
                $q->whereNull('processed_by')->orWhere('processed_by', $user->id);
            });
        }

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
