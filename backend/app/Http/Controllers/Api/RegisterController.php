<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\BrandUser;
use App\Models\Brand;
use App\Models\BankAccount;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use App\Notifications\PendingApprovalSignup;

class RegisterController extends Controller
{
    // GET /api/brands  — helper for registration (public)
    public function brands()
    {
        $brands = \App\Models\Brand::select('id', 'name')->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data'    => $brands,
        ]);
    }
    // =====================================================================
    // Universal registration — handles all roles except garage/shop
    // POST /api/register
    // =====================================================================
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'         => 'required|string|max:255',
            'phone_number' => ['required', 'regex:/^\d{10}$/', 'unique:users,phone_number'],
            'role'         => 'required|string|in:individual,business_owner,others,garage,shop,insurance,employee,marketer',
            'location'     => 'nullable|string|max:255',
            'email'        => 'nullable|email|unique:users,email',
            'password'     => 'required|string|min:6|max:6|confirmed',
            'terms'        => 'required|accepted',
        ], [
            'phone_number.unique' => 'You already have an account with this phone number.',
            'phone_number.regex'  => 'Phone number must be exactly 10 digits.',
        ]);

        $this->addRoleSpecificValidation($validator, $request);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();

        try {
            $licenseImagePath = $this->handleFileUpload($request, 'license_image', 'uploads/licenses');
            $stampImagePath   = $this->handleFileUpload($request, 'stamp_image', 'uploads/stamps');

            $user = User::create([
                'name'                    => $request->name,
                'phone_number'            => $request->phone_number,
                'role'                    => $request->role,
                'location'                => $request->location,
                'latitude'                => $request->latitude ?? null,
                'longitude'               => $request->longitude ?? null,
                'email'                   => $request->filled('email') ? $request->email : null,
                'password'                => Hash::make($request->password),
                'tin_number'              => $request->tin_number,
                'business_license_number' => $request->business_license_number,
                'license_expire_date'     => $request->license_expire_date,
                'license_image'           => $licenseImagePath,
                'stamp_image'             => $stampImagePath,
                'approved'                => false,
                'balance'                 => 0,
                'registered_by'           => $request->registered_by ?? null,
            ]);

            if ($request->has('brands') && is_array($request->brands)) {
                foreach ($request->brands as $brand) {
                    BrandUser::create(['brand_id' => $brand, 'user_id' => $user->id]);
                }
            }

            Log::info('User registration submitted (API)', [
                'user_id' => $user->id,
                'role'    => $user->role,
                'ip'      => $request->ip(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Registration successful. Awaiting admin approval.',
                'data'    => new UserResource($user),
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();

            if (isset($licenseImagePath)) Storage::disk('public')->delete($licenseImagePath);
            if (isset($stampImagePath))   Storage::disk('public')->delete($stampImagePath);

            Log::error('API universal registration failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Registration failed. Please try again.',
            ], 500);
        }
    }

 
    // =====================================================================
    // Business owner registration
    // POST /api/register/business-owner
    // NOTE: role is stored as 'others' and approved = true — matches web behaviour
    // =====================================================================
    public function storeOthers(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'         => 'required|string|max:255',
            'phone_number' => ['required', 'regex:/^\d{10}$/', 'unique:users,phone_number'],
            'location'     => 'nullable|string|max:255',
            'email'        => 'nullable|email|unique:users,email',
            'password'     => 'required|string|min:6|max:6|confirmed',
            'terms'        => 'required|accepted',
        ], [
            'phone_number.unique' => 'You already have an account with this phone number.',
            'phone_number.regex'  => 'Phone number must be exactly 10 digits.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();

        try {
            $user = User::create([
                'name'         => $request->name,
                'phone_number' => $request->phone_number,
                'location'     => $request->location,
                'email'        => $request->filled('email') ? $request->email : null,
                'password'     => Hash::make($request->password),
                'role'         => 'others',
                'approved'     => true,
                'balance'      => 0,
            ]);

            Log::info('Business owner registration submitted (API)', [
                'user_id' => $user->id,
                'ip'      => $request->ip(),
            ]);

            DB::commit();

            $token = $user->createToken('mobile-app')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Registration successful.',
                'data'    => [
                    'token'      => $token,
                    'token_type' => 'Bearer',
                    'user'       => new UserResource($user),
                ],
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Business owner API registration failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Registration failed. Please try again.',
            ], 500);
        }
    }

    // =====================================================================
    // Garage / Spare-part shop registration
    // POST /api/register/garage-shop
    // Requires: license_image, stamp_image (direct file uploads)
    //           tin_number, brands[] (required for shop role)
    //           bank_name + account_number (optional)
    // =====================================================================
    public function storeGarageSparepart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'                 => 'required|string|max:255',
            'phone_number'         => ['required', 'regex:/^\d{10}$/', 'unique:users,phone_number'],
            'role'                 => 'required|string|in:garage,shop',
            'location'             => 'required|string|max:255',
            'tin_number'           => 'required|string|max:255',
            'license_expire_date'  => 'nullable|date',
            'email'                => 'nullable|email|unique:users,email',
            'password'             => 'required|string|min:6|max:6|confirmed',
            'terms'                => 'required|accepted',
            'license_image'        => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'stamp_image'          => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'brands'               => 'nullable|array',
            'brands.*'             => 'exists:brands,id',
            'bank_name'            => 'nullable|string|max:255',
            'account_number'       => 'nullable|string|max:50',
        ], [
            'phone_number.unique'      => 'You already have an account with this phone number.',
            'phone_number.regex'       => 'Phone number must be exactly 10 digits.',
            'tin_number.required'      => 'TIN Number is required.',
            'license_image.required'   => 'Please upload the business license image.',
            'stamp_image.required'     => 'Please upload the stamp image.',
        ]);

        // Shops must select at least one brand
        if ($request->input('role') === 'shop') {
            $validator->addRules([
                'brands'   => 'required|array|min:1',
                'brands.*' => 'exists:brands,id',
            ]);
        }

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();

        try {
            $licensePath = $this->handleFileUpload($request, 'license_image', 'uploads/licenses');
            $stampPath   = $this->handleFileUpload($request, 'stamp_image', 'uploads/stamps');

            $user = User::create([
                'name'                => $request->name,
                'phone_number'        => $request->phone_number,
                'role'                => $request->role,
                'location'            => $request->location,
                'tin_number'          => $request->tin_number,
                'license_expire_date' => $request->license_expire_date,
                'email'               => $request->filled('email') ? $request->email : null,
                'password'            => Hash::make($request->password),
                'license_image'       => $licensePath,
                'stamp_image'         => $stampPath,
                'approved'            => false,
                'balance'             => 0,
                'store_id'            => null,
                'registered_by'       => null,
            ]);

            // Generate store_id based on user ID (ES- for shop, GR- for garage)
            $prefix = $request->role === 'shop' ? 'ES-' : 'GR-';
            $user->update(['store_id' => $prefix . str_pad($user->id, 4, '0', STR_PAD_LEFT)]);

            // Attach brands (bulk insert for performance)
            if (!empty($request->brands)) {
                $brandData = collect($request->brands)->map(fn($brandId) => [
                    'brand_id'   => $brandId,
                    'user_id'    => $user->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])->toArray();

                BrandUser::insert($brandData);
            }

            // Create bank account if provided
            if ($request->filled('bank_name') && $request->filled('account_number')) {
                BankAccount::create([
                    'user_id'        => $user->id,
                    'bank_name'      => $request->bank_name,
                    'account_number' => $request->account_number,
                ]);
            }

            Log::info('Garage/Shop registration submitted (API)', [
                'user_id'  => $user->id,
                'role'     => $user->role,
                'store_id' => $user->store_id,
                'ip'       => $request->ip(),
            ]);

            DB::commit();

            // Notify admins outside the transaction so a notification failure never rolls back registration
            $this->notifyAdminsOfNewRegistration($user);

            return response()->json([
                'success' => true,
                'message' => 'Registration successful. Awaiting admin approval.',
                'data'    => new UserResource($user),
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();

            if (isset($licensePath)) Storage::disk('public')->delete($licensePath);
            if (isset($stampPath))   Storage::disk('public')->delete($stampPath);

            Log::error('Garage/Shop API registration failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Registration failed. Please try again.',
            ], 500);
        }
    } 
     // =====================================================================
    // Business owner registration
    // POST /api/register/business-owner
    // =====================================================================
  
     public function storeBusinessOwner(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'                 => 'required|string|max:255',
            'phone_number'         => ['required', 'regex:/^\d{10}$/', 'unique:users,phone_number'],
            'location'             => 'required|string|max:255',
            'tin_number'           => 'required|string|max:255',
            'license_expire_date'  => 'nullable|date',
            'email'                => 'nullable|email|unique:users,email',
            'password'             => 'required|string|min:6|max:6|confirmed',
            'terms'                => 'required|accepted',
            'license_image'        => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'stamp_image'          => 'required|image|mimes:jpg,jpeg,png|max:2048',
           
            'bank_name'            => 'nullable|string|max:255',
            'account_number'       => 'nullable|string|max:50',
        ], [
            'phone_number.unique'      => 'You already have an account with this phone number.',
            'phone_number.regex'       => 'Phone number must be exactly 10 digits.',
            'tin_number.required'      => 'TIN Number is required.',
            'license_image.required'   => 'Please upload the business license image.',
            'stamp_image.required'     => 'Please upload the stamp image.',
        ]);

       

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();

        try {
            $licensePath = $this->handleFileUpload($request, 'license_image', 'uploads/licenses');
            $stampPath   = $this->handleFileUpload($request, 'stamp_image', 'uploads/stamps');

            $user = User::create([
                'name'                => $request->name,
                'phone_number'        => $request->phone_number,
                'role'                => 'business_owner',
                'location'            => $request->location,
                'tin_number'          => $request->tin_number,
                'license_expire_date' => $request->license_expire_date,
                'email'               => $request->filled('email') ? $request->email : null,
                'password'            => Hash::make($request->password),
                'license_image'       => $licensePath,
                'stamp_image'         => $stampPath,
                'approved'            => false,
                'balance'             => 0,
                'store_id'            => null,
                'registered_by'       => null,
            ]);

            // Generate BO- store_id
            $user->update(['store_id' => 'BO-' . str_pad($user->id, 4, '0', STR_PAD_LEFT)]);

            // Create bank account if provided
            if ($request->filled('bank_name') && $request->filled('account_number')) {
                BankAccount::create([
                    'user_id'        => $user->id,
                    'bank_name'      => $request->bank_name,
                    'account_number' => $request->account_number,
                ]);
            }

            Log::info('Business owner registration submitted (API)', [
                'user_id'  => $user->id,
                'store_id' => $user->store_id,
                'ip'       => $request->ip(),
            ]);

            DB::commit();

            $this->notifyAdminsOfNewRegistration($user);

            return response()->json([
                'success' => true,
                'message' => 'Registration successful. Awaiting admin approval.',
                'data'    => new UserResource($user),
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();

            if (isset($licensePath)) Storage::disk('public')->delete($licensePath);
            if (isset($stampPath))   Storage::disk('public')->delete($stampPath);

            Log::error('Business owner API registration failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Registration failed. Please try again.',
            ], 500);
        }
    }
    // =====================================================================
    // Private helpers
    // =====================================================================

    /**
     * Add extra validation rules depending on the requested role.
     */
    private function addRoleSpecificValidation($validator, Request $request): void
    {
        $role = $request->input('role');

        switch ($role) {
            case 'business_owner':
                $validator->addRules([
                    'tin_number' => 'required|string|max:15',
                ]);
                break;

            case 'garage':
            case 'shop':
                $validator->addRules([
                    'license_image' => 'required|image|mimes:jpg,jpeg,png|max:2048',
                    'stamp_image'   => 'required|image|mimes:jpg,jpeg,png|max:2048',
                ]);

                if ($role === 'shop') {
                    $validator->addRules([
                        'brands'   => 'required|array|min:1',
                        'brands.*' => 'exists:brands,id',
                    ]);
                }
                break;

            case 'insurance':
                $validator->addRules([
                    'tin_number'              => 'required|string|max:15',
                    'business_license_number' => 'required|string|max:20',
                ]);
                break;
        }
    }

    /**
     * Store an uploaded file and return its path, or null if not present.
     */
    private function handleFileUpload(Request $request, string $fieldName, string $storagePath): ?string
    {
        if ($request->hasFile($fieldName) && $request->file($fieldName)->isValid()) {
            return $request->file($fieldName)->store($storagePath, 'public');
        }

        return null;
    }

    /**
     * Send database + Telegram notification to all admins about a new pending registration.
     * Rate-limited to once per user per 24 hours.
     */
    private function notifyAdminsOfNewRegistration(User $user): void
    {
        try {
            $key = 'pending_approval_signup_notified_user_' . $user->id;

            if (!Cache::has($key)) {
                $admins = User::whereIn('role', ['admin', 'superadmin'])
                    ->where('approved', true)
                    ->get();

                if ($admins->isNotEmpty()) {
                    Notification::send(
                        $admins,
                        new PendingApprovalSignup(
                            $user->id,
                            (string) ($user->name ?? 'User'),
                            $user->role,
                            $user->email,
                            $user->phone_number
                        )
                    );

                    $telegram = new \App\Services\TelegramService();
                    if ($telegram->isConfigured()) {
                        foreach ($admins->filter(fn($a) => !empty($a->telegram_chat_id)) as $admin) {
                            $telegram->sendNewRegistrationNotification($admin->telegram_chat_id, $user);
                        }
                    }
                }

                Cache::put($key, true, now()->addDay());
            }
        } catch (\Throwable $e) {
            Log::warning('Admin registration notification failed (API)', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);
        }
    }
}