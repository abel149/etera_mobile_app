<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\BrandUser;
use App\Models\Brand;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use App\Mail\EmailOtpMail;
use App\Models\SentEmail;
use App\Notifications\PendingApprovalSignup;

class RegisterController extends Controller
{
    /**
     * Show the universal registration form
     */
    public function showRegistrationForm()
    {
        $brands = Brand::where('is_test', false)->orderBy('name')->get();
        return view('authentication.signup', compact('brands'));
    }

    /**
     * Show the individual registration form
     */
    public function showIndividualRegistrationForm()
    {
        return view('authentication.signup-individual');
    }

    /**
     * Show the business owner registration form
     */
    public function showBusinessOwnerRegistrationForm()
    {
        return view('authentication.signup-business-owner');
    }

    /**
     * Show the garage/spare part registration form
     */
    public function showGarageSparePartRegistrationForm()
    {
        $brands = Brand::where('is_test', false)->orderBy('name')->get();
        return view('authentication.signup-garage-sparepart', compact('brands'));
    }

    /**
     * Universal registration method - handles all user types
     */
    public function store(Request $request)
    {
        // Validate common fields
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone_number' => ['required', 'regex:/^\d{10}$/', 'unique:users,phone_number'],
            'role' => 'required|string|in:individual,business_owner,others,garage,shop,insurance,employee,marketer',
            'location' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:users,email',
            'password' => 'required|string|min:6|max:6|confirmed',
            'terms' => 'required|accepted',
        ], [
            'phone_number.unique' => 'You already have an account with this phone number.',
        ]);

        // Add role-specific validation
        $this->addRoleSpecificValidation($validator, $request);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Begin transaction
        DB::beginTransaction();

        try {
            // Handle file uploads
            $licenseImagePath = $this->handleFileUpload($request, 'license_image', 'uploads/licenses');
            $stampImagePath = $this->handleFileUpload($request, 'stamp_image', 'uploads/stamps');

            // Create user with universal approval system
            $user = User::create([
                'name' => $request->name,
                'phone_number' => $request->phone_number,
                'role' => $request->role,
                'location' => $request->location,
                'latitude' => $request->latitude ?? null,
                'longitude' => $request->longitude ?? null,
                'email' => $request->filled('email') ? $request->email : null,
                'password' => Hash::make($request->password),
                'tin_number' => $request->tin_number,
                'business_license_number' => $request->business_license_number,
                'license_expire_date' => $request->license_expire_date,
                'license_image' => $licenseImagePath,
                'stamp_image' => $stampImagePath,
                'approved' => false, // ALL users require admin approval
                'balance' => 0,
                'registered_by' => $request->registered_by ?? null,
            ]);

            // Register brands for spare part shops
            if ($request->has('brands') && is_array($request->brands)) {
                foreach ($request->brands as $brand) {
                    BrandUser::create([
                        'brand_id' => $brand,
                        'user_id' => $user->id,
                    ]);
                }
            }

            // Log the registration
            Log::info('User registration submitted', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'role' => $user->role,
                'ip_address' => $request->ip(),
            ]);

            DB::commit();

            // Allow Telegram connect page access for this newly registered user.
            $request->session()->put('telegram_connect_user_id', $user->id);

            return redirect()->route('login')
                ->with('welcome', 'Registration successful! Welcome to etera. Please sign in to continue.');

        } catch (\Exception $e) {
            DB::rollback();
            
            // Clean up uploaded files on error
            if (isset($licenseImagePath)) {
                Storage::disk('public')->delete($licenseImagePath);
            }
            if (isset($stampImagePath)) {
                Storage::disk('public')->delete($stampImagePath);
            }

            Log::error('User registration failed', [
                'error' => $e->getMessage(),
                'request_data' => $request->except(['password', 'password_confirmation']),
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Registration failed. Please try again.'])
                ->withInput();
        }
    }

    /**
     * Handle individual registration (legacy support)
     */
    public function storeIndividual(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => ['required', 'regex:/^\d{10}$/', 'unique:users,phone_number'],
            'location' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email',
            'password' => 'required|string|min:6|max:6|confirmed',
            'terms' => 'accepted',
        ], [
            'phone_number.unique' => 'You already have an account with this phone number.',
        ]);

        try {
            DB::beginTransaction();

            $user = User::create([
                'name' => $validatedData['name'],
                'phone_number' => $validatedData['phone_number'],
                'location' => $validatedData['location'],
                'email' => $validatedData['email'] ?? null,
                'password' => Hash::make($validatedData['password']),
                'role' => 'individual',
                'approved' => false, // Requires admin approval
                'balance' => 0,
            ]);

            Log::info('Individual user registration submitted', [
                'user_id' => $user->id,
                'user_email' => $user->email,
            ]);

            DB::commit();

            // Allow Telegram connect page access for this newly registered user.
            $request->session()->put('telegram_connect_user_id', $user->id);

            return redirect()->route('login')
                ->with('welcome', 'Registration successful! Welcome to etera. Please sign in to continue.');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Individual registration failed', ['error' => $e->getMessage()]);
            
            return redirect()->back()
                ->withErrors(['error' => 'Registration failed. Please try again.'])
                ->withInput();
        }
    }

    /**
     * Handle business owner registration (with detailed logging)
     */
    public function storeBusinessOwner(Request $request)
    {
        // 1. Log incoming request BEFORE validation
        Log::info('STORE BUSINESS OWNER: Incoming request received', [
            'request_all' => $request->all(),
            'ip' => $request->ip(),
        ]);

        try {

            // 2. Manually validate so we can catch & log validation errors
            $validatedData = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'phone_number' => ['required', 'regex:/^\d{10}$/', 'unique:users,phone_number'],
                'location' => 'nullable|string|max:255',
                'email' => 'nullable|email|unique:users,email',
                'password' => 'required|string|min:6|max:6|confirmed',
                'terms' => 'accepted',
            ], [
                'phone_number.unique' => 'You already have an account with this phone number.',
            ]);

            // 3. If validation fails → log it
            if ($validatedData->fails()) {
                Log::warning('STORE BUSINESS OWNER: Validation failed', [
                    'errors' => $validatedData->errors()->toArray(),
                    'request_data' => $request->all()
                ]);

                return redirect()->back()
                    ->withErrors($validatedData)
                    ->withInput();
            }

            Log::info("STORE BUSINESS OWNER: Validation passed");

            DB::beginTransaction();

            // 4. Create user
            Log::info("STORE BUSINESS OWNER: Creating new user...");

            $user = User::create([
                'name' => $request->name,
                'phone_number' => $request->phone_number,
                'location' => $request->location,
                'email' => $request->filled('email') ? $request->email : null,
                'password' => Hash::make($request->password),
                'role' => 'others',
                'approved' => true,
                'balance' => 0,
            ]);

            Log::info("STORE BUSINESS OWNER: User created successfully", [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            DB::commit();
            Log::info("STORE BUSINESS OWNER: Transaction committed");

            // Allow Telegram connect page access for this newly registered user.
            $request->session()->put('telegram_connect_user_id', $user->id);

            return redirect()->route('login')
                ->with('welcome', 'Registration successful! Welcome to etera. Please sign in to continue.');

        } catch (\Throwable $e) {

            DB::rollBack();

            // 5. Log ANY error
            Log::error("STORE BUSINESS OWNER: Exception occurred", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Registration failed. Please try again.'])
                ->withInput();
        }
    }
public function storeGarageSparepart(Request $request)
{
    // SAFETY: Block any incoming store_id from the request
    $request->request->remove('store_id');

    Log::info('Incoming Garage/Shop registration', $request->except([
        'password', 'password_confirmation'
    ]));

    try {
        // ----------------- VALIDATION -----------------
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => ['required', 'regex:/^\d{10}$/', 'unique:users,phone_number'],
            'role' => 'required|string|in:garage,shop',
            'location' => 'required|string|max:255',
            'tin_number' => 'required|string|max:255',
            'license_expire_date' => 'nullable|date',
            'email' => 'nullable|email|unique:users,email',
            'password' => 'required|string|min:6|max:6|confirmed',
            'terms' => 'accepted',
            'license_image_data' => 'required|string',
            'stamp_image_data' => 'required|string',
            'brands' => 'nullable|array',
            'brands.*' => 'exists:brands,id',

            // ----------------- BANK ACCOUNT VALIDATION -----------------
            'bank_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:50',
        ], [
            'phone_number.unique' => 'You already have an account with this phone number.',
            'tin_number.required' => 'The TIN Number is required for registration.',
            'license_image_data.required' => 'Please upload the business license image.',
            'stamp_image_data.required' => 'Please upload the stamp image.',
            'bank_name.nullable' => 'Please select your bank or Telebirr.',
            'account_number.nullable' => 'Please enter your bank account number.',
        ]);

        if ($request->role === 'shop' && empty($request->brands)) {
            throw ValidationException::withMessages([
                'brands' => 'Please select at least one car brand.'
            ]);
        }

        DB::beginTransaction();

        // ----------------- MOVE IMAGES -----------------
        $licensePath = $this->moveTempImage($validatedData['license_image_data'], 'licenses');
        $stampPath   = $this->moveTempImage($validatedData['stamp_image_data'], 'stamps');

        // ----------------- USER CREATION -----------------
        $user = User::create([
            'name' => $validatedData['name'],
            'phone_number' => $validatedData['phone_number'],
            'role' => $validatedData['role'],
            'location' => $validatedData['location'],
            'tin_number' => $validatedData['tin_number'],
            'license_expire_date' => $validatedData['license_expire_date'],
            'email' => $validatedData['email'] ?? null,
            'password' => Hash::make($validatedData['password']),
            'license_image' => $licensePath,
            'stamp_image' => $stampPath,
            'approved' => false,
            'balance' => 0,
            'store_id' => null, // GUARANTEED
            'registered_by' => auth()->id() ?? null,
        ]);

        // ----------------- STORE ID GENERATION -----------------
        $prefix = $validatedData['role'] === 'shop' ? 'ES-' : 'GR-';
        $finalStoreId = $prefix . str_pad($user->id, 4, '0', STR_PAD_LEFT);
        $user->update(['store_id' => $finalStoreId]);

        // ----------------- ASSIGN BRANDS -----------------
        if ($validatedData['role'] === 'shop' && !empty($validatedData['brands'])) {
            $brandData = [];
            foreach ($validatedData['brands'] as $brandId) {
                $brandData[] = [
                    'brand_id' => $brandId,
                    'user_id' => $user->id,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
            BrandUser::insert($brandData);
        }

        // ----------------- BANK ACCOUNT CREATION -----------------
        // ----------------- BANK ACCOUNT CREATION (if provided) -----------------
        if (!empty($validatedData['bank_name']) && !empty($validatedData['account_number'])) {
            \App\Models\BankAccount::create([
                'user_id' => $user->id,
                'bank_name' => $validatedData['bank_name'],
                'account_number' => $validatedData['account_number'],
                ]);
        }


        DB::commit();

        // Allow Telegram connect page access for this newly registered user.
        $request->session()->put('telegram_connect_user_id', $user->id);

        // Notify admins that a new garage/shop user signed up and is pending approval.
        // Rate limit: once per user (24 hours) to avoid duplicates in case of retries.
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

                    // Also notify admins via Telegram if they have it connected
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
            Log::warning('Pending approval signup notification failed', [
                'user_id' => $user->id ?? null,
                'error' => $e->getMessage(),
            ]);
        }


            return redirect()->route('login')
                ->with('welcome', 'Registration successful! Welcome to etera. Please sign in to continue.');

    } catch (ValidationException $e) {
        DB::rollBack();
        return redirect()->back()
            ->withErrors($e->errors())
            ->withInput($request->except([
                'password', 'password_confirmation', 'license_image_data', 'stamp_image_data'
            ]));
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Registration failed', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return redirect()->back()
            ->withErrors(['error' => 'System error occurred during registration.'])
            ->withInput($request->except(['password', 'password_confirmation']));
    }
}



    /**
     * Moves a file from public/uploads/temp to public/uploads/{destinationFolder}
     */
    private function moveTempImage($tempPath, $destinationFolder)
    {
        // Use the 'public' disk since TempController saves to 'public'
        $disk = Storage::disk('public');

        // Logic to ensure we have the correct source path
        // TempController usually returns "uploads/temp/filename.jpg"
        $filename = basename($tempPath);
        $sourcePath = 'uploads/temp/' . $filename;

        // Check if file exists at constructed path, or fall back to provided path
        if (!$disk->exists($sourcePath)) {
            if ($disk->exists($tempPath)) {
                $sourcePath = $tempPath;
            } else {
                throw new \Exception("Temporary file not found: {$tempPath}. Please re-upload the image.");
            }
        }

        // Generate new name and destination
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $finalFilename = 'img_' . time() . '_' . Str::random(10) . '.' . $extension;
        $dateFolder = date('Y/m/d');
        $finalPath = "uploads/{$destinationFolder}/{$dateFolder}/{$finalFilename}";

        // Move the file
        $disk->move($sourcePath, $finalPath);

        return $finalPath;
    }
     
    /**
     * Send signup OTP email and redirect to verification page
     */
    private function sendSignupOtp($user)
    {
        // Generate 6-digit OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store OTP
        DB::table('email_otps')->where('email', $user->email)->delete();
        DB::table('email_otps')->insert([
            'email' => $user->email,
            'otp' => $otp,
            'type' => 'signup',
            'expires_at' => now()->addMinutes(10),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            Mail::to($user->email)->send(new EmailOtpMail($otp, $user->name));

            $emailBody = view('emails.email_otp', ['otp' => $otp, 'userName' => $user->name])->render();

            SentEmail::log(
                'email_otp',
                $user->email,
                $user->name,
                $user->id,
                null,
                'Your Email Verification Code - ETERA',
                'sent',
                null,
                $emailBody
            );

            Log::info('OTP sent to user', ['email' => $user->email]);
        } catch (\Exception $e) {
            Log::error('OTP email failed', ['error' => $e->getMessage()]);

            SentEmail::log(
                'email_otp',
                $user->email,
                $user->name,
                $user->id,
                null,
                'Your Email Verification Code - ETERA',
                'failed',
                $e->getMessage()
            );
        }

        return redirect('/verify-otp')
            ->with('otp_email', $user->email)
            ->with('success', 'Registration successful! Please verify your email with the OTP code we sent.');
    }

    private function addRoleSpecificValidation($validator, $request)
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
                    'stamp_image' => 'required|image|mimes:jpg,jpeg,png|max:2048',
                ]);
                
                if ($role === 'shop') {
                    $validator->addRules([
                        'brands' => 'required|array|min:1',
                        'brands.*' => 'exists:brands,id',
                    ]);
                }
                break;

            case 'insurance':
                $validator->addRules([
                    'tin_number' => 'required|string|max:15',
                    'business_license_number' => 'required|string|max:20',
                ]);
                break;
        }
    }

    /**
     * Handle file upload with error checking (legacy method)
     */
    private function handleFileUpload($request, $fieldName, $storagePath)
    {
        if ($request->hasFile($fieldName) && $request->file($fieldName)->isValid()) {
            return $request->file($fieldName)->store($storagePath, 'public');
        }
        return null;
    }

    /**
     * Alternative method to handle both file upload and base64
     * This method can be used if you want to support both methods
     */
    private function handleImageUpload($request, $fieldName, $folder)
    {
        // Check if it's a base64 image
        if ($request->has($fieldName . '_data') && !empty($request->input($fieldName . '_data'))) {
            return $this->saveBase64Image($request->input($fieldName . '_data'), $folder);
        }
        
        // Fall back to regular file upload
        if ($request->hasFile($fieldName) && $request->file($fieldName)->isValid()) {
            return $request->file($fieldName)->store("uploads/{$folder}", 'public');
        }
        
        return null;
    }
}
