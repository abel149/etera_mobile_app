<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApplicationResource;
use App\Http\Resources\PartResource;
use App\Http\Resources\ProformaResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\WithdrawalResource;
use App\Models\CarPart;
use App\Models\Inbox;
use App\Models\PaidUser;
use App\Models\Proforma;
use App\Models\ProformaApplication;
use App\Models\ProformaPartPrice;
use App\Models\User;
use App\Models\WithdrawalRequest;
use App\Notifications\ProformaApplicationReceived;
use App\Services\ProformaClosingService;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ShopController extends Controller
{
    // =========================================================================
    // Resolve the "owner" user ID.
    // An employee of a shop has registered_by = shop owner ID, so all data
    // operations run under the shop owner's account.
    // =========================================================================
    private function getOwnerId(): int
    {
        $user = auth()->user();
        return $user->registered_by ?? $user->id;
    }

    // =========================================================================
    // GET /api/v1/shop/dashboard
    // Application stats + balance + inbox count
    // =========================================================================
    public function dashboard()
    {
        $ownerId = $this->getOwnerId();
        $owner   = User::find($ownerId);

        $applications = ProformaApplication::with('proforma')
            ->where('application_by', $ownerId)
            ->get();

        $inboxCount = Inbox::where('user_id', $ownerId)->count();

        return response()->json([
            'success' => true,
            'data'    => [
                'balance'         => (float) $owner->balance,
                'inbox_count'     => $inboxCount,
                'total'           => $applications->count(),
                'pending_count'   => $applications->filter(
                    fn($a) => in_array(optional($a->proforma)->status, ['pending', 'opened', 'published'])
                )->count(),
                'closed_count'    => $applications->filter(
                    fn($a) => optional($a->proforma)->status === 'closed'
                )->count(),
                'completed_count' => $applications->filter(
                    fn($a) => optional($a->proforma)->status === 'completed'
                )->count(),
            ],
        ]);
    }

    // =========================================================================
    // GET /api/v1/shop/inbox
    // Proformas directed to this shop (via Inbox)
    // =========================================================================
    public function inbox()
    {
        $ownerId   = $this->getOwnerId();
        $inboxItems = Inbox::where('user_id', $ownerId)
            ->with(['proforma.brand', 'proforma.parts'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'count'   => $inboxItems->count(),
            'data'    => $inboxItems->map(fn($item) => [
                'inbox_id'    => $item->proforma_id,
                'proforma'    => $item->proforma ? new ProformaResource($item->proforma) : null,
                'received_at' => $item->created_at?->toIso8601String(),
            ]),
        ]);
    }

    // =========================================================================
    // GET /api/v1/shop/proformas
    // List published/floated proformas available for this shop to apply.
    // Filtered by the shop's registered brands.
    // =========================================================================
    public function proformas(Request $request)
    {
        $ownerId = $this->getOwnerId();
        $owner   = User::find($ownerId);

        // Brand IDs this shop handles
        $brandIds = $owner ? $owner->brands()->pluck('brands.id')->toArray() : [];

        $query = Proforma::with(['brand', 'parts', 'poster'])
            ->whereIn('status', ['published', 'opened'])
            ->where(function ($q) use ($brandIds) {
                if (!empty($brandIds)) {
                    $q->whereIn('car_brand_id', $brandIds);
                }
            });

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $proformas = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'success'    => true,
            'data'       => $proformas->map(fn($p) => [
                'id'              => $p->id,
                'file_number'     => $p->file_number,
                'status'          => $p->status,
                'customer_name'   => $p->customer_name,
                'brand'           => $p->brand ? ['id' => $p->brand->id, 'name' => $p->brand->name] : null,
                'model'           => $p->model,
                'year'            => $p->year,
                'parts_count'     => $p->parts->count(),
                'already_applied' => ProformaApplication::where('application_by', $ownerId)
                    ->where('proforma_id', $p->id)->exists(),
                'created_at'      => $p->created_at?->toIso8601String(),
                'proforma'        => new ProformaResource($p),
            ]),
            'pagination' => [
                'current_page' => $proformas->currentPage(),
                'last_page'    => $proformas->lastPage(),
                'per_page'     => $proformas->perPage(),
                'total'        => $proformas->total(),
            ],
        ]);
    }

    // =========================================================================
    // GET /api/v1/shop/proformas/{id}
    // Full proforma detail for a shop to review before applying.
    // Clears inbox entry on view.
    // =========================================================================
    public function proformaDetail($id)
    {
        $ownerId  = $this->getOwnerId();
        $proforma = Proforma::with(['brand', 'parts'])
            ->whereIn('status', ['pending', 'opened', 'published'])
            ->find($id);

        if (!$proforma) {
            return response()->json(['success' => false, 'message' => 'Proforma not found or no longer accepting applications.'], 404);
        }

        Inbox::where('user_id', $ownerId)->where('proforma_id', $proforma->id)->delete();

        $alreadyApplied = ProformaApplication::where('application_by', $ownerId)
            ->where('proforma_id', $proforma->id)
            ->exists();

        return response()->json([
            'success'         => true,
            'already_applied' => $alreadyApplied,
            'data'            => [
                'proforma' => new ProformaResource($proforma),
                'parts'    => PartResource::collection($proforma->parts),
            ],
        ]);
    }

    // =========================================================================
    // POST /api/v1/shop/proformas/{id}/apply
    // Submit a per-part price quote for a proforma.
    //
    // Payload:
    //   {
    //     "discount": 5,
    //     "parts": [
    //       { "proforma_part_id": 1, "unit_price": 1200 },
    //       { "proforma_part_id": 2, "unit_price": 800 }
    //     ]
    //   }
    //
    // Quantity is taken from the proforma part itself (not from the client).
    // Amount is auto-calculated server-side: sum(unit_price * part.quantity) - discount%.
    // =========================================================================
    public function applyProforma(Request $request, $id)
    {
        $ownerId = $this->getOwnerId();

        try {
            return DB::transaction(function () use ($request, $id, $ownerId) {

                // Row-level lock — prevents race conditions on simultaneous submissions
                $proforma = Proforma::where('id', $id)
                    ->whereIn('status', ['pending', 'opened', 'published'])
                    ->lockForUpdate()
                    ->first();

                if (!$proforma) {
                    return response()->json(['success' => false, 'message' => 'Proforma not found or no longer accepting applications.'], 404);
                }

                // Duplicate guard
                $alreadyApplied = ProformaApplication::where('application_by', $ownerId)
                    ->where('proforma_id', $proforma->id)
                    ->exists();

                if ($alreadyApplied) {
                    return response()->json(['success' => false, 'message' => 'You have already applied to this proforma.'], 422);
                }

                // Validation — shops always submit per-part unit prices
                $validated = $request->validate([
                    'parts'                    => ['required', 'array', 'min:1'],
                    'parts.*.proforma_part_id' => ['required', 'integer', 'exists:proforma_parts,id'],
                    'parts.*.unit_price'       => ['required', 'numeric', 'min:0'],
                    'discount'                 => ['nullable', 'numeric', 'min:0', 'max:100'],
                ]);

                $discount = (float) ($validated['discount'] ?? 0);

                // Build a lookup: proforma_part_id => unit_price
                $priceMap = collect($validated['parts'])->keyBy('proforma_part_id');

                // Calculate total using the proforma's own quantity per part
                $totalAmount = 0;
                foreach ($proforma->parts as $part) {
                    $unitPrice = (float) ($priceMap[$part->id]['unit_price'] ?? 0);
                    if ($unitPrice > 0) {
                        $totalAmount += $unitPrice * ($part->quantity ?? 1);
                    }
                }

                $discountAmount = ($totalAmount * $discount) / 100;
                $finalAmount    = max($totalAmount - $discountAmount, 1);

                // Create the application record
                $application = ProformaApplication::create([
                    'proforma_id'    => $proforma->id,
                    'application_by' => $ownerId,
                    'from'           => 'shop',
                    'amount'         => $finalAmount,
                    'discount'       => $discount,
                ]);

                // Save per-part price rows (same as web controller)
                $partsProcessed = 0;
                foreach ($proforma->parts as $part) {
                    $unitPrice = (float) ($priceMap[$part->id]['unit_price'] ?? 0);
                    if ($unitPrice > 0) {
                        $quantity   = $part->quantity ?? 1;
                        $carPartId  = CarPart::firstOrCreate(
                            ['name'      => $part->component ?: ($part->number ?: ('Part-' . $part->id))],
                            ['component' => $part->component ?: 'Mechanical Parts']
                        )->id;

                        $application->prices()->create([
                            'car_part_id' => $carPartId,
                            'quantity'    => $quantity,
                            'unit_price'  => $unitPrice,
                            'part_total'  => $unitPrice * $quantity,
                        ]);

                        $partsProcessed++;
                    }
                }

                // Clear shop's inbox entry for this proforma
                Inbox::where('user_id', $ownerId)->where('proforma_id', $proforma->id)->delete();

                // Progress counts for notification content
                $requiredShops   = (int) ($proforma->required_number_of_shops ?? 0);
                $requiredGarages = (int) ($proforma->required_number_of_garages ?? 0);
                $totalRequired   = $requiredShops + $requiredGarages;
                $currentCount    = $proforma->applications()->count();

                // Telegram notification to poster
                try {
                    if ($proforma->poster && !empty($proforma->poster->telegram_chat_id)) {
                        (new TelegramService())->sendApplicationReceivedNotification(
                            $proforma->poster->telegram_chat_id,
                            $proforma,
                            'shop'
                        );
                    }
                } catch (\Throwable $e) {
                    Log::warning('Shop apply: Telegram notification failed', [
                        'proforma_id' => $proforma->id,
                        'error'       => $e->getMessage(),
                    ]);
                }

                // Database notification to poster (with correct progress counts)
                $applicant = auth()->user();
                if ($proforma->poster && $proforma->poster->id !== $ownerId) {
                    $proforma->poster->notify(
                        new ProformaApplicationReceived($proforma, $application, $applicant, $currentCount, $totalRequired)
                    );
                }

                // Auto-close via ProformaClosingService if all slots are filled.
                // Both garage AND shop quotas must be met — this shop submission may be
                // the last shop slot, but garages may not be2 done yet (e.g. insurance proforma).
                $isEteraChereta = ($requiredGarages + $requiredShops) === 0;
                if (!$isEteraChereta) {
                    $garageCount = $proforma->applications()->where('from', 'garage')->count();
                    $shopCount   = $proforma->applications()->where('from', 'shop')->count();
                    $garageMet   = $requiredGarages === 0 || $garageCount >= $requiredGarages;
                    $shopMet     = $requiredShops   === 0 || $shopCount   >= $requiredShops;

                    if ($garageMet && $shopMet) {
                        (new ProformaClosingService())->closeProforma($proforma, $ownerId);
                    }
                }

                Log::info('Shop apply proforma: completed', [
                    'proforma_id'    => $proforma->id,
                    'application_id' => $application->id,
                    'parts_saved'    => $partsProcessed,
                    'final_amount'   => $finalAmount,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Price quote submitted successfully.',
                    'data'    => [
                        'application_id'  => $application->id,
                        'amount'          => round($finalAmount, 2),
                        'discount'        => $discount,
                        'parts_processed' => $partsProcessed,
                        'proforma_status' => $proforma->fresh()->status,
                    ],
                ], 201);
            });

        } catch (\Throwable $e) {
            Log::error('Shop apply proforma failed', [
                'proforma_id' => $id,
                'user_id'     => auth()->id(),
                'error'       => $e->getMessage(),
            ]);
            return response()->json(['success' => false, 'message' => 'Failed to submit quote. Please try again.'], 500);
        }
    }

    // =========================================================================
    // GET /api/v1/shop/my-applications
    // All applications submitted by this shop, paginated.
    // =========================================================================
    public function myApplications(Request $request)
    {
        $ownerId      = $this->getOwnerId();
        $applications = ProformaApplication::with(['proforma.brand', 'proforma.parts', 'prices'])
            ->where('application_by', $ownerId)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success'    => true,
            'data'       => $applications->map(fn($app) => [
                'application_id' => $app->id,
                'amount'         => (float) $app->amount,
                'discount'       => (float) ($app->discount ?? 0),
                'submitted_at'   => $app->created_at?->toIso8601String(),
                'parts_pricing'  => $app->prices->map(fn($p) => [
                    'part_id'    => $p->car_part_id,
                    'unit_price' => (float) $p->unit_price,
                    'quantity'   => (int) $p->quantity,
                    'part_total' => (float) $p->part_total,
                ]),
                'proforma'       => $app->proforma ? new ProformaResource($app->proforma) : null,
            ]),
            'pagination' => [
                'current_page' => $applications->currentPage(),
                'last_page'    => $applications->lastPage(),
                'per_page'     => $applications->perPage(),
                'total'        => $applications->total(),
            ],
        ]);
    }

    // =========================================================================
    // GET /api/v1/shop/balance
    // Commissions earned from Etera. Shops have no outgoing invoices.
    // =========================================================================
    public function balance()
    {
        $owner = User::find($this->getOwnerId());

        $commissions = PaidUser::where('user_id', $owner->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($p) => [
                'date'      => $p->created_at->toIso8601String(),
                'type'      => 'commission',
                'reference' => 'Commission from Etera',
                'amount'    => abs((float) $p->amount),
                'is_paid'   => (bool) $p->is_paid,
            ]);

        return response()->json([
            'success' => true,
            'data'    => [
                'balance'              => (float) $owner->balance,
                'pending_commissions'  => $commissions->where('is_paid', false)->sum('amount'),
                'paid_commissions'     => $commissions->where('is_paid', true)->sum('amount'),
                'total_commissions'    => $commissions->sum('amount'),
                'withdrawal_requests'  => WithdrawalResource::collection(
                    $owner->withdrawalRequests()->orderBy('created_at', 'desc')->get()
                ),
                'transactions'         => $commissions->values(),
            ],
        ]);
    }

    // =========================================================================
    // POST /api/v1/shop/withdraw
    // Request a withdrawal. Amount must not exceed balance.
    // Body: { "amount": 500, "bank_name": "CBE", "account_number": "1000123456" }
    // =========================================================================
    public function submitWithdrawal(Request $request)
    {
        $owner = User::find($this->getOwnerId());

        $validated = $request->validate([
            'amount'         => ['required', 'numeric', 'min:1', 'max:' . $owner->balance],
            'bank_name'      => ['required', 'string', 'in:CBE,Abyssiniya,Awash,Dashen,Enat,Wegagen,Tsedey'],
            'account_number' => ['required', 'string'],
        ]);

        $withdrawal = WithdrawalRequest::create([
            'from'           => $owner->id,
            'amount'         => $validated['amount'],
            'bank_name'      => $validated['bank_name'],
            'account_number' => $validated['account_number'],
            'status'         => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Withdrawal request submitted successfully.',
            'data'    => new WithdrawalResource($withdrawal),
        ], 201);
    }

    // =========================================================================
    // GET /api/v1/shop/employees
    // List all employees registered under this shop.
    // =========================================================================
    public function listEmployees()
    {
        $ownerId   = $this->getOwnerId();
        $employees = User::where('registered_by', $ownerId)
            ->where('role', 'employee')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => UserResource::collection($employees),
        ]);
    }

    // =========================================================================
    // POST /api/v1/shop/employees
    // Create one or multiple employees at once.
    //
    // Single employee:
    //   { "name": "...", "phone_number": "...", "password": "...", "password_confirmation": "..." }
    //
    // Multiple employees (array):
    //   { "employees": [
    //       { "name": "...", "phone_number": "...", "password": "...", "password_confirmation": "..." },
    //       { "name": "...", "phone_number": "...", "password": "...", "password_confirmation": "..." }
    //   ]}
    //
    // Max 10 employees total per shop.
    // =========================================================================
    public function createEmployee(Request $request)
    {
        $ownerId      = $this->getOwnerId();
        $currentCount = User::where('registered_by', $ownerId)->where('role', 'employee')->count();

        $isBulk = $request->has('employees');

        if ($isBulk) {
            $validated = $request->validate([
                'employees'                       => ['required', 'array', 'min:1', 'max:10'],
                'employees.*.name'                => ['required', 'string', 'max:255'],
                'employees.*.phone_number'        => ['required', 'string', 'regex:/^\d{10}$/', 'distinct', 'unique:users,phone_number'],
                'employees.*.email'               => ['nullable', 'email', 'distinct', 'unique:users,email'],
                'employees.*.password'            => ['required', 'string', 'min:6', 'confirmed'],
            ]);

            $newCount = count($validated['employees']);
        } else {
            $validated = $request->validate([
                'name'         => ['required', 'string', 'max:255'],
                'phone_number' => ['required', 'string', 'regex:/^\d{10}$/', 'unique:users,phone_number'],
                'email'        => ['nullable', 'email', 'unique:users,email'],
                'password'     => ['required', 'string', 'min:6', 'confirmed'],
            ]);

            $newCount = 1;
        }

        if ($currentCount + $newCount > 10) {
            return response()->json([
                'success' => false,
                'message' => "Cannot add {$newCount} employee(s). You have {$currentCount}/10 already. Limit is 10 per shop.",
            ], 422);
        }

        $created = [];

        DB::beginTransaction();
        try {
            $list = $isBulk ? $validated['employees'] : [$validated];

            foreach ($list as $data) {
                $employee = User::create([
                    'name'          => $data['name'],
                    'phone_number'  => $data['phone_number'],
                    'email'         => $data['email'] ?? null,
                    'password'      => Hash::make($data['password']),
                    'role'          => 'employee',
                    'approved'      => true,
                    'registered_by' => $ownerId,
                ]);
                $created[] = $employee;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => count($created) . ' employee(s) created successfully.',
                'data'    => UserResource::collection(collect($created)),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Shop employee creation failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Employee creation failed. Please try again.'], 500);
        }
    }

    // =========================================================================
    // DELETE /api/v1/shop/employees/{id}
    // Remove an employee registered under this shop.
    // =========================================================================
    public function deleteEmployee($id)
    {
        $ownerId  = $this->getOwnerId();
        $employee = User::where('id', $id)
            ->where('registered_by', $ownerId)
            ->where('role', 'employee')
            ->first();

        if (!$employee) {
            return response()->json(['success' => false, 'message' => 'Employee not found.'], 404);
        }

        $employee->delete();

        return response()->json(['success' => true, 'message' => 'Employee removed successfully.']);
    }
    /**
     * shop management for the admin side
     */
     
        /**
     * Store a newly created resource in storage.
     */
public function createShop(Request $request)
{
    $request->validate([
        'name' => 'required',
        'email' => 'nullable|email|unique:users,email',
        'phone_number' => 'required|unique:users,phone_number',
        'location' => 'required',

        // Password is optional; if null, default will be applied
        'password' => 'nullable|min:6|max:6|confirmed',

        'tin_number' => 'required|unique:users,tin_number',
        'brands' => 'required',
        'brands.*' => 'required|exists:brands,id',

        'license_image' => 'required|file|image',
        'stamp_image' => 'required|file|image',
    ]);

    // Default password handling
    $password = $request->password ?? '123456';

    // Generate UNIQUE store_id
    do {
        $lastNumber = User::whereNotNull('store_id')
            ->selectRaw("MAX(CAST(SUBSTRING(store_id, 4) AS UNSIGNED)) as max_id")
            ->value('max_id');

        $newStoreId = 'ES-' . str_pad(($lastNumber + 1), 4, '0', STR_PAD_LEFT);

    } while (User::where('store_id', $newStoreId)->exists());

    // Upload images
    $licenseImagePath = $request->file('license_image')->store('public/licenses');
    $stampImagePath = $request->file('stamp_image')->store('public/stamps');

    // Create the shop user
    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'phone_number' => $request->phone_number,
        'password' => bcrypt($password),
        'location' => $request->location,
        'role' => 'shop',
        'tin_number' => $request->tin_number,
        'registered_by' => auth()->user()->id,
        'license_image' => $licenseImagePath,
        'stamp_image' => $stampImagePath,
        'store_id' => $newStoreId,
    ]);

    // Attach brands
    foreach ($request->brands as $brand) {
        BrandUser::create([
            'brand_id' => $brand,
            'user_id' => $user->id,
        ]);
    }

    return response()->json([
        'success' => true,
        'message' => 'Shop created successfully! Default password: 123456',
        'data' => [
            'user' => $user
        ]
    ]);
}

public function editShop(string $id)
{

     // Check if the authenticated user is an admin or marketer
     if (!Auth::check() || !in_array(Auth::user()->role, ['admin', 'marketer'])) {
        return redirect()->back()->with('error', 'Unauthorized access');
    }


    // Find the shop by ID
    $shop = User::findOrFail($id);

    // Fetch the brands associated with the shop (user)
    $brands = BrandUser::where('user_id', $id)->pluck('brand_id')->toArray();

    // Fetch all available brands to display in the form (optional)
    $allBrands = \App\Models\Brand::latest()->get();

   return response()->json([
        'success' => true,
        'data' => [
            'shop' => $shop,
            'brands' => $brands,
            'allBrands' => $allBrands
        ]
    ]);




}

      /**
     * Update the specified resource in storage.
     */
    public function updateShop(Request $request, string $id)
    {


        
        $request->validate([
            'name' => 'required',
            'email' => 'nullable|email|unique:users,email,' . $id,
            'phone_number' => 'required|unique:users,phone_number,' . $id,
            'location' => 'required',
            // 'business_license_number' => 'required|unique:users,business_license_number,' . $id,
            // 'license_expire_date' => 'required|date',
            'tin_number' => 'required|unique:users,tin_number,' . $id,
            'brands' => 'required',
            'brands.*' => 'required|exists:brands,id', // Ensure the brand exists
            'license_image' => 'nullable|file|image',
            'stamp_image' => 'nullable|file|image',


        ]);
    
        // Find the shop to update
        $shop = User::findOrFail($id);
    
    
        // Handle image update if a new image is uploaded
        if ($request->hasFile('license_image')) {
            // Delete the old image if it exists
            if ($shop->license_image && Storage::exists('public/licenses/' . $shop->license_image)) {
                Storage::delete('public/licenses/' . $shop->license_image);
            }
            // Store the new license image
            $shop->license_image = $request->file('license_image')->store('licenses', 'public');
        }
    
        if ($request->hasFile('stamp_image')) {
            // Delete the old image if it exists
            if ($shop->stamp_image && Storage::exists('public/stamps/' . $shop->stamp_image)) {
                Storage::delete('public/stamps/' . $shop->stamp_image);
            }
            // Store the new stamp image
            $shop->stamp_image = $request->file('stamp_image')->store('stamps', 'public');
        }
    
        // Update other fields
        $shop->name = $request->name;
        $shop->email = $request->email;
        $shop->phone_number = $request->phone_number;
        $shop->location = $request->location;
        // $shop->business_license_number = $request->business_license_number;
        // $shop->license_expire_date = $request->license_expire_date;
        $shop->tin_number = $request->tin_number;


        // Save the updated shop
        $shop->save();
    
        // Remove existing brands and add new ones
        BrandUser::where('user_id', $id)->delete();
        foreach ($request->brands as $brand) {
            BrandUser::create([
                'brand_id' => $brand,
                'user_id' => $shop->id,
            ]);
        }
    
     return response()->json([
        'success' => true,
        'message' => 'shop updated successfully'
        
    ]);
    }
    
    
    /**
     * Remove the specified resource from storage.
     */
    public function destroyShop(string $id)
    {
        // Find the shop by ID
        $shop = User::findOrFail($id);
    
        // Delete the shop's associated brands
        BrandUser::where('user_id', $id)->delete();
    
        // Delete the shop's images if they exist
        if ($shop->license_image && Storage::exists('public/licenses/' . $shop->license_image)) {
            Storage::delete('public/licenses/' . $shop->license_image);
        }
        if ($shop->stamp_image && Storage::exists('public/stamps/' . $shop->stamp_image)) {
            Storage::delete('public/stamps/' . $shop->stamp_image);
        }
    
        // Delete the shop
        $shop->delete();
    
        // Redirect with a success message
        return response()->json([
            'success' => true,
            'message' => 'shop deleted successfully'
        ]);

   
    }
}
