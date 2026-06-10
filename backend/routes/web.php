<?php
use App\Http\Controllers\CarPartController;
use App\Http\Controllers\ProformaController;
use App\Http\Controllers\ProformaApplicationDataController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\File\TemporaryFileController;
use App\Http\Controllers\LevelController;
use App\Http\Controllers\PartnerController;
use Illuminate\Support\Facades\Broadcast;

// Register broadcast authentication routes for Reverb
Broadcast::routes(['middleware' => ['web', 'auth.user']]);

// Load broadcast channel authorization
require __DIR__ . '/channels.php';


use App\Http\Controllers\AccountantController;
use App\Http\Controllers\TempController;
use App\Http\Controllers\ProformaApplicationController;
use App\Http\Controllers\UserBalanceController;
use App\Http\Controllers\WithdrawalController;
use App\Http\Middleware\GarageMiddleware;
use App\Http\Middleware\ShopMiddleware;
use App\Models\Inbox;
use App\Models\Proforma;
use App\Models\ProformaPart;
use App\Models\PartsImages;
use App\Models\ProformaSelection;
use App\Models\User;
use App\Models\BrandUser;
use App\Models\Brand;
use App\Models\Cost;
use App\Models\Commission;
use App\Models\PaidUser;
use App\Models\CarPart;
use App\Models\ProformaApplication;
use App\Models\ProformaInvoice;
use App\Services\AudioService;
use App\Services\ImageService;
use App\Services\TelegramService;
use App\Services\VideoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

use App\Jobs\AutoSelectProformaOffers;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GarageController;
use App\Http\Controllers\BusinessOwnerController;
use App\Http\Controllers\MarketerController;

use App\Http\Controllers\MarketerBusinessController;
use App\Http\Controllers\ProfileController;

use App\Http\Controllers\NotificationController;
use App\Notifications\ProformaApplicationReceived;
use App\Http\Controllers\UserReviewController;

use App\Events\ProformaPublished;
use App\Events\ProformaCreated;


use App\Http\Controllers\LogViewerController;

Route::get('/logs', [LogViewerController::class, 'index']);

Route::get('/logs/fetch', [LogViewerController::class, 'fetchLogs']);

// 🔹 CSRF Token Refresh (prevents 419 errors on long sessions)
Route::get('/csrf-token', function () {
    return response()->json(['token' => csrf_token()]);
});

// 🔹 Public invoice page by SKU (no auth required)
Route::get('/transaction/{sku}', function ($sku) {
    $invoice = \App\Models\ProformaInvoice::where('sku', $sku)->firstOrFail();
    $proforma = $invoice->proforma;

    return view('transaction', compact('invoice', 'proforma'));
});

Route::get('/review', function () {
    $users = User::where('is_test', false)
    ->whereIn('role', ['garage', 'shop'])
    ->get();

    return view('review', compact('users'));
})->name('reviews.form');

Route::post('/reviews/store', [UserReviewController::class, 'store'])
    ->name('reviews.store');


// Helper function to process temporary files
if (!function_exists('processTemporaryFile')) {
function processTemporaryFile($tempFile, $destinationFolder) {
    \Log::info('Upload: processing temp file', [
        'temp_folder' => is_string($tempFile) ? $tempFile : 'NON_STRING',
        'destination' => $destinationFolder,
    ]);
    if (is_string($tempFile)) {
        // If it's a folder name from FilePond
        $tempFileModel = \App\Models\TemporaryFile::where('folder', $tempFile)->first();
        if ($tempFileModel) {
            $tempPath = 'temporary/tmp/' . $tempFile . '/' . $tempFileModel->file;
            $newPath = $destinationFolder . '/' . time() . '_' . $tempFileModel->file;
            
            if (Storage::disk('local')->exists($tempPath)) {
                // Copy file to permanent location
                Storage::disk('public')->put($newPath, Storage::disk('local')->get($tempPath));
                
                // Clean up temporary file
                Storage::disk('local')->deleteDirectory('temporary/tmp/' . $tempFile);
                $tempFileModel->delete();
                
                return $newPath;
            }
        }
    }
    return null;
}
}

Route::post('/upload-part-image', [TempController::class, 'uploadPartImage'])->name('upload.part.image');
Route::delete('/delete-part-image', [TempController::class, 'revert'])->name('upload.part.image.revert');


// Livewire File Upload Demo Route
Route::get('/livewire-file-upload-demo', function () {
    return view('livewire.file-upload-demo');
})->name('livewire.file-upload-demo');

// Enhanced Upload Components Demo Route
Route::get('/upload-demo', function () {
    return view('livewire.upload-demo');
})->name('upload-demo');

// ******************Authentication******************

// Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

// Guest routes (login/signup) - redirect authenticated users
Route::middleware(['guest'])->group(function () {

    // Force logout other devices (accessible from login page when blocked)
    Route::post('/force-logout-other-devices', function (Request $request) {
        $request->validate([
            'email_or_phone' => 'required',
            'password' => 'required',
        ]);

        $input = $request->input('email_or_phone');
        $user = null;

        if (filter_var($input, FILTER_VALIDATE_EMAIL)) {
            $user = User::where('email', $input)->first();
        } else {
            $raw = preg_replace('/[^0-9]/', '', $input);
            $core = $raw;
            if (strlen($raw) >= 10 && substr($raw, 0, 3) === '251') {
                $core = substr($raw, 3);
            } elseif (strlen($raw) >= 10 && substr($raw, 0, 1) === '0') {
                $core = substr($raw, 1);
            }
            $user = User::where(function ($q) use ($core, $raw) {
                $q->where('phone_number', '+251' . $core)
                  ->orWhere('phone_number', '251' . $core)
                  ->orWhere('phone_number', '0' . $core)
                  ->orWhere('phone_number', $core);
            })->first();
        }

        if (!$user || !\Illuminate\Support\Facades\Hash::check($request->input('password'), $user->password)) {
            return back()->withErrors(['email_or_phone' => 'Invalid credentials.'])->withInput();
        }

        // Clear old session from sessions table
        if ($user->session_id) {
            DB::table('sessions')->where('id', $user->session_id)->delete();
        }

        // Reset session_id so the user can log in fresh
        $user->session_id = null;
        $user->save();

        return redirect('/login')->with('success', 'All other devices have been logged out. You can now sign in.');
    })->name('force-logout-other-devices');

    Route::get('/login', function () {
    $brands = array_map(function ($file) {
        return pathinfo($file, PATHINFO_FILENAME);
    }, glob(public_path('assets/images/brands/*.png')));

    return view('authentication.login', compact('brands'));
})->name('login');
Route::post('/login', function (Request $request) {
    // Validate the password field
    $request->validate([
        'password' => 'required|min:6|max:10',
        'email_or_phone' => 'required',
    ]);
    
    $input = $request->input('email_or_phone');
    $credentials = ['password' => $request->input('password')];

    // Email?
    if (filter_var($input, FILTER_VALIDATE_EMAIL)) {
        $credentials['email'] = $input;
    }
    // Phone? — normalize +251 / 251 / 0 prefixes to match any stored format
    else if (preg_match('/^\+?[0-9]{10,15}$/', $input)) {
        // Strip prefix to get core digits (e.g. 912345678)
        $phone = $input;
        if (str_starts_with($phone, '+251')) {
            $core = substr($phone, 4);
        } elseif (str_starts_with($phone, '251') && strlen($phone) >= 12) {
            $core = substr($phone, 3);
        } elseif (str_starts_with($phone, '0')) {
            $core = substr($phone, 1);
        } else {
            $core = $phone;
        }

        // Try to find user with any of the 3 prefix formats
        $user = \App\Models\User::where(function ($q) use ($core) {
            $q->where('phone_number', '+251' . $core)
              ->orWhere('phone_number', '251' . $core)
              ->orWhere('phone_number', '0' . $core)
              ->orWhere('phone_number', $core);
        })->first();

        if ($user && \Illuminate\Support\Facades\Hash::check($request->input('password'), $user->password)) {
            Auth::login($user, $request->has('remember'));
        } else {
            return back()->withErrors(['email_or_phone' => 'Invalid credentials.'])->withInput();
        }
    } else {
        return back()->withErrors(['email_or_phone' => 'The email or phone number is not valid.'])->withInput();
    }

    // For email login, use Auth::attempt; for phone, already logged in above
    if (isset($credentials['email']) && !Auth::attempt($credentials, $request->has('remember'))) {
        return back()->withErrors(['email_or_phone' => 'Invalid credentials.'])->withInput();
    }

    if (Auth::check()) {

        $user = Auth::user();

        // ⭐ Check if user has an active session on another device (spare-part shops only)
        if ($user->role === 'shop' && $user->session_id && $user->session_id !== Session::getId()) {
            $storedSession = DB::table('sessions')
                ->where('id', $user->session_id)
                ->where('user_id', $user->id)
                ->first();

            if ($storedSession) {
                // Check if the old session is from the SAME browser (user cleared cache)
                $currentUserAgent = $request->header('User-Agent', '');
                $oldUserAgent = $storedSession->user_agent ?? '';
                $isSameBrowser = ($currentUserAgent === $oldUserAgent);

                // Check if the old session has expired
                $sessionLifetime = config('session.lifetime', 120) * 60; // in seconds
                $isExpired = (time() - $storedSession->last_activity) > $sessionLifetime;

                if ($isSameBrowser || $isExpired) {
                    // Same browser (cleared cache) or expired session — clean up and allow login
                    DB::table('sessions')->where('id', $user->session_id)->delete();
                    $user->session_id = null;
                    $user->save();
                } else {
                    // Truly different device/browser with an active session — block
                    Auth::logout();
                    return back()->withErrors([
                        'email_or_phone' => 'You are already logged in on another device or browser.'
                    ])->with('session_blocked', true)->withInput();
                }
            } else {
                // The stored session no longer exists — allow login
                $user->session_id = null;
                $user->save();
            }
        }


        // Role access & approval
        if (!$user->approved) {
            // Notify admins (database) that a pending user attempted to log in.
            // Rate limit: once per hour per user.
            try {
                $key = 'pending_approval_login_notified_user_' . $user->id;
                if (!\Illuminate\Support\Facades\Cache::has($key)) {
                    $admins = \App\Models\User::whereIn('role', ['admin', 'superadmin'])
                        ->where('approved', true)
                        ->get();

                    if ($admins->isNotEmpty()) {
                        \Illuminate\Support\Facades\Notification::send(
                            $admins,
                            new \App\Notifications\PendingApprovalLoginAttempt(
                                $user->id,
                                (string) ($user->name ?? 'User'),
                                $user->role,
                                $user->email,
                                $user->phone_number
                            )
                        );
                    }

                    \Illuminate\Support\Facades\Cache::put($key, true, now()->addHour());
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Pending approval login attempt notification failed', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }

            // Send Telegram notification to admins
            try {
                $telegram = new \App\Services\TelegramService();
                $telegram->sendPendingUserLoginNotification(
                    $user->id,
                    (string) ($user->name ?? 'User'),
                    $user->role,
                    $user->email,
                    $user->phone_number
                );
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Pending approval Telegram notification failed', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }

            Auth::logout();
            return back()->withErrors(['email_or_phone' => 'Your account is pending approval. Please wait for admin approval.'])->withInput();
        }

        // ⭐ Store current session ID
        $user->session_id = Session::getId();
        $user->save();
		
        Session::put('last_activity', time());

        // Redirect ALL roles to telegram-connect if not connected
        if (!$user->telegram_chat_id && app(\App\Services\TelegramService::class)->isConfigured()) {
            return redirect('/telegram-connect');
        }

        switch ($user->role) {
            case 'admin':
                return redirect()->intended('/admin');
            case 'superadmin':
                return redirect()->intended('/admin');
            case 'manager':
                return redirect()->intended('/manager/dashboard');
            case 'operator':
                return redirect()->intended('/operator/dashboard');
            case 'insurance':
                return redirect()->intended('/insurance');
            case 'others':
                return redirect()->intended('/business-owner');
            case 'garage':
                return redirect()->intended('/garage/');
            case 'shop':
                return redirect()->intended('/spare-part-shops/');
            case 'marketer':
                return redirect()->intended('/marketer');
            case 'employee':
                return redirect()->intended('/employee');

            // ✅ New Role Added Here
            case 'accountant':
                return redirect()->intended('/finance');

            default:
                return redirect()->intended('/login');
        }
    }

    return back()->withErrors(['email_or_phone' => 'Invalid credentials.'])->withInput();
})->name('login');

    // Signup routes
    Route::get('/signup', [\App\Http\Controllers\RegisterController::class, 'showRegistrationForm'])->name('signup');
    Route::get('/signup/individual', [\App\Http\Controllers\RegisterController::class, 'showIndividualRegistrationForm'])->name('signup.individual');
    Route::get('/signup/business-owner', [\App\Http\Controllers\RegisterController::class, 'showBusinessOwnerRegistrationForm'])->name('signup.business-owner');
    Route::get('/signup/garage-sparepart', [\App\Http\Controllers\RegisterController::class, 'showGarageSparePartRegistrationForm'])->name('signup.garage-sparepart');
});

// CSRF token refresh route (for AJAX requests)
Route::get('/csrf-refresh', function () {
    return response()->json([
        'token' => csrf_token(),
        'timestamp' => now()->toISOString()
    ]);
})->name('csrf.refresh');

// API endpoint for admin dashboard real-time polling
Route::get('/api/admin/proformas', function () {
    if (!auth()->check() || !in_array(auth()->user()->role, ['admin', 'superadmin'])) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    $data = Cache::remember('admin_proformas_data', 10, function () {
        $proformas = \App\Models\Proforma::with('poster')
            ->whereHas('poster')
            ->orderBy('created_at', 'desc')
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
})->middleware('auth.user');

// API endpoint for notification bell polling (all roles)
Route::get('/api/notifications', function () {
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
})->middleware('auth.user');

// Mark notifications as read
Route::post('/api/notifications/read', function () {
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
})->middleware('auth.user');

// Close proforma (admin only)
Route::patch('/admin/proforma/{id}/close', [\App\Http\Controllers\AdminController::class, 'closeProforma'])
    ->name('proforma.close')
    ->middleware('auth.user');

// Telegram connect page (shown after signup)
Route::get('/telegram-connect/{userId}', function ($userId) {
    $user = \App\Models\User::findOrFail($userId);
    $sessionUserId = session('telegram_connect_user_id');
    if (!auth()->check() && (string) $sessionUserId !== (string) $userId) {
        return redirect('/login');
    }

    return response()
        ->view('authentication.telegram-connect', ['user' => $user])
        ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
        ->header('Pragma', 'no-cache')
        ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
})->name('telegram.connect');

// Telegram webhook - receives bot callbacks
Route::post('/telegram/webhook', function (\Illuminate\Http\Request $request) {
    try {
        $data = $request->all();
        // Handle /start command with user ID
        if (isset($data['message']['text']) && str_starts_with($data['message']['text'], '/start ')) {
            $userId = str_replace('/start ', '', $data['message']['text']);
            $chatId = $data['message']['chat']['id'];
            $user = \App\Models\User::find($userId);
            if ($user) {
                // Check if this telegram account is already linked to another user
                $existing = \App\Models\User::where('telegram_chat_id', (string) $chatId)
                    ->where('id', '!=', $userId)
                    ->first();
                if ($existing) {
                    (new \App\Services\TelegramService())->sendMessage($chatId, "You've registered using this account please use other account");
                    return response()->json(['ok' => true]);
                }
                $user->telegram_chat_id = $chatId;
                $user->save();
                (new \App\Services\TelegramService())->sendMessage($chatId, "✅ Connected! You'll now receive notifications from etera.");
            }
        }
        return response()->json(['ok' => true]);
    } catch (\Throwable $e) {
        \Illuminate\Support\Facades\Log::error('Telegram webhook error', ['error' => $e->getMessage()]);
        return response()->json(['ok' => true]); // Always 200 to Telegram
    }
});

// Protected routes - require authentication
Route::middleware(['auth.user'])->group(function () {
    // Profile routes
    Route::get('/profile', function () {
        return view('admin.profile.profile');
    })->name('profile.show');

    Route::put('/profile/update', function (Request $request) {
        $user = Auth::user();

        // Normalize empty strings to null (email can be optional for some roles)
        $request->merge([
            'email' => $request->filled('email') ? $request->email : null,
        ]);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . $user->id,
            'phone_number' => 'nullable|string|max:20',
            'password' => 'nullable|min:6|confirmed',
        ]);

        // Update user details
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'password' => $request->filled('password') ? Hash::make($request->password) : $user->password,
        ]);

        return redirect()->back()->with('success', 'Profile updated successfully!');
    })->name('profile.update');

    // Logout route
    Route::delete('/logout', function (Request $request) {

    $user = Auth::user();

    if ($user) {
        // Clear the stored session ID, allowing new login anywhere
        $user->session_id = null;
        $user->save();
    }

    Auth::logout();

    // Properly invalidate the session so back/refresh won't reuse cached auth pages.
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->to('/login')->with('success', 'You have been successfully logged out.');
})->name('logout');


    // Withdrawal routes
    Route::post('withdraw-requests', [WithdrawalController::class, 'store'])->name('withdraw.store');

    // Profile update routes
    Route::put('/profile/{user}', function (User $user, Request $request) {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'phone_number' => 'required|starts_with:2519,2517|min:12|integer|unique:users,phone_number,'.
                $user->id,
            'current_password' => 'nullable|min:8',
            'password' => 'nullable|confirmed|min:8',
            'tin_number' => 'nullable',
            'brands' => 'nullable',
            'business_license_number' => 'nullable',
            'license_expire_date' => 'nullable',
        ]);

        if (! is_null($request->password)) {
            if (Hash::check($request->current_password, auth()->user()->password)) {
                return redirect()->back();
            }
            auth()
                ->user()
                ->update([
                    'password' => bcrypt($request->password),
                ]);
        }
        if ($request->brands) {
            auth()->user()->brands->each(fn ($brand) => $brand->delete());
            foreach ($request->brands as $brand) {
                BrandUser::updateOrCreate([
                    'brand_id' => $brand,
                    'user_id' => auth()->id(),
                ]);
            }

        }

        auth()
            ->user()
            ->update([
                'name' => $request->name,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
            ]);

        session()->flash('success', 'Profile updated successfully!');

        return redirect()->back();
    })->name('profile.update.user');

    Route::put('/my-profile/update', [ProfileController::class, 'updateSelf'])->name('user.profile.update');
    
    Route::post('/user/bank', [ProfileController::class, 'storeBank'])->name('user.bank.store');
    Route::put('/user/bank/{bank}', [ProfileController::class, 'updateBank'])->name('user.bank.update');



    // Admin and Superadmin routes
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
});



/********************FILE RELATED ROUTES*****************************/
Route::post('upload/{type}', [TemporaryFileController::class, 'store']);
Route::delete('delete', [TemporaryFileController::class, 'destroy']);

// These routes are now handled in the auth.user middleware group above

Route::post('check-password', function (Request $request) {
    $request->validate([
        'password' => 'required|min:8',
        'proforma' => 'required|exists:proformas,id',
    ]);

    if (Hash::check($request->password, auth()->user()->password)) {
        if (auth()->user()?->role == 'insurance') {
            return redirect()->intended(
                '/insurance/proforma-details?proforma_id='.$request->proforma
            );
        } else {
            return redirect()->intended(
                '/business-owner/proforma-details?proforma_id='.$request->proforma
            );
        }
    }

    return back();
});







Route::patch('/proforma/close/{id}', [ProformaController::class, 'closeProforma'])->name('proforma.close');

Route::patch('/proforma/paid/{id}', [ProformaController::class, 'paymentCollected'])->name('proforma.paid');
Route::get('/proforma/{id}/status', [ProformaController::class, 'getStatusSummary'])->name('proforma.status');
Route::post('/proforma/{id}/check-auto-close', [ProformaController::class, 'checkAutoClose'])->name('proforma.check-auto-close');

// Proforma Application Data Routes
Route::prefix('proforma-applications')->group(function () {
    Route::post('/{proformaId}/register', [ProformaApplicationDataController::class, 'registerApplication'])->name('proforma.applications.register');
    Route::get('/{proformaId}/data', [ProformaApplicationDataController::class, 'getApplicationData'])->name('proforma.applications.data');
    Route::get('/{proformaId}/export', [ProformaApplicationDataController::class, 'exportApplicationData'])->name('proforma.applications.export');
    Route::get('/statistics', [ProformaApplicationDataController::class, 'getApplicationStatistics'])->name('proforma.applications.statistics');
    Route::get('/real-time-updates', [ProformaApplicationDataController::class, 'getRealTimeUpdates'])->name('proforma.applications.real-time');
});



Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user();
        if (!$user->approved) {
            Auth::logout();
            return redirect('/login')->with('error', 'Your account is pending approval.');
        }
        if (empty($user->role)) {
            Auth::logout();
            return redirect('/login')->with('error', 'Please login again!');
        }
        return redirect(match ($user->role) {
            'admin', 'superadmin' => '/admin',
            'insurance' => '/insurance',
            'others' => '/business-owner',
            'garage' => '/garage/',
            'shop' => '/spare-part-shops/',
            'marketer' => '/marketer',
            'employee' => '/employee',
            'manager' => '/manager/dashboard',
            'operator' => '/operator/dashboard',
            'accountant' => '/finance',
            default => '/login',
        });
    }
    return redirect()->to('/login');
});

// Route::get('/received-details', function (Request $request) {
//     $proforma = \App\Models\Proforma::findOrFail($request->query('proforma'));
    
//     $applications = $proforma?->applications;


//     // dd($applications);

//     return view('spare-part.received-details', compact('proforma', 'applications'));
// });

Route::get('/received-details', function (Request $request) {
    $proforma = Proforma::with([
        'applications.prices.part',
        'brand'
    ])->findOrFail($request->query('proforma'));

    // Load all invoice rows for this proforma (handles multiple rows and returns)
    $invoices = \App\Models\ProformaInvoice::where('proforma_id', $proforma->id)
        ->orderBy('updated_at', 'desc')
        ->get();

    // Sort applications by actual final price (lowest first)
    $applications = $proforma->applications->sortBy(function($application) {
        if ($application->from === 'shop' && $application->prices->count() > 0) {
            $subtotal = $application->prices->sum('part_total');
            $discountPct = (float)($application->discount ?? 0);
            $discountAmt = ($subtotal * $discountPct) / 100;
            return $subtotal - $discountAmt;
        }
        return $application->amount ?? 0;
    });

    // Limit to requested number for non-Etera Chereta
    $requiredShops = (int) ($proforma->required_number_of_shops ?? 0);
    if ($requiredShops > 0) {
        $applications = $applications->take($requiredShops);
    }

    return view('spare-part.received-details', [
        'proforma' => $proforma,
        'applications' => $applications,
        'invoices' => $invoices,
    ]);
});


// Login route is now handled in the guest middleware group above
// Profile routes are now handled in the auth.user middleware group above
































// Update Profile Route
Route::put('/profile/update', function (Request $request) {
    $user = Auth::user();

    // Normalize empty strings to null (email can be optional for some roles)
    $request->merge([
        'email' => $request->filled('email') ? $request->email : null,
    ]);

    // Validate the request
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'nullable|email|unique:users,email,' . $user->id,
        'phone_number' => 'nullable|string|max:20',
        'password' => 'nullable|min:6|confirmed',
        'stamp_image' => 'nullable|image|mimes:jpg,png,jpeg,gif,svg|max:2048',
        'license_image' => 'nullable|image|mimes:jpg,png,jpeg,gif,svg|max:2048',
    ]);

    // Update user details
    $user->name = $request->name;
    $user->email = $request->email;
    $user->phone_number = $request->phone_number;

    // Handle password update
    if ($request->filled('password')) {
        $user->password = Hash::make($request->password);
    }

    // Handle stamp image upload
    if ($request->hasFile('stamp_image')) {
        // Delete the old stamp image if exists
        if ($user->stamp_image) {
            unlink(storage_path('app/public/stamps/' . basename($user->stamp_image)));
        }

        // Store the new stamp image
        $stampPath = $request->file('stamp_image')->store('stamps', 'public');
        $user->stamp_image = $stampPath;
    }

    // Handle license image upload
    if ($request->hasFile('license_image')) {
        // Delete the old license image if exists
        if ($user->license_image) {
            unlink(storage_path('app/public/licenses/' . basename($user->license_image)));
        }

        // Store the new license image
        $licensePath = $request->file('license_image')->store('licenses', 'public');
        $user->license_image = $licensePath;
    }

    // Save the updated user information
    $user->save();

    // Redirect with success message
    return redirect()->back()->with('success', 'Profile updated successfully!');
})->middleware('auth')->name('profile.update');
































// Show Profile Page
Route::get('/profile', function () {
    return view('admin.profile.profile');
    // Ensure this Blade file exists in resources/views/admin/
})->middleware('auth')->name('profile.show');

// Update Profile
Route::put('/profile/update', function (Request $request) {
    $user = Auth::user();

    // Normalize empty strings to null (email can be optional for some roles)
    $request->merge([
        'email' => $request->filled('email') ? $request->email : null,
    ]);

    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'nullable|email|unique:users,email,' . $user->id,
        'phone_number' => 'nullable|string|max:20',
        'password' => 'nullable|min:6|confirmed',
    ]);

    // Update user details
    $user->name = $request->name;
    $user->email = $request->email;
    $user->phone_number = $request->phone_number;

    if ($request->filled('password')) {
        $user->password = Hash::make($request->password);
    }

    $user->save();

    return redirect()->back()->with('success', 'Profile updated successfully!');
})->middleware('auth')->name('profile.update');





        // Register Users


        Route::get('/signup', [\App\Http\Controllers\RegisterController::class, 'showRegistrationForm'])->name('signup');
        Route::post('/add-register', [\App\Http\Controllers\RegisterController::class, 'store'])->name('add-register');

        // Separate signup routes for different user types
        Route::get('/signup/individual', [\App\Http\Controllers\RegisterController::class, 'showIndividualRegistrationForm'])->name('signup.individual');
        Route::post('/register/individual', [\App\Http\Controllers\RegisterController::class, 'storeIndividual'])->name('register.individual');
        
        Route::get('/signup/business-owner', [\App\Http\Controllers\RegisterController::class, 'showBusinessOwnerRegistrationForm'])->name('signup.business-owner');
        Route::post('/signup/business-owner', [\App\Http\Controllers\RegisterController::class, 'storeBusinessOwner'])->name('register.business-owner');
        
        Route::get('/signup/garage-sparepart', [\App\Http\Controllers\RegisterController::class, 'showGarageSparePartRegistrationForm'])->name('signup.garage-sparepart');
        Route::post('/register/garage-sparepart', [\App\Http\Controllers\RegisterController::class, 'storeGarageSparepart'])->name('register.garage-sparepart');


// Route::get('/signup',    function () {return view('authentication.signup');});

Route::get('/forgot-password', function () {
    return view('authentication.forgot-password');
});

Route::post('/forgot-password', function (Request $request) {
    $request->validate(['email' => 'required|email']);

    $user = User::where('email', $request->email)->first();
    if (!$user) {
        return back()->withErrors(['email' => 'No account found with that email address.'])->withInput();
    }

    // Generate token
    $token = \Illuminate\Support\Str::random(64);

    // Delete any existing tokens for this email
    DB::table('password_reset_tokens')->where('email', $request->email)->delete();

    // Insert new token
    DB::table('password_reset_tokens')->insert([
        'email' => $request->email,
        'token' => Hash::make($token),
        'created_at' => now(),
    ]);

    // Build reset URL
    $resetUrl = url('/reset-password?token=' . $token . '&email=' . urlencode($request->email));

    try {
        if (\App\Models\EmailSetting::isEnabled('password_reset')) {
            \Illuminate\Support\Facades\Mail::to($request->email)->send(new \App\Mail\PasswordResetMail($resetUrl, $user->name));
        }

        // Render email body for logging
        $emailBody = view('emails.password_reset', ['resetUrl' => $resetUrl, 'userName' => $user->name])->render();

        \App\Models\SentEmail::log(
            'password_reset',
            $request->email,
            $user->name,
            $user->id,
            null,
            'Reset Your Password - etera',
            'sent',
            null,
            $emailBody
        );
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Password reset email failed', ['error' => $e->getMessage()]);

        \App\Models\SentEmail::log(
            'password_reset',
            $request->email,
            $user->name,
            $user->id,
            null,
            'Reset Your Password - ETERA',
            'failed',
            $e->getMessage()
        );
    }

    return back()->with('success', 'If an account exists with that email, a password reset link has been sent.');
})->name('password.email');

Route::post('/forgot-password-telegram', function (Request $request) {
	$request->validate([
		'phone_number' => 'required|string|max:20',
	]);

	$rawPhone = (string) $request->phone_number;
	$rawPhone = preg_replace('/\s+/', '', $rawPhone);
	$rawPhone = preg_replace('/[^0-9\+]/', '', $rawPhone);

	$candidates = [];
	$local = null;
	$intl = null;

	if (strpos($rawPhone, '+251') === 0) {
		$rest = substr($rawPhone, 4);
		$local = '0' . $rest;
		$intl = '+251' . $rest;
	} elseif (strpos($rawPhone, '251') === 0) {
		$rest = substr($rawPhone, 3);
		$local = '0' . $rest;
		$intl = '+251' . $rest;
	} elseif (strpos($rawPhone, '0') === 0) {
		$local = $rawPhone;
		$intl = '+251' . substr($rawPhone, 1);
	} else {
		$local = '0' . $rawPhone;
		$intl = '+251' . $rawPhone;
	}

	if (preg_match('/^09\d{8}$/', (string) $local)) {
		$candidates[] = $local;
	}
	if (preg_match('/^\+2519\d{8}$/', (string) $intl)) {
		$candidates[] = $intl;
	}
	$candidates[] = $rawPhone;
	$candidates = array_values(array_unique(array_filter($candidates)));

	$rawNoPlus = ltrim($rawPhone, '+');
	$isRawOk = preg_match('/^09\d{8}$/', $rawPhone)
		|| preg_match('/^\+2519\d{8}$/', $rawPhone)
		|| preg_match('/^2519\d{8}$/', $rawNoPlus);

	$isNormalizedOk = preg_match('/^09\d{8}$/', (string) $local) || preg_match('/^\+2519\d{8}$/', (string) $intl);

	if (! $isRawOk && ! $isNormalizedOk) {
		return back()->withErrors([
			'phone_number' => 'Invalid phone number format. Use +2519XXXXXXXX or 09XXXXXXXX.',
		]);
	}

	$user = User::whereIn('phone_number', $candidates)->first();
	if (! $user || empty($user->telegram_chat_id)) {
		return back()->with('success', 'If an account exists with that phone number and Telegram is connected, a password reset link will be sent via Telegram.');
	}

	$identifier = $user->email ?: $user->phone_number;
	if (empty($identifier)) {
		\Illuminate\Support\Facades\Log::warning('Telegram password reset: user has no email or phone_number identifier', [
			'user_id' => $user->id,
		]);
		return back()->with('success', 'If an account exists with that phone number and Telegram is connected, a password reset link will be sent via Telegram.');
	}

	$token = \Illuminate\Support\Str::random(64);
	DB::table('password_reset_tokens')->where('email', $identifier)->delete();
	DB::table('password_reset_tokens')->insert([
		'email' => $identifier,
		'token' => Hash::make($token),
		'created_at' => now(),
	]);

	$resetUrl = url('/reset-password?token=' . $token . '&email=' . urlencode($identifier));
	$rejectAction = 'pw_reject:' . $identifier;

	try {
		$messageId = null;
		app(TelegramService::class)->sendPasswordResetLink((string) $user->telegram_chat_id, $resetUrl, $rejectAction, true, $messageId);
	} catch (\Throwable $e) {
		\Illuminate\Support\Facades\Log::warning('Telegram password reset send failed', [
			'user_id' => $user->id,
			'error' => $e->getMessage(),
		]);
	}

	return back()->with('success', 'If an account exists with that phone number and Telegram is connected, a password reset link will be sent via Telegram.');
})->name('password.telegram');

Route::get('/reset-password-reject', function (Request $request) {
	$request->validate([
		'token' => 'required',
		'email' => 'required|string',
	]);

	$record = DB::table('password_reset_tokens')->where('email', $request->email)->first();
	if ($record && Hash::check($request->token, $record->token)) {
		DB::table('password_reset_tokens')->where('email', $request->email)->delete();
	}

	return redirect('/login')->with('success', 'Password reset request rejected. If you did not request this, your account is safe.');
})->name('password.reset.reject');

Route::get('/reset-password', function (Request $request) {
    return view('authentication.reset-password', [
        'token' => $request->query('token'),
        'email' => $request->query('email'),
    ]);
});

Route::post('/reset-password', function (Request $request) {
    $request->validate([
        'token' => 'required',
        'email' => 'required|string',
        'password' => 'required|min:6|max:6|confirmed',
    ]);

    $record = DB::table('password_reset_tokens')->where('email', $request->email)->first();

    if (!$record || !Hash::check($request->token, $record->token)) {
        return back()->withErrors(['email' => 'Invalid or expired reset token.']);
    }

    // Check if token is older than 5 minutes
    if (now()->diffInMinutes($record->created_at) > 5) {
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();
        return back()->withErrors(['email' => 'This reset link has expired. Please request a new one.']);
    }

    // Update password
    $user = User::where('email', $request->email)->first();
    if (!$user) {
        $user = User::where('phone_number', $request->email)->first();
    }
    if (!$user) {
        return back()->withErrors(['email' => 'User not found.']);
    }

    $user->update(['password' => Hash::make($request->password)]);

    // Delete used token
    DB::table('password_reset_tokens')->where('email', $request->email)->delete();

    return redirect('/login')->with('success', 'Your password has been reset successfully. You can now log in.');
})->name('password.reset');

// ==========================================
// OTP Verification Routes
// ==========================================
Route::get('/verify-otp', function () {
    if (!session('otp_email')) {
        return redirect('/login');
    }
    return view('authentication.verify-otp');
})->name('verify-otp');

Route::post('/verify-otp', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'otp' => 'required|string|size:6',
    ]);

    $otpRecord = DB::table('email_otps')
        ->where('email', $request->email)
        ->where('type', 'signup')
        ->where('expires_at', '>', now())
        ->latest()
        ->first();

    if (!$otpRecord || $otpRecord->otp !== $request->otp) {
        return back()->withErrors(['otp' => 'Invalid or expired OTP code. Please try again.'])
            ->with('otp_email', $request->email);
    }

    // Mark email as verified
    User::where('email', $request->email)->update(['email_verified_at' => now()]);

    // Clean up OTP
    DB::table('email_otps')->where('email', $request->email)->delete();

    return redirect('/login')->with('success', 'Email verified successfully! You can now log in once your account is approved.');
})->name('verify-otp.submit');

Route::post('/resend-otp', function (Request $request) {
    $request->validate(['email' => 'required|email']);

    $user = User::where('email', $request->email)->first();
    if (!$user) {
        return back()->withErrors(['email' => 'User not found.'])->with('otp_email', $request->email);
    }

    // Delete old OTPs
    DB::table('email_otps')->where('email', $request->email)->delete();

    // Generate new OTP
    $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

    DB::table('email_otps')->insert([
        'email' => $request->email,
        'otp' => $otp,
        'type' => 'signup',
        'expires_at' => now()->addMinutes(10),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    try {
        if (\App\Models\EmailSetting::isEnabled('email_otp')) {
            \Illuminate\Support\Facades\Mail::to($request->email)->send(new \App\Mail\EmailOtpMail($otp, $user->name));
        }

        $emailBody = view('emails.email_otp', ['otp' => $otp, 'userName' => $user->name])->render();

        \App\Models\SentEmail::log(
            'email_otp',
            $request->email,
            $user->name,
            $user->id,
            null,
            'Your Email Verification Code - etera',
            'sent',
            null,
            $emailBody
        );
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('OTP resend failed', ['error' => $e->getMessage()]);

        \App\Models\SentEmail::log(
            'email_otp',
            $request->email,
            $user->name,
            $user->id,
            null,
            'Your Email Verification Code - ETERA',
            'failed',
            $e->getMessage()
        );
    }

    return back()->with('success', 'A new OTP has been sent to your email.')
        ->with('otp_email', $request->email);
})->name('resend-otp');

// ==========================================
// My Applications Route (Garage & Shop)
// ==========================================
Route::get('/my-applications', function () {
    $user = auth()->user();
    if (!$user || !in_array($user->role, ['garage', 'shop'])) {
        return redirect('/');
    }

    $applications = \App\Models\ProformaApplication::with(['proforma.brand', 'proforma.parts', 'prices'])
        ->where('application_by', $user->id)
        ->orderBy('created_at', 'desc')
        ->get();

    // Count by proforma status
    $pendingCount = $applications->filter(fn($app) => in_array(optional($app->proforma)->status, ['pending', 'opened', 'published']))->count();
    $closedCount = $applications->filter(fn($app) => optional($app->proforma)->status === 'closed')->count();
    $completedCount = $applications->filter(fn($app) => optional($app->proforma)->status === 'completed')->count();
    $totalCount = $applications->count();

    return view('spare-part.my-applications', compact('applications', 'pendingCount', 'closedCount', 'completedCount', 'totalCount'));
})->middleware('auth')->name('my-applications');

// Root routes for spare-part-shops and garage dashboards (my-applications as dashboard)
Route::get('/spare-part-shops', function () {
    $user = auth()->user();
    if (!$user || $user->role !== 'shop') {
        return redirect('/');
    }

    $applications = \App\Models\ProformaApplication::with(['proforma.brand', 'proforma.parts', 'prices'])
        ->where('application_by', $user->id)
        ->orderBy('created_at', 'desc')
        ->get();

    $pendingCount = $applications->filter(fn($app) => in_array(optional($app->proforma)->status, ['pending', 'opened', 'published']))->count();
    $closedCount = $applications->filter(fn($app) => optional($app->proforma)->status === 'closed')->count();
    $completedCount = $applications->filter(fn($app) => optional($app->proforma)->status === 'completed')->count();
    $totalCount = $applications->count();

    return view('spare-part.my-applications', compact('applications', 'pendingCount', 'closedCount', 'completedCount', 'totalCount'));
})->middleware('auth')->name('spare-part-shops.dashboard');

Route::get('/garage', function () {
    $user = auth()->user();
    if (!$user || $user->role !== 'garage') {
        return redirect('/');
    }

    $applications = \App\Models\ProformaApplication::with(['proforma.brand', 'proforma.parts', 'prices'])
        ->where('application_by', $user->id)
        ->orderBy('created_at', 'desc')
        ->get();

    $pendingCount = $applications->filter(fn($app) => in_array(optional($app->proforma)->status, ['pending', 'opened', 'published']))->count();
    $closedCount = $applications->filter(fn($app) => optional($app->proforma)->status === 'closed')->count();
    $completedCount = $applications->filter(fn($app) => optional($app->proforma)->status === 'completed')->count();
    $totalCount = $applications->count();

    return view('spare-part.my-applications', compact('applications', 'pendingCount', 'closedCount', 'completedCount', 'totalCount'));
})->middleware('auth')->name('garage.dashboard');

Route::get('/float', function (Request $request) {
    // Require Telegram connection for non-superadmin admins
    $user = auth()->user();
    if ($user->role === 'admin' && !$user->is_superadmin && empty($user->telegram_chat_id)) {
        return redirect('/telegram-connect')->with('error', 'Please connect your Telegram before you process a file!');
    }

    $proforma = \App\Models\Proforma::find($request->query('proforma_id'));
    if (! $proforma || $proforma?->status != 'pending') {
        return redirect()->back();
    }

    $proforma->update(['status' => 'published', 'processed_by' => auth()->id()]);

    // Log Activity
    \App\Models\ProformaActivityLog::create([
        'proforma_id' => $proforma->id,
        'user_id' => auth()->id(),
        'action' => 'floated',
        'details' => 'Proforma floated (published) by ' . auth()->user()->name,
    ]);

    // 🔥 Fire Event
    event(new ProformaPublished($proforma));

    return redirect()->back();
});

// ******************Admin Side******************

Route::prefix('/admin')
    ->middleware([\App\Http\Middleware\AdminMiddleware::class])
    ->group(function () {
        // Approve newly registered users
        Route::put('/users/{id}/approve', function ($id) {
            $user = \App\Models\User::findOrFail($id);
            $user->update([
                'approved' => true,
                'approved_at' => now(),
            ]);

            return redirect()->back()->with('success', 'User approved successfully');
        });
        
         Route::get('/transactions', [\App\Http\Controllers\TransactionController::class, 'index'])->name('admin.transactions.index');
        
         // 📋 Proforma Statuses
         Route::get('/proforma-statuses', function (\Illuminate\Http\Request $request) {
             $proformas = \App\Models\Proforma::with(['brand', 'processedBy'])
                 ->whereNotNull('processed_by')
                 ->orderBy('updated_at', 'desc')
                 ->get();

             $admins = \App\Models\User::whereIn('role', ['admin', 'superadmin'])->orderBy('name')->get();

             return view('admin.proforma.statuses', compact('proformas', 'admins'));
         })->name('admin.proforma-statuses');
        
         // 📧 Sent Emails Log
         Route::get('/sent-emails', function (\Illuminate\Http\Request $request) {
             $query = \App\Models\SentEmail::with(['user', 'proforma'])->orderBy('created_at', 'desc');

             if ($request->filled('type')) {
                 $query->where('type', $request->type);
             }
             if ($request->filled('status')) {
                 $query->where('status', $request->status);
             }
             if ($request->filled('search')) {
                 $s = $request->search;
                 $query->where(function ($q) use ($s) {
                     $q->where('to_email', 'like', "%{$s}%")
                       ->orWhere('to_name', 'like', "%{$s}%")
                       ->orWhere('subject', 'like', "%{$s}%")
                       ->orWhereHas('proforma', fn($pq) => $pq->where('file_number', 'like', "%{$s}%"));
                 });
             }

             $emails = $query->paginate(25);

             $stats = [
                 'total' => \App\Models\SentEmail::count(),
                 'sent' => \App\Models\SentEmail::where('status', 'sent')->count(),
                 'failed' => \App\Models\SentEmail::where('status', 'failed')->count(),
                 'today' => \App\Models\SentEmail::whereDate('created_at', today())->count(),
             ];

             return view('admin.sent-emails', compact('emails', 'stats'));
         })->name('admin.sent-emails');
        
           // ⭐ View ratings (ADMIN ONLY)
        Route::get('/ratings', [UserReviewController::class, 'index'])
            ->name('admin.ratings.index');
        // Dashboard

        // Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard'); // Redirects to dashboard

        Route::get('/users/chart', function () {
            $users = \App\Models\User::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
                ->where('created_at', '>=', now()->subDays(7))
                ->groupBy('date')
                ->orderBy('date', 'ASC')
                ->get();

            return response()->json([
                'categories' => $users->pluck('date'),
                'data' => $users->pluck('count'),
            ]);
        });

        Route::get('/', function () {
            return view('admin.index');
        })->name('admin.dashboard');

        // Create a new admin user
        Route::post('/create-admin', function (Request $request) {
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

            return redirect()->back()->with('admin_created', 'Admin "' . $user->name . '" created successfully! Default password: 123456');
        })->name('admin.create-admin');

        // Proforma timeline API
        Route::get('/proforma/{id}/timeline', function ($id) {
            $proforma = \App\Models\Proforma::with(['poster', 'processedBy', 'activityLogs.user'])->findOrFail($id);

            $timeline = [];

            // Created
            $timeline[] = [
                'action' => 'Created',
                'date' => $proforma->created_at?->format('d M Y, h:i A'),
                'user' => $proforma->poster?->name ?? 'Unknown',
                'icon' => 'bx-file',
                'color' => '#6c757d',
            ];

            // Activity logs
            foreach ($proforma->activityLogs->sortBy('created_at') as $log) {
                $timeline[] = [
                    'action' => ucfirst($log->action),
                    'date' => $log->created_at?->format('d M Y, h:i A'),
                    'user' => $log->user?->name ?? 'System',
                    'details' => $log->details,
                    'icon' => match(strtolower($log->action)) {
                        'floated' => 'bx-send',
                        'closed' => 'bx-lock',
                        'completed' => 'bx-check-circle',
                        'rejected' => 'bx-x-circle',
                        'sent_to_owner' => 'bx-share',
                        default => 'bx-right-arrow-alt',
                    },
                    'color' => match(strtolower($log->action)) {
                        'floated' => '#0dcaf0',
                        'closed' => '#dc3545',
                        'completed' => '#198754',
                        'rejected' => '#dc3545',
                        'sent_to_owner' => '#0d6efd',
                        default => '#6c757d',
                    },
                ];
            }

            // Current status if not reflected in logs
            $timeline[] = [
                'action' => 'Current Status: ' . ucfirst(str_replace('_', ' ', $proforma->status)),
                'date' => $proforma->updated_at?->format('d M Y, h:i A'),
                'user' => $proforma->processedBy?->name ?? 'System',
                'icon' => 'bx-flag',
                'color' => '#ffc107',
                'is_current' => true,
            ];

            return response()->json([
                'file_number' => $proforma->file_number,
                'customer_name' => $proforma->customer_name,
                'brand' => $proforma->brand?->name ?? 'N/A',
                'model' => $proforma->model,
                'year' => $proforma->year,
                'timeline' => $timeline,
            ]);
        })->name('admin.proforma.timeline');

        Route::get('/profile', function () {
            return view('admin.profile.profile');
        });




Route::get('/verify/{proforma}', function (Proforma $proforma) {

    // ❗ Prevent double verification
    if ($proforma->status === 'completed') {
        return redirect()->back()->with('error', 'Proforma already verified.');
    }

    DB::beginTransaction();

    try {

        Log::info('Verification started', ['proforma_id' => $proforma->id]);

	if(!$proforma->brand->is_test){
        $latestCost = Cost::latest()->first();
        if (!$latestCost) {
            throw new Exception('Cost data not found');
        }

        $vatRate = 0.15;
        $rows = [];

        $requiredShops   = (int) ($proforma->required_number_of_shops ?? 0);
        $requiredGarages = (int) ($proforma->required_number_of_garages ?? 0);

        // 🔹 Determine proforma type
        // Explicit insurance subtypes (set via proforma_type column) always use insurance billing
        if ($proforma->proforma_type && str_starts_with($proforma->proforma_type, 'insurance_')) {
            $type = 'insurance';
        } elseif ($requiredShops > 0 && $requiredGarages == 0) {
            $type = 'regular';
        } elseif ($requiredShops == 3 && $requiredGarages == 3) {
            $type = 'insurance';
        } elseif ($requiredShops == 0 && $requiredGarages == 0) {
            $type = 'etera_chereta';
        } else {
            throw new Exception('Unknown proforma type');
        }

        Log::info('Proforma type determined', ['type' => $type]);

        /*
        |--------------------------------------------------------------------------
        | 🔹 REGULAR PROFORMA (FIXED PRICE BUG)
        |--------------------------------------------------------------------------
        */
        if ($type === 'regular') {

            $applications = ProformaApplication::where('proforma_id', $proforma->id)->get();
            $count = $applications->count();

            // Dynamic pricing from Cost table (EX: 1_proforma_cost, 2_proforma_cost ...)
            $field = "{$count}_proforma_cost";
            $totalAmount = (float) ($latestCost->$field ?? 0);

            if ($totalAmount <= 0) {
                throw new Exception("Invalid regular proforma cost for {$count} applications");
            }

            $unitPrice = $totalAmount / (1 + $vatRate);
            $vatAmount = $totalAmount - $unitPrice;

            $rows[] = [
                'proforma_id'     => $proforma->id,
                'type'            => 'regular',
                'requested_count' => $count,
                'unit_price'      => $unitPrice,
                'vat_rate'        => $vatRate * 100,
                'vat_amount'      => $vatAmount,
                'total_amount'    => $totalAmount,
                'created_by'      => Auth::id(),
                'created_at'      => now(),
                'updated_at'      => now(),
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | 🔹 INSURANCE PROFORMA
        |--------------------------------------------------------------------------
        */
        elseif ($type === 'insurance') {

            $insuranceTotal = (float) (
                $proforma->insured
                    ? ($latestCost->insured_cost ?? 0)
                    : ($latestCost->insurance_proforma ?? 0)
            );

            if ($insuranceTotal <= 0) {
                throw new Exception('Invalid insurance cost');
            }

            $unitPrice = $insuranceTotal / (1 + $vatRate);
            $vatAmount = $insuranceTotal - $unitPrice;

            $rows[] = [
                'proforma_id'     => $proforma->id,
                'type'            => 'insurance',
                'requested_count' => ($requiredShops + $requiredGarages) ?: 6,
                'unit_price'      => $unitPrice,
                'vat_rate'        => $vatRate * 100,
                'vat_amount'      => $vatAmount,
                'total_amount'    => $insuranceTotal,
                'is_paid'         => !$proforma->insured,
                'created_by'      => Auth::id(),
                'created_at'      => now(),
                'updated_at'      => now(),
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | 🔹 ETERA CHERETA
        |--------------------------------------------------------------------------
        */
        elseif ($type === 'etera_chereta') {

            $total = (float) ($latestCost->etera_chereta_cost ?? 0);
            if ($total <= 0) {
                throw new Exception('Invalid Etera Chereta cost');
            }

            $unit = $total / (1 + $vatRate);
            $vatAmount = $total - $unit;

            $rows[] = [
                'proforma_id'  => $proforma->id,
                'type'         => 'etera_chereta',
                'requested_count' => 1,
                'unit_price'   => 0,
                'hourly_price' => $unit,
                'hours'        => 1,
                'vat_rate'     => $vatRate * 100,
                'vat_amount'   => $vatAmount,
                'total_amount' => $total,
                'created_by'   => Auth::id(),
                'created_at'   => now(),
                'updated_at'   => now(),
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | 🔹 SAVE INVOICE
        |--------------------------------------------------------------------------
        */
        ProformaInvoice::where('proforma_id', $proforma->id)->delete();
        $savedInvoices = [];
        foreach ($rows as $row) {
            $savedInvoices[] = ProformaInvoice::create($row);
        }

        /*
        |--------------------------------------------------------------------------
        | 🔹 SEND INVOICE LINK EMAIL
        |--------------------------------------------------------------------------
        */
        if (!empty($savedInvoices)) {
            $invoice = $savedInvoices[0];
            $recipientEmail = $proforma->customer_email ?? $proforma->poster?->email;

            if ($recipientEmail && \App\Models\EmailSetting::isEnabled('proforma_completed')) {
                try {
                    $invoiceUrl = url("/transaction/{$invoice->sku}");

                    \Illuminate\Support\Facades\Mail::raw(
                        "etera – Your Proforma is Complete!\n\n" .
                        "Dear Customer,\n\n" .
                        "Your proforma #{$proforma->file_number} has been completed successfully.\n\n" .
                        "You can login and view the PIs ,\n\n" .
                        "Your invoice (SKU: {$invoice->sku}) is now available.\n" .
                        "View your full invoice here: {$invoiceUrl}\n\n" .
                        "Thank you for using etera!",
                        function ($message) use ($proforma, $recipientEmail, $invoice) {
                            $message->to($recipientEmail)
                                    ->subject("etera – Invoice for Proforma #{$proforma->file_number} (SKU: {$invoice->sku})");
                        }
                    );

                    \App\Models\SentEmail::log(
                        'proforma_completed',
                        $recipientEmail,
                        $proforma->customer_name,
                        $proforma->poster?->id,
                        $proforma->id,
                        "etera – Invoice for Proforma #{$proforma->file_number}",
                        'sent'
                    );
                } catch (\Throwable $emailEx) {
                    Log::warning('Failed to send invoice link email', [
                        'proforma_id' => $proforma->id,
                        'email' => $recipientEmail,
                        'error' => $emailEx->getMessage(),
                    ]);
                    try {
                        \App\Models\SentEmail::log(
                            'proforma_completed',
                            $recipientEmail,
                            $proforma->customer_name,
                            $proforma->poster?->id,
                            $proforma->id,
                            "etera – Invoice for Proforma #{$proforma->file_number}",
                            'failed',
                            $emailEx->getMessage()
                        );
                    } catch (\Throwable $logEx) {}
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 🔹 DEBIT POSTER WALLET (INVOICE)
        |--------------------------------------------------------------------------
        */
        $invoiceTotal = $rows[0]['total_amount'] ?? 0;

        if ($invoiceTotal > 0) {
            (new \App\Services\WalletService())->processTransaction(
                $proforma->poster,
                $invoiceTotal,
                'invoice',
                'Invoice for Proforma #' . $proforma->id,
                $proforma
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 🔹 COMMISSIONS
        |--------------------------------------------------------------------------
        */
        $commissions = Commission::first();

        // 🔹 Insurance poster commission (NOT insured)
        if ($type === 'insurance' && !$proforma->insured) {
            addCommissionRecord(
                $proforma->poster,
                $proforma->id,
                null,
                $commissions->insurancePay ?? 0
            );
        }

        // 🔹 Applicant commissions (INSURANCE ONLY)
        if ($type === 'insurance') {
        $applications = ProformaApplication::where('proforma_id', $proforma->id)->get();

        foreach ($applications as $application) {
            $user = $application->applicationBy;
                $amount = $user->role === 'garage'
                    ? ($commissions->garagePay ?? 0)
                    : ($commissions->shopPay ?? 0);

            if ($amount > 0) {
                addCommissionRecord(
                    $user,
                    $proforma->id,
                    $application->id,
                    $amount
                );
            }
        }
        }else{
             $applications = ProformaApplication::where('proforma_id', $proforma->id)->get();

        foreach ($applications as $application) {
            $user = $application->applicationBy;
                $amount = ($commissions->othersPay ?? 0);

            if ($amount > 0) {
                addCommissionRecord(
                    $user,
                    $proforma->id,
                    $application->id,
                    $amount
                );
            }
       
        }
        }

        // 🔹 Operator commission (ALL TYPES)
        $selection = \App\Models\ProformaSelection::where('proforma_id', $proforma->id)
            ->where('active', true)
            ->first();

        if ($selection) {
            $operator = $selection->operator ?? \App\Models\User::find($selection->employee_id);
            if ($operator) {
                $amount = $operator->commission_per_file ?? ($commissions->operatorPay ?? 0);
                if ($amount > 0) {
                    addCommissionRecord($operator, $proforma->id, null, $amount);
                    $selection->update(['commission_earned' => $amount]);
                }
            }
        }
	}

        /*
        |--------------------------------------------------------------------------
        | 🔹 FINALIZE
        |--------------------------------------------------------------------------
        */
        $proforma->update(['status' => 'completed']);
        $proforma->verify();

        DB::commit();

        // Send database notification (bell icon) to poster
        try {
            if ($proforma->poster) {
                $proforma->poster->notify(
                    new \App\Notifications\ProformaSentToOwnerNotification($proforma)
                );
            }
        } catch (\Throwable $e) {
            Log::warning('Verify: bell notification failed', ['error' => $e->getMessage()]);
        }

        // Send Telegram notification to poster
        try {
            if ($proforma->poster && !empty($proforma->poster->telegram_chat_id)) {
                $invoiceUrl = !empty($savedInvoices) ? url("/transaction/{$savedInvoices[0]->sku}") : '';
                (new \App\Services\TelegramService())->sendSentToOwnerNotification(
                    $proforma->poster->telegram_chat_id,
                    $proforma,
                    $invoiceUrl
                );
            }
        } catch (\Throwable $e) {
            Log::warning('Verify: Telegram notification failed', [
                'proforma_id' => $proforma->id,
                'error' => $e->getMessage(),
            ]);
        }

        return redirect()->back()->with('success', 'Proforma verified successfully.');

    } catch (\Throwable $e) {

        DB::rollBack();

        Log::error('Verification failed', [
            'error' => $e->getMessage(),
            'line'  => $e->getLine()
        ]);

        return redirect()->back()->with('error', 'Verification failed.');
    }
});


/**
 * 🔹 Helper function to create PaidUser commission records
 */
 
 if (!function_exists('addCommissionRecord')) {
 
function addCommissionRecord($user, $proformaId, $applicationId, $amount)
{
    $role = $user->role; // 'shop', 'garage', or 'insurance'

    // 1. Create PaidUser record (Legacy/Work Log)
    $record = PaidUser::create([
        'user_id'       => $user->id,
        'proforma_id'   => $proformaId,
        'application_id'=> $applicationId,
        'amount'        => $amount,
        'is_paid'       => false,
        'paid_at'       => null,
    ]);

    Log::info('PaidUser record created', [
        'user_id' => $user->id,
        'role' => $role,
        'amount' => $amount,
        'proforma_id' => $proformaId,
        'application_id' => $applicationId,
    ]);

    // 2. Create Transaction (Ledger)
    // Commission is "Money In" (Credit) for the user
    $walletService = new \App\Services\WalletService();
    $walletService->processTransaction(
        $user,
        -$amount,
        'commission',
        'Commission for Proforma #' . $proformaId,
        $record
    );

    return $record;
}

}



        // View Proforma
        // admin proformas
        Route::get('/proforma', function () {
            $proformas = \App\Models\Proforma::fromInsurances()
                ->orderBy('created_at', 'desc')
                ->get();

                // dd($proformas);

            return view('admin.proforma.view', compact('proformas'));
        })->name('admin.proformas.index');

        // Post Proforma
        Route::get('/post-proforma', function (Request $request) {
            $proforma = \App\Models\Proforma::find(
                $request->query('proforma_id')
            );
            if (! $proforma) {
                return redirect()->back();
            }

            return view('admin.proforma.post', compact('proforma'));
        });

        Route::get('/others-proforma', function () {
            $proformas = \App\Models\Proforma::fromOthers()
                ->orderBy('created_at', 'desc')
                ->get();

            return view(
                'admin.proforma.others-proforma.view',
                compact('proformas')
            );
        });

        Route::get('/post-others-proforma', function () {
            return view('admin.proforma.others-proforma.post');
        });

        // View Garage Bid
        Route::get('/bid', function () {
            return view('admin.bid.view');
        });

        // View Admins (superadmin only)
        Route::get('/admins', function () {
            $admins = \App\Models\User::whereIn('role', ['admin', 'superadmin'])->get();

            return view('admin.users.admins.view', [
                'admins' => $admins,
            ]);
        })->name('admin.admins.index');

        // Update Admin (superadmin only - no password change allowed)
        Route::put('/admins/{id}', function (Request $request, $id) {
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

            return redirect()->back()->with('success', 'Admin updated successfully!');
        })->name('admin.admins.update');

        // Delete Admin (superadmin only)
        Route::delete('/admins/{id}', function ($id) {
            if (auth()->user()->role !== 'superadmin') {
                abort(403);
            }
            $admin = \App\Models\User::findOrFail($id);
            if ($admin->role === 'superadmin' || $admin->id === auth()->id()) {
                return redirect()->back()->withErrors(['error' => 'Cannot delete this admin.']);
            }
            $admin->delete();
            return redirect()->back()->with('success', 'Admin deleted successfully!');
        })->name('admin.admins.destroy');

        // View Insurances
        Route::get('/insurances', function () {
            $insurances = \App\Models\User::where('role', 'insurance')->get();

            return view('admin.users.insurances.view', [
                'insurances' => $insurances,
            ]);
        })->name('admin.insurances.index');








        

        // Add Insurance
        Route::get('/add-insurance', function () {
            return view('admin.users.insurances.add');
        })->name('admin.insurances.create');

        


        // Add Insurance
        Route::post('/add-insurance', [
            \App\Http\Controllers\InsuranceController::class,
            'store',
        ])->name('add-insurance');
        Route::get('/edit-insurance/{id}', [
            \App\Http\Controllers\InsuranceController::class,
            'edit',
        ])->name('edit-insurance');
        
        Route::put('/update-insurance/{id}', [
            \App\Http\Controllers\InsuranceController::class,
            'update',
        ])->name('update-insurance');
        
        Route::post('/delete-insurance/{id}', [
            \App\Http\Controllers\InsuranceController::class,
            'destroy',
        ])->name('delete-insurance');
        




        
        // View Garage
        Route::get('/garages', function () {
            $garages = \App\Models\User::where('role', 'garage')->get();

            return view('admin.users.garages.view', [
                'garages' => $garages,
            ]);
        });

        // Add Garage
        Route::get('/add-garage', function () {
            return view('admin.users.garages.add');
        });


        Route::post('/add-garage', [
            \App\Http\Controllers\GarageController::class,
            'store',
        ])->name('add-garage');

// adding garages for the admin

        Route::get('/edit-garage/{id}', [
            \App\Http\Controllers\GarageController::class,
            'edit',
            
        ])->name('edit-garage');



        Route::put('/update-garage/{id}', [
            \App\Http\Controllers\GarageController::class,
            'update',
        ])->name('update-garage');

        Route::post('/delete-garage/{id}', [
            \App\Http\Controllers\GarageController::class,
            'destroy',
        ])->name('delete-garage');


// Edit Business Owner (GET)
Route::get('/business-owners/{id}/edit', [BusinessOwnerController::class, 'edit'])
    ->name('admin.business-owners.edit');  // Ensure this is plural

// Update Business Owner (PUT)
Route::put('/business-owners/{id}', [BusinessOwnerController::class, 'update'])
    ->name('admin.business-owners.update');

// Delete Business Owner (DELETE)
Route::POST('/business-owners/{id}', [BusinessOwnerController::class, 'destroy'])
    ->name('admin.business-owners.destroy');



    // Marketers

// Edit Marketer (GET)
Route::get('/admin/marketers/{id}/edit', [MarketerController::class, 'edit'])
    ->name('admin.users.marketers.edit');

// Update Marketer (PUT)
Route::put('/admin/marketers/{id}', [MarketerController::class, 'update'])
    ->name('admin.users.marketers.update');

// Delete Marketer (DELETE)
Route::post('/admin/marketers/{id}', [MarketerController::class, 'destroy'])
    ->name('admin.users.marketers.destroy');



        Route::get('/business-owners', function () {
            $businessOwners = \App\Models\User::where(
                'role',
                'others'
            )->get();

            return view('admin.users.business-owners.view', [
                'businessOwners' => $businessOwners,
            ]);
        });

        Route::get('/add-business-owner', function () {
            return view('admin.users.business-owners.add');
        });


        Route::post('/add-business-owner', [
            \App\Http\Controllers\BusinessOwnerController::class,
            'store',
        ])->name('add-busines-owner');



            // business owners add 

// In routes/web.php

            // Route::get('/edit-business-owner/{id}', [
            //     \App\Http\Controllers\BusinessOwnerController::class,
            //     'edit',
            // ])->name('edit-business-owner');

            // Route::put('/update-business-owner/{id}', [
            //     \App\Http\Controllers\BusinessOwnerController::class,
            //     'update',
            // ])->name('update-business-owner');

            // Route::post('/delete-business-owner/{id}', [
            //     \App\Http\Controllers\BusinessOwnerController::class,
            //     'destroy',
            // ])->name('delete-business-owner');




            // marketers route


        // Add Business Owner
        Route::get('/add-marketer', function () {
            return view('admin.users.marketers.add');
        });
        Route::get('/marketers', function () {
            $marketers = \App\Models\User::where('role', 'marketer')->get();

            return view('admin.users.marketers.view', [
                'marketers' => $marketers,
            ]);
        });
        // View Spare Part Shops
        Route::get('/spare-part-shops', function () {
            $shops = \App\Models\User::where('role', 'shop')->get();

            // dd($shops);


            return view('admin.users.spare-part-shops.view', [
                'shops' => $shops,
            ]);
        });
        // Add Insurance
        Route::post('/add-shop', [
            \App\Http\Controllers\ShopController::class,
            'store',
        ])->name('add-shop');

        Route::get('/edit-shop/{id}', [
            \App\Http\Controllers\ShopController::class,
            'edit',
            
        ])->name('edit-shop');


        
        Route::put('/update-shop/{id}', [
            \App\Http\Controllers\ShopController::class,
            'update',
        ])->name('update-shop');

        Route::post('/delete-shop/{id}', [
            \App\Http\Controllers\ShopController::class,
            'destroy',
        ])->name('delete-shop');


        // spare part shops

        // Add Spare Part Shop
        Route::get('/add-spare-part-shop', function () {
            $brands = \App\Models\Brand::latest()->get();

            return view('admin.users.spare-part-shops.add', [
                'brands' => $brands,
            ]);
        });
           // Employee Routes
        Route::get('/employees', [EmployeeController::class, 'index'])->name(
            'admin.employees.index'
        );
        Route::get('/add-employee', [EmployeeController::class, 'create'])->name(
            'admin.employees.create'
        );
        Route::post('/add-employee', [EmployeeController::class, 'store'])->name(
            'admin.employees.store'
        );

        Route::prefix('employees')->name('admin.employees.')->group(function () {
            Route::get('/{id}/edit', [EmployeeController::class, 'edit'])
                ->name('edit');
            Route::put('/{id}', [EmployeeController::class, 'update'])
                ->name('update');
            Route::delete('/{id}', [EmployeeController::class, 'destroy'])
                ->name('destroy');
            Route::post('/{id}/assign-files', [EmployeeController::class, 'assignFiles'])
                ->name('assign-files');
        });

        // =====================
        // Operator Management Routes
        // =====================
        Route::prefix('operators')->name('admin.operators.')->group(function () {
            Route::get('/', [\App\Http\Controllers\AdminController::class, 'listOperators'])
                ->name('index');
            Route::post('/{operator}/assign-manager', [\App\Http\Controllers\AdminController::class, 'assignOperatorToManager'])
                ->name('assign-manager');
            Route::post('/{operator}/set-quota', [\App\Http\Controllers\AdminController::class, 'setOperatorQuota'])
                ->name('set-quota');
            Route::post('/{operator}/set-commission', [\App\Http\Controllers\AdminController::class, 'setOperatorCommission'])
                ->name('set-commission');
        });

        // =====================
        // Commission Overview Route
        // =====================
        Route::get('/commissions', [\App\Http\Controllers\AdminController::class, 'viewAllCommissions'])
            ->name('admin.commissions.index');
        


        Route::get('/edit-employee/{employeeId}', function ($employeeId) {
            return Livewire::mount(EditEmployee::class, ['employeeId' => $employeeId]);
        })->name('admin.employees.edit-employee');
        
        Route::post('/employees/{id}/assign-manager', [\App\Http\Controllers\EmployeeController::class, 'assignManager'])
            ->name('admin.employees.assign-manager');
        

// admin car parts





// Route::get('/parts/{id}', function ($id) {
//     $carPart = CarPart::findOrFail($id);  // Get the car part by ID
//     return view('admin.parts.edit', ['carPart' => $carPart]);  // Pass car part to the view
// })->name('parts');  // Name the route 'parts'




// Route::put('/parts/{id}', function ($id) {
//     $carPart = CarPart::findOrFail($id);
//     $carPart->update(request()->only('name')); // Update the part (add validation and sanitization)
//     // return redirect()->route('admin.parts.view', $carPart->id);
//     return redirect()->to('/admin/parts');

// })->name('parts.update');



// // Delete Route (Handle delete action)
// Route::delete('/parts/{id}', function ($id) {
//     $carPart = \App\Models\CarPart::findOrFail($id);
//     $carPart->delete();

//     return redirect()->to('/admin/parts');
    

// })->name('parts.destroy');


// /admin/parts






Route::get('/parts/{id}', function ($id) {
    $carPart = CarPart::findOrFail($id);  // Get the car part by ID
    return view('admin.parts.edit', ['carPart' => $carPart]);  // Pass car part to the view
})->name('parts');  // Name the route 'parts'




// Route::put('/parts/{id}', function ($id) {
//     $carPart = CarPart::findOrFail($id);
//     $carPart->update(request()->only('name')); // Update the part (add validation and sanitization)
    
//     // return redirect()->route('admin.parts.view', $carPart->id);
//     return redirect()->to('/admin/parts');

// })->name('parts.update');


Route::put('/parts/{id}', function ($id) {
    // Find the car part by ID or fail if not found
    $carPart = \App\Models\CarPart::findOrFail($id);
    
    // Validate the request
    $validatedData = request()->validate([
        'name' => 'required|string|max:255|unique:car_parts,name,' . $id,
        'component' => 'required|string|in:Body Parts (Inner),Body Parts (Outer),Mechanical Parts',
    ]);

    // Update the car part with the validated data
    $carPart->update($validatedData);

    // Redirect back to the parts list page
    return redirect()->to('/admin/parts')->with('success', 'Car part updated successfully');
})->name('parts.update');

// Delete Route (Handle delete action)
Route::delete('/parts/{id}', function ($id) {
    $carPart = \App\Models\CarPart::findOrFail($id);
    $carPart->delete();

    return redirect()->to('/admin/parts');
    

})->name('parts.destroy');

        // // View Brands
        // Route::get('/brands', function () {
        //     $brands = \App\Models\Brand::latest()->get();

        //     return view('admin.brands.view', [
        //         'brands' => $brands,
        //     ]);
        // });
// View Brands - Sorted Alphabetically
Route::get('/brands', function () {
    $brands = \App\Models\Brand::orderBy('name', 'asc')->get();  // Order by 'name' in ascending order

    return view('admin.brands.view', [
        'brands' => $brands,
    ]);
});

        Route::post('/brands', function (Request $request) {
            $request->validate([
                'name' => 'required|unique:brands,name',
            ]);

            $brands = \App\Models\Brand::create([
                'name' => $request->name,
            ]);

            $brands->save();

            return redirect()->to('/admin/brands');
        })->name('add-brand');

        // Add Brands
        Route::get('/add-brands', function () {
            return view('admin.brands.add');
        });












        Route::get('/brands/{id}', function ($id) {
            $brands = Brand::findOrFail($id);  // Get the car part by ID
            return view('admin.brands.edit', ['brands' => $brands]);  // Pass car part to the view
        })->name('brands');  // Name the route 'parts'
        
        
        
        
        Route::put('/brands/{id}', function ($id) {
            $brands = Brand::findOrFail($id);
            $brands->update(request()->only('name')); // Update the part (add validation and sanitization)
            // return redirect()->route('admin.parts.view', $carPart->id);
            return redirect()->to('/admin/brands');
        
        })->name('brands.update');
        
        
        
        // Delete Route (Handle delete action)
        Route::delete('/brands/{id}', function ($id) {
            $brands = \App\Models\Brand::findOrFail($id);
            $brands->delete();
        
            return redirect()->to('/admin/brands');
            
        
        })->name('brands.destroy');
        
        
        
        
        



Route::prefix('marketer')->group(function () {
    Route::get('/business-owners/{id}/edit', [MarketerBusinessController::class, 'edit'])
        ->name('marketer.business-owners.edit');

    Route::put('/business-owners/{id}', [MarketerBusinessController::class, 'update'])
        ->name('marketer.business-owners.update');

    Route::delete('/business-owners/{id}', [MarketerBusinessController::class, 'destroy'])
        ->name('marketer.business-owners.destroy');
    

});











        

        // View Car Parts
        // Route::get('/parts', function () {
        //     $carParts = \App\Models\CarPart::latest()->get();

        //     return view('admin.parts.view', [
        //         'carParts' => $carParts,
        //     ]);
        // });

// View Car Parts - Sorted Alphabetically
// Route::get('/parts', function () {
//     // $carParts = \App\Models\CarPart::orderBy('name', 'asc')->get();  // Order by 'name' in ascending order
//     $carParts = CarPart::orderBy('name', 'asc')->paginate(8); // ✅ This returns a Paginator

//     return view('admin.parts.view', [
//         'carParts' => $carParts,
//     ]);
// });
Route::get('/parts', function (Request $request) {
    $query = CarPart::query();

    if ($request->has('component')) {
        $query->where('component', $request->component);
    }

    $carParts = $query->orderBy('name', 'asc')->paginate(8);

    return view('admin.parts.view', [
        'carParts' => $carParts,
    ]);
})->name('parts.index');
        Route::post('/parts', function (Request $request) {
            $request->validate([
                'name' => 'required|unique:car_parts,name',
                'component' => 'required|in:Body Parts (Inner),Body Parts (Outer),Mechanical Parts',

            ]);

            $part = \App\Models\CarPart::create([
                'name' => $request->name,
                'component' => $request->component,

            ]);

            $part->save();

            return redirect()->to('/admin/parts');
        })->name('add-part');

        // Add Car Parts
        Route::get('/add-parts', function () {
            return view('admin.parts.add');
        });

        // View Roles
        Route::get('/roles', [LevelController::class, 'index'])->name(
            'operators.role.index'
        );
        Route::get('/add-role', [LevelController::class, 'create'])->name(
            'operators.role.create'
        );
        Route::post('/add-role', [LevelController::class, 'store'])->name(
            'operators.role.store'
        );

        // Withdraw Requests
        Route::get('/withdraw-requests', [
            WithdrawalController::class,
            'index',
        ])->name('withdraw-requests');
        Route::put('withdraw-requests/{id}/approve', [
            WithdrawalController::class,
            'approve',
        ])->name('withdraw.approve');
        Route::put('withdraw-requests/{id}/reject', [
            WithdrawalController::class,
            'reject',
        ])->name('withdraw.reject');

        // ******************Insurance Side******************
    });

Route::prefix('marketer')
    ->middleware([\App\Http\Middleware\MarketerMiddleware::class])
    ->group(function () {
        Route::get('/', function () {
            return view('marketer.index');
        });
            Route::get('/proformas', [\App\Http\Controllers\MarketerProformaController::class, 'index'])
        ->name('marketer.proformas');

        // Marketer Proforma Details (Read-only view)
        Route::get('/proforma-details', function (Request $request) {
            $proforma = \App\Models\Proforma::find($request->query('proforma'));
            if (!$proforma) {
                return redirect()->back();
            }
            return view('marketer.proforma-details', compact('proforma'));
        });









        // View Insurances
        Route::get('/insurances', function () {
            $insurances = \App\Models\User::where('role', 'insurance')
            ->where('registered_by', auth()->id())
            ->get();

            return view('marketer.users.insurances.view', [
                'insurances' => $insurances,
            ]);
        });

        // Add Insurance
        Route::get('/add-insurance', function () {
            return view('marketer.users.insurances.add');
        });

        // Add Insurance
        Route::post('/add-insurance', [
            \App\Http\Controllers\InsuranceController::class,
            'store',
        ])->name('add-insurance.marketer');



        Route::get('/edit-insurance/{id}', [
            \App\Http\Controllers\InsuranceController::class,
            'edit',
        ])->name('edit-insurance.marketer');
        
        Route::put('/update-insurance/{id}', [
            \App\Http\Controllers\InsuranceController::class,
            'update',
        ])->name('update-insurance.marketer');
        





        Route::get('/edit-shop/{id}', [
            \App\Http\Controllers\ShopController::class,
            'edit',
            
        ])->name('edit-shop.marketer');


        
        Route::put('/update-shop/{id}', [
            \App\Http\Controllers\ShopController::class,
            'update',
        ])->name('update-shop.marketer');


        Route::get('/edit-garage/{id}', [
            \App\Http\Controllers\GarageController::class,
            'edit',
            
        ])->name('edit-garage.marketer');



        Route::put('/update-garage/{id}', [
            \App\Http\Controllers\GarageController::class,
            'update',
        ])->name('update-garage.marketer');






        // View Garage
        Route::get('/garages', function () {
            $garages = \App\Models\User::where('role', 'garage')
            ->where('registered_by', auth()->id())
            ->get();

            return view('marketer.users.garages.view', [
                'garages' => $garages,
            ]);
        });

        // Add Garage
        Route::get('/add-garage', function () {
            return view('marketer.users.garages.add');
        });
        // Add Insurance
        Route::post('/add-garage', [
            \App\Http\Controllers\GarageController::class,
            'store',
        ])->name('add-garage.marketer');
        // View Business Owner
        Route::get('/business-owners', function () {
            $businessOwners = \App\Models\User::where(
                'role',
                'others'
            )->where('registered_by', auth()->id())
            ->get();



                





            return view('marketer.users.business-owners.view', [
                'businessOwners' => $businessOwners,
            ]);
        });

        // Add Business Owner
        Route::get('/add-business-owner', function () {
            return view('marketer.users.business-owners.add');
        });





// Edit Business Owner (GET)
Route::get('/business-owners/{id}/edit', [BusinessOwnerController::class, 'edit'])
    ->name('marketer.business-owners.edit');  // Ensure this is plural

// Update Business Owner (PUT)
Route::put('/business-owners/{id}', [BusinessOwnerController::class, 'update'])
    ->name('marketer.business-owners.update');



        // Add Insurance
        Route::post('/add-business-owner', [
            \App\Http\Controllers\BusinessOwnerController::class,
            'store',
        ])->name('add-business-owner.marketer');

        // View Spare Part Shops
        Route::get('/spare-part-shops', function () {
            $shops = \App\Models\User::where('role', 'shop')
            ->where('registered_by', auth()->id())
            ->get();

            return view('marketer.users.spare-part-shops.view', [
                'shops' => $shops,
            ]);
        });
        // Add Insurance
        Route::post('/add-shop', [
            \App\Http\Controllers\ShopController::class,
            'store',
        ])->name('add-shop.marketer');
        // Add Spare Part Shop
        Route::get('/add-spare-part-shop', function () {
            $brands = \App\Models\Brand::latest()->get();

            return view('marketer.users.spare-part-shops.add', [
                'brands' => $brands,
            ]);
        });
    });

Route::prefix('employee')
    ->middleware([\App\Http\Middleware\EmployeeMiddleware::class])
    ->group(function () {
        Route::get('/', function () {
            return view('employee.index');
        });

        Route::get('/proformas-from-insurances', function () {
            return view('employee.insurance-proformas');
        });

        Route::get('/proformas-from-others', function () {
            return view('employee.other-proformas');
        });

        Route::get('/my-files', function () {
            $proformas = \App\Models\Proforma::all();

            return view('employee.my-files', compact('proformas'));
        });

        Route::get('/change-status/{proforma}', function (Proforma $proforma) {
            $currentUserLevel = auth()->user()->level;
            $userManager = auth()->user()->manager;
            if ($userManager) {
                $proforma->update(['status' => 'payment collected']);
                $proforma->selectedBy()?->deactivate();
                ProformaSelection::updateOrCreate([
                    'proforma_id' => $proforma->id,
                    'employee_id' => $userManager->manager?->id,
                    'active' => true,
                ]);
            } else {
                 if ($proforma->selected()) {
                    $proforma->selectedBy()?->deactivate();
                    $proforma->update([
                        'status' => 'completed',
                        'verified' => true,
                    ]);
                } else {
                    $proforma->update([
                        'status' => 'completed',
                    ]);
                }
            }


            return redirect()->back();
        });

        Route::get('/withdraw-requests', function () {
            return view('employee.balance');
        });
        Route::get('/profile', function () {
            return view('employee.profile');
        });

        Route::get('/post-proforma', function (Request $request) {
            $proforma = \App\Models\Proforma::find(
                $request->query('proforma_id')
            );
            if (! $proforma) {
                return redirect()->back();
            }

            return view('admin.proforma.operator-post', compact('proforma'));
        });
    });

Route::prefix('insurance')
    ->middleware([\App\Http\Middleware\RoleMiddleware::class])
    ->group(function () {
        Route::get('/', function(){ return view('insurance.index'); });
Route::get('/balance', [UserBalanceController::class, 'index'])->name('balance');
        Route::get('/received-proformas', function () {
    if (auth()->check()) {
        $user = auth()->user();

        // Mark all new proformas for this user as viewed
        $user->markReceivedProformasAsViewed();

        $proformas = \App\Models\Proforma::where('poster_id', $user->id)
            ->where('status', 'completed')
            ->where('verified', true)
            ->orderBy('created_at','desc')
            ->paginate(10);

        return view('insurance.proformas', compact('proformas'));
    }

    return redirect('/login');
});

        Route::get('proforma-details', function (Request $request) {
            $proforma = \App\Models\Proforma::with([
                'applications.prices',
                'applications.applicationBy',
                'parts',
            ])->findOrFail($request->query('proforma_id'));
            $applications = $proforma->applications->sortBy(function($application) {
                // For shops: calculate final price from parts minus discount
                if ($application->from === 'shop' && $application->prices->count() > 0) {
                    $subtotal = $application->prices->sum('part_total');
                    $discountPct = (float)($application->discount ?? 0);
                    $discountAmt = ($subtotal * $discountPct) / 100;
                    return $subtotal - $discountAmt;
                }
                // For garages: use amount field
                return $application->amount ?? 0;
            });

            // Limit to requested number for non-Etera Chereta
            $requiredShops = (int) ($proforma->required_number_of_shops ?? 0);
            if ($requiredShops > 0) {
                $applications = $applications->take($requiredShops);
            }

            // Etera Chereta (0 shops requested): show only top 5 lowest price
            if ($requiredShops === 0) {
                $applications = $applications->take(5);
            }

            return view('insurance.proforma-details', compact('proforma','applications'));
        });
        Route::get('/add-parts', function () {
            return view('insurance.parts.add');
        });
        Route::get('/parts', function () {
            return view('insurance.parts.view');
        });
        // Create file
        Route::get('partners', function () {
            return view('insurance.partners');
        });
        Route::delete('partners/{partner}', [
            PartnerController::class,
            'destroy',
        ])->name('partners.destroy');
        Route::post('partners/add', [PartnerController::class, 'store'])->name('partners.add');

        Route::get('/profile', function () {
            return view('insurance.profile');
        });
        




        // validation is not included for the file creation
        Route::get('create-file', function () {
            $availableBrands = BrandUser::distinct('brand_id')->pluck('brand_id');
            // $brands = \App\Models\Brand::whereIn('id', $availableBrands?->toArray())->orderBy('name', 'asc')->get();
            $brands = \App\Models\Brand::orderBy('name', 'asc')->get();
            $parts = \App\Models\CarPart::orderBy('name', 'asc')->get();
            $spare_part_partners = auth()->user()->sparePartPartners();
            $garage_partners = auth()->user()->garagePartners();

            return view(
                'insurance.create-file',
                compact(
                    'brands',
                    'parts',
                    'spare_part_partners',
                    'garage_partners'
                )
            );
        });
        // Route::post('create-file', function (Request $request) {
        //     $request->validate(
        //         [
        //             'file_number' => 'required',
        //             'brand_id' => 'required|exists:brands,id',
        //             'model' => 'required',
        //             'year' => 'required|numeric',
        //             'customer_name' => 'required',
        //             'customer_phone_number' => 'required|numeric',
        //             'license_plate_number' => 'required',
        //             'chassis_number' => 'required',
        //             'parts' => 'required|array',
        //             'parts.id' => 'required|array',
        //             'parts.number' => 'required|array|size:',
        //                 count($request->input('parts.id')),
        //             'parts.grade' => 'required|array|size:',
        //                 count($request->input('parts.id')),
        //             'parts.id.*' => 'required|exists:car_parts,id',
        //             'parts.number.*' => 'required|string',
        //             'parts.grade.*' => 'required|string',
        //             'parts.country.*' => 'nullable|string',
        //             'parts.quantity.*' => 'nullable|numeric',
        //         ],
        //         [
        //             'parts.required' => 'Please provide the parts information.',
        //             'parts.array' => 'Parts must be an array.',
        //             'parts.id.required' => 'Part IDs are required.',
        //             'parts.id.array' => 'Part IDs must be an array.',
        //             'parts.number.required' => 'Please enter part numbers for each part.',
        //             'parts.number.array' => 'Part numbers must be an array.',
        //             'parts.number.size' => 'You must provide a part number for each part.',
        //             'parts.grade.required' => 'Please specify grades for each part.',
        //             'parts.grade.array' => 'Grades must be an array.',
        //             'parts.grade.size' => 'You must provide a grade for each part.',
        //             'parts.id.*.required' => 'Each part ID is required.',
        //             'parts.id.*.exists' => 'The selected part does not exist.',
        //             'parts.number.*.required' => 'Each part number is required.',
        //             'parts.number.*.numeric' => 'Each part number must be a numeric value.',
        //             'parts.grade.*.required' => 'Each part grade is required.',
        //             'parts.grade.*.string' => 'Each part grade must be a string.',
        //         ]
        //     );

        //     DB::beginTransaction();
        //     $proforma = \App\Models\Proforma::create([
        //         'poster_id' => auth()->user()->id,
        //         'file_number' => $request->file_number,
        //         'car_brand_id' => $request->brand_id,
        //         'customer_name' => $request->customer_name,
        //         'customer_phone_number' => $request->customer_phone_number,
        //         'license_plate_number' => $request->license_plate_number,
        //         'chassis_number' => $request->chassis_number,
        //         'year' => $request->year,
        //         'model' => $request->model,
        //         'required_number_of_shops' => 3,
        //         'required_number_of_garages' => 3,
        //     ]);

        //     foreach ($request->parts['id'] as $index => $partId) {
        //         $proforma->parts()->attach($partId, [
        //             'number' => $request->parts['number'][$index],
        //             'grade' => $request->parts['grade'][$index],
        //             'country' => $request->parts['country'][$index],
        //             'quantity' => $request->parts['quantity'][$index],
        //             'photo' => $request->parts['photo'][$index] ?? null,
        //         ]);
        //     }



            

        //     if ($request.spare_part_partners) {
        //         foreach ($request.spare_part_partners as $inbox) {
        //             Inbox::create([
        //                 'proforma_id' => $proforma->id,
        //                 'user_id' => $inbox,
        //             ]);
        //         }
        //     }
        //     if ($request.garage_partners) {
        //         foreach ($request.garage_partners as $inbox) {
        //             Inbox::create([
        //                 'proforma_id' => $proforma->id,
        //                 'user_id' => $inbox,
        //             ]);
        //         }
        //     }


        //     return redirect()
        //         ->to('/insurance')
        //         ->with('success', 'File created successfully');
        // })->name('create.proforma');








        Route::post('create-file', function (Request $request) {
            $validator = Validator::make($request->all(), [
                'file_number' => 'nullable',
                'brand_id' => 'required|exists:brands,id',
                'car_type' => 'nullable|in:ICE,EV,Hybrid,Others',
                'model' => 'required',
                'year' => 'required',
                'customer_name' => 'required',
                'insured' => 'nullable|boolean',
                'customer_phone_number' => 'required|string',
                'customer_email' => 'nullable|string',
                'license_plate_number' => 'required',
                'chassis_number' => 'nullable',
                'parts' => 'required|array',
                'parts.*.number' => 'required|string',
                'parts.*.grade' => 'required|string',
                'parts.*.country' => 'required|string',
                'parts.*.quantity' => 'nullable|numeric',
                'parts.*.condition' => 'nullable|string',
                'parts.*.component' => 'nullable|string',
                'parts.*.images.*' => 'nullable|image|max:10240', // Validate images
                'number_of_proformas' => 'nullable|integer|min:-1|max:5',
                'etera_chereta_hours' => 'nullable|integer|in:4,8,12,24,48,72',
                'voice_note' => 'nullable|string|max:10485760',
                'proforma_type' => 'nullable|in:insurance_standard,insurance_shop_only,insurance_garage_only',
                'number_of_garages' => 'nullable|integer|min:1|max:5'
            ]);
            
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }
        
            DB::beginTransaction();

            $isEteraChereta = $request->input('number_of_proformas') === '-1';

            $timerMinutes = null;
            $requiredShops = 3;
            $requiredGarages = 3;
            $timerExpiresAt = null;
            $proformaType = null;

            if ($isEteraChereta) {
                $eteraHours = (int) $request->input('etera_chereta_hours', 24);
                $timerMinutes = $eteraHours * 60;
                $timerEnabled = true;
                $timerExpiresAt = now()->addMinutes($timerMinutes);
                $requiredShops = 0;
                $requiredGarages = 0;
                $proformaType = null;
            } elseif ($request->input('proforma_type') === 'insurance_garage_only') {
                $requiredShops = 0;
                $requiredGarages = max(1, (int) $request->input('number_of_garages', 3));
                $proformaType = 'insurance_garage_only';
            } elseif ($request->input('proforma_type') === 'insurance_shop_only') {
                $requiredShops = max(1, (int) $request->input('number_of_proformas', 3));
                $requiredGarages = 0;
                $proformaType = 'insurance_shop_only';
            } else {
                $requiredShops = max(1, (int) $request->input('number_of_proformas', 3));
                $requiredGarages = 3;
                $proformaType = 'insurance_standard';
            }

            $proforma = \App\Models\Proforma::create([
                'poster_id' => auth()->user()->id,
                'file_number' => $request->file_number ?? '#'.auth()->user()->id.'-'.time(),
                'car_brand_id' => $request->brand_id,
                'car_type' => $request->input('car_type', 'ICE'),
                'customer_name' => $request->customer_name,
                'customer_phone_number' => $request->customer_phone_number,
                'customer_email' => $request->customer_email,
                'license_plate_number' => $request->license_plate_number,
                'chassis_number' => $request->chassis_number,
                'year' => $request->year,
                'model' => $request->model,
                'required_number_of_shops' => $requiredShops,
                'required_number_of_garages' => $requiredGarages,
                'proforma_type' => $proformaType,
                'timer_duration' => $timerMinutes,
                'timer_expires_at' => $timerExpiresAt,
                'insured' => $request->has('insured') ? true : false,
            ]);

            foreach ($request->parts as $partData) {
                // Create a new part record
                $part = $proforma->parts()->create([
                    'number' => $partData['number'],
                    'grade' => $partData['grade'] ?? null,
                    'country' => $partData['country'] ?? null,
                    'quantity' => $partData['quantity'] ?? null,
                    'condition' => $partData['condition'] ?? null,
                    'component' => $partData['component'] ?? null,
                ]);

            }
            
            // Shop partner inboxes (skip for garage-only)
            if ($proformaType !== 'insurance_garage_only' && $request->spare_part_partners) {
                foreach ($request->spare_part_partners as $inbox) {
                    Inbox::create([
                        'proforma_id' => $proforma->id,
                        'user_id' => $inbox,
                        'source' => 'insurance',
                    ]);
                }
            }

            // Garage partner inboxes (skip for shop-only)
            if ($proformaType !== 'insurance_shop_only' && $request->garage_partners) {
                foreach ($request->garage_partners as $inbox) {
                    Inbox::create([
                        'proforma_id' => $proforma->id,
                        'user_id' => $inbox,
                        'source' => 'insurance',
                    ]);
                }
            }

            // Handle voice note if present
            if ($request->voice_note) {
    try {
        $base64 = $request->voice_note;

        // Remove any header like "data:audio/webm;codecs=opus;base64,"
        if (preg_match('/^data:audio\/(\w+);.*base64,/', $base64, $matches)) {
            $extension = $matches[1]; // e.g. webm, mp3, wav
            $base64 = substr($base64, strpos($base64, ',') + 1);
        } else {
            $extension = 'webm'; // default
        }

        // Decode and save safely
        $voiceData = base64_decode($base64);

        if ($voiceData === false) {
            throw new \Exception('Base64 decoding failed.');
        }

        $filename = 'voice_note_' . $proforma->id . '_' . time() . '.' . $extension;
        Storage::disk('public')->put('voice_notes/' . $filename, $voiceData);

        $proforma->update(['voice_note_path' => 'voice_notes/' . $filename]);
    } catch (\Exception $e) {
        \Log::error('Voice note upload failed: ' . $e->getMessage());
    }
}

            
        
            
            
            $imageService = new ImageService();
            $imageService->upload($request, $proforma->id);
            $audioservice = new AudioService();
            $audioservice->upload($request, $proforma->id);
            $videoService = new VideoService();
            $videoService->upload($request, $proforma->id);
            

            DB::commit();

            // 🔔 Broadcast to admin dashboard in real-time
            event(new ProformaCreated($proforma));

            // If Etera-Chereta mode, dispatch auto-selection after expiry
            if ($isEteraChereta) {
                \App\Jobs\AutoSelectProformaOffers::dispatch($proforma->id)->delay(now()->addMinutes($timerMinutes));
            }

            return redirect()->back()->with('success', 'Proforma created successfully');
        })->name('insurance.create-file');

    Route::get('/insurance/create-file', function () {
        $brands = Brand::all();
        $spare_part_partners = User::where('role', 'shop')->get();
        $garage_partners = User::where('role', 'garage')->get();
        
        return view('insurance.create-file', compact('brands', 'spare_part_partners', 'garage_partners'));
    })->name('insurance.create-file.show');


        
        
    Route::post('/upload/image', [App\Http\Controllers\FileUploadController::class, 'uploadPartsImage'])->name('upload.image');
    Route::delete('/delete', [App\Http\Controllers\FileUploadController::class, 'deleteUpload'])->name('upload.delete');

    });

Route::get('proforma-details', function (Request $request) {
    $proforma = \App\Models\Proforma::find($request->query('proforma'));
    if (!$proforma) {
        return redirect()->back();
    }

    // Reset inbox count when user opens proforma details
    if (auth()->check()) {
        // Remove the proforma from user's inbox to reset the ticker
        \App\Models\Inbox::where('user_id', auth()->id())
            ->where('proforma_id', $proforma->id)
            ->delete();
    }

    return view('spare-part.details', compact('proforma'));
})->name('proforma-details');

Route::prefix('garage')
    ->middleware([GarageMiddleware::class])
    ->group(function () {
        Route::get('/proformas', function () {
            return view('spare-part.garage-proformas');
        });
        

Route::post('/proforma/{proforma}/request-close', function ($proformaId) {

    Log::info("🔵 Route hit: Start request-close", [
        'proforma_id' => $proformaId,
    ]);

    // Try to fetch the model normally
    $proforma = \App\Models\Proforma::find($proformaId);

    if (!$proforma) {
        Log::error("❌ Proforma not found!", [
            'proforma_id' => $proformaId,
        ]);

        return back()->with('error', 'Proforma not found.');
    }

    Log::info("🟡 Proforma loaded", [
        'id' => $proforma->id,
        'current_close_request' => $proforma->close_request,
    ]);

    // Try updating
    $updated = $proforma->update([
        'close_request' => true,
    ]);

    Log::info("🟢 Update executed", [
        'result' => $updated,
        'new_close_request' => $proforma->fresh()->close_request,
    ]);

    return back()->with('success', 'Close request submitted.');
})->name('garage.proforma.request-close');

        
        Route::get('/my-files', function () {
    $proformas = auth()->user()
        ->proformas()
        ->orderBy('created_at', 'desc')
        ->get();

    return view('spare-part.files', compact('proformas'));
})->name('garage.my-files');

        
        Route::get('proforma-details', function (Request $request) {
            $proforma = \App\Models\Proforma::find($request->query('proforma'));
            if (! $proforma) {
                return redirect()->back();
            }

            // Reset inbox count when user opens proforma details
            if (auth()->check()) {
                // Remove the proforma from user's inbox to reset the ticker
                \App\Models\Inbox::where('user_id', auth()->id())
                    ->where('proforma_id', $proforma->id)
                    ->delete();
            }

            return view('spare-part.details', compact('proforma'));
        })->name('garage.proforma-details');
        
        Route::post('apply/{proforma}', function (
            Request $request,
            Proforma $proforma
        ) {
            $request->validate([
                'amount' => 'required|numeric|min:1',
                'discount' => 'nullable|numeric|min:0|max:100',
            ]);

            // Calculate final amount
            $initialPrice = $request->amount;
            $discount = $request->discount ?? 0;
            $finalAmount = $request->input('final-amount', $initialPrice);
            
            // Ensure minimum amount
            $finalAmount = max($finalAmount, 1);

            $application = $proforma->applications()->create([
                'application_by' => auth()->id(),
                'from' => 'garage',
                'amount' => $finalAmount,
                'discount' => $discount,
            ]);

            // Send notification to proforma poster
            if ($proforma->poster && $proforma->poster->id !== auth()->id()) {
                $proforma->poster->notify(new ProformaApplicationReceived($proforma, $application, auth()->user()));
            }

            // Remove inbox record if exists
            $proforma->inboxes()->where('user_id', auth()->id())->delete();

            // Check if proforma should be closed (both garage and shop requirements met)
            $garageApplicationsCount = $proforma->applications()->where('from', 'garage')->count();
            $shopApplicationsCount = $proforma->applications()->where('from', 'shop')->count();
            $requiredGarages = (int) ($proforma->required_number_of_garages ?? 0);
            $requiredShops = (int) ($proforma->required_number_of_shops ?? 0);
            
            // Check if BOTH garage and shop requirements are met and not an Etera-Chereta (0,0) proforma
            $isEteraChereta = ($requiredGarages + $requiredShops) === 0;
            $garageRequirementMet = $requiredGarages === 0 || $garageApplicationsCount >= $requiredGarages;
            $shopRequirementMet = $requiredShops === 0 || $shopApplicationsCount >= $requiredShops;
            
            if (!$isEteraChereta && $garageRequirementMet && $shopRequirementMet && ($requiredGarages > 0 || $requiredShops > 0)) {
                $proforma->update(['status' => 'closed']);
                $proforma->inboxes()->delete();
            }

            return redirect('/role/proformas')
                ->with('success', 'Price quote submitted successfully!');
        })->name('garage.proforma.apply');

// Located inside the Route::prefix('garage') group

Route::get('/received-proformas', function () {
    $user = auth()->user();

    // Mark all new proformas for this user as viewed
    $user->markReceivedProformasAsViewed();

    // Fetch only completed proformas for the current user, newest first, paginated
    $proformas = Proforma::where('poster_id', $user->id)
        ->where('status', 'completed')       // ✅ only completed
        ->orderBy('created_at', 'desc')      // ✅ newest first
        ->paginate(10);

    return view('spare-part.received', compact('proformas'));
});


Route::get('/received-details', function (Request $request) {
    // Eagerly load all relationships the frontend view needs for the Proforma
    $proforma = Proforma::with([
            'parts', 
            'proformaInvoice', 
            'brand'
        ])
        ->where('status', 'completed')
        ->findOrFail($request->query('proforma'));

    // Eagerly load the 'applicationBy' relationship for each application
    $applications = $proforma->applications()
        ->with(['prices', 'applicationBy']) // ✅ Added 'applicationBy'
        ->orderBy('created_at', 'desc')
        ->get()
        ->sortByDesc(function($application) {
            // This sorting logic remains the same
            if ($application->applicationBy->role === 'shop' && $application->prices->count() > 0) {
                $subtotal = $application->prices->sum('part_total');
                $discountPct = (float)($application->discount ?? 0);
                $discountAmt = ($subtotal * $discountPct) / 100;
                return $subtotal - $discountAmt;
            }
            return $application->amount ?? 0;
        });

    return view('spare-part.received-details', compact('proforma', 'applications'));
});
        Route::get('/balance', [UserBalanceController::class, 'index'])->name('balance');
        Route::get('/inbox', function () {
            return view('spare-part.inbox');
        });

        Route::get('/other-proformas', function () {
            $proformas = Proforma::fromOthers()
                ->where('status', 'published')
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            return view('spare-part.others', compact('proformas'));
        });

Route::get('/create-file', function () {
    return view('spare-part.posts');
})->name('garage.create-file');

// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Log;
// use Illuminate\Support\Facades\Storage;
// use App\Jobs\AutoSelectProformaOffers;
// use App\Models\Proforma;
// use App\Http\Middleware\GarageMiddleware;
// use App\Http\Controllers\TempController;

Route::prefix('garage')
    ->middleware([GarageMiddleware::class])
    ->group(function () {

        /**
         * Display the proforma creation form
         */
        Route::get('create-file', function () {
            Log::info('🔍 GET request to garage/create-file');
            return view('spare-part.posts');
        })->name('garage.create-file');

        /**
         * Handle FilePond uploads
         */
        Route::post('/upload-part-image', [TempController::class, 'uploadPartImage'])->name('garage.upload-part-image');
        Route::delete('/delete-part-image', [TempController::class, 'revert'])->name('garage.delete-part-image');

        /**
         * Handle form submission (POST)
         */
        Route::post('create-file', function (Request $request) {
            Log::info('📝 POST request to garage/create-file received', [
                'user_id' => auth()->id(),
                'has_files' => $request->hasFile('parts.photo'),
                'all_input_keys' => array_keys($request->all()),
                'files_count' => $request->hasFile('parts.photo') ? count($request->file('parts.photo')) : 0,
            ]);

            Log::debug('📥 Full Request Data Snapshot', [
                'raw_input' => $request->except(['voice_note']),
                'voice_note_present' => $request->filled('voice_note'),
            ]);

            // 🔹 Step 1 — Validate input
            try {
                $validatedData = $request->validate([
                    'number_of_proformas' => ['required', 'integer', 'min:-1', 'max:4'],
                    'etera_chereta_hours' => ['nullable', 'integer', 'in:4,8,12,24,48,72'],
                    'brand_id' => ['required', 'integer', 'exists:brands,id'],
                    'car_type' => 'required|in:ICE,EV,Hybrid,Others',
                    'model' => ['required', 'string', 'max:255'],
                    'year' => ['required', 'regex:/^(#N\/A|19\d{2}|20\d{2})$/'],
                    'customer_phone_number' => ['required', 'string'],
                    'license_plate_number' => ['required', 'string'],
                    'chassis_number' => ['nullable', 'string'],
                    'parts.condition' => ['required', 'array', 'min:1'],
                    'parts.condition.*' => ['required', 'string', 'in:New'],
                    'parts.number' => ['required', 'array', 'min:1'],
                    'parts.number.*' => ['required', 'string'],
                    'parts.grade' => ['required', 'array', 'min:1'],
                    'parts.grade.*' => ['required', 'string'],
                    'parts.country' => ['required', 'array'],
                    'parts.country.*' => ['required', 'string'],
                    'parts.quantity' => ['required', 'array'],
                    'parts.quantity.*' => ['required', 'integer'],
                    'parts.component' => ['required', 'array', 'min:1'],
                    'parts.component.*' => ['required', 'string', 'in:Body Parts,Mechanical Parts'],
                    // ⚠️ FilePond now sends uploaded paths, not actual image files
                    'parts.photo' => ['nullable', 'array'],
                    'parts.photo.*' => ['nullable', 'array'],
                    'parts.photo.*.*' => ['nullable', 'string'],
                    'voice_note' => ['nullable', 'string'],
                ]);

                Log::info('✅ Validation passed successfully');
            } catch (\Illuminate\Validation\ValidationException $e) {
                Log::error('❌ Validation failed', [
                    'errors' => $e->errors(),
                    'input_data' => $request->except(['parts.photo', 'voice_note'])
                ]);
                return redirect()->back()->withErrors($e->errors())->withInput();
            }

            // 🔹 Step 2 — Process transaction
            try {
                DB::beginTransaction();

                $isEteraChereta = $request->input('number_of_proformas') === '-1';
                $eteraHours = (int) $request->input('etera_chereta_hours', 24);
                $requiredShops = $isEteraChereta ? 0 : (int) $request->input('number_of_proformas', 3);
                $timerMinutes = $isEteraChereta ? $eteraHours * 60 : null;
                $timerExpiresAt = $isEteraChereta ? now()->addMinutes($timerMinutes) : null;

                $proforma = Proforma::create([
                    'poster_id' => auth()->id(),
                    'file_number' => '#' . auth()->id() . '-' . substr(time(), -4),
                    'car_brand_id' => $request->brand_id,
                    'car_type' => $request->input('car_type', 'ICE'),
                    'customer_name' => auth()->user()->name,
                    'customer_phone_number' => $request->customer_phone_number,
                    'license_plate_number' => $request->license_plate_number,
                    'chassis_number' => $request->chassis_number,
                    'year' => $request->year,
                    'model' => $request->model,
                    'required_number_of_shops' => $requiredShops,
                    'required_number_of_garages' => 0,
                    'timer_duration' => $timerMinutes,
                    'timer_expires_at' => $timerExpiresAt,
                ]);

                // 🔹 Step 3 — Handle spare parts
                // 🔹 Attach FilePond async-uploaded images (using PartsImage model)
// 🔹 Step 3 — Handle spare parts
$partsData = $request->input('parts');

foreach ($partsData['condition'] as $index => $condition) {
    $part = $proforma->parts()->create([
        'number'    => $partsData['number'][$index] ?? null,
        'grade'     => $partsData['grade'][$index] ?? null,
        'country'   => $partsData['country'][$index] ?? null,
        'quantity'  => $partsData['quantity'][$index] ?? 1,
        'condition' => $condition,
        'component' => $partsData['component'][$index] ?? null,
    ]);

    Log::info('✅ Proforma part created', ['part_id' => $part->id, 'index' => $index]);

    // 🔹 Move temp images and attach them
    if (isset($partsData['photo'][$index]) && is_array($partsData['photo'][$index])) {
        foreach ($partsData['photo'][$index] as $photoPath) {
            if (!empty($photoPath) && str_contains($photoPath, 'uploads/temp/')) {
                $tempPath = $photoPath;
                $filename = basename($tempPath);
                $finalPath = 'uploads/part-images/' . $filename;

                // Move the file
                if (Storage::disk('public')->exists($tempPath)) {
                    Storage::disk('public')->move($tempPath, $finalPath);

                    \App\Models\PartsImage::create([
                        'proforma_part_id' => $part->id,
                        'image_path' => $finalPath,
                    ]);

                    Log::info('✅ Temp image moved and saved', [
                        'from' => $tempPath,
                        'to' => $finalPath,
                        'proforma_part_id' => $part->id,
                    ]);
                } else {
                    Log::warning('⚠️ Temp image not found', ['path' => $tempPath]);
                }
            }
        }
    } else {
        Log::warning('⚠️ No images found for part', ['index' => $index]);
    }
}


                // 🔹 Step 4 — Voice note (optional)
                if ($request->filled('voice_note')) {
                    $base64 = $request->voice_note;
                    
                    // Extract the extension from the MIME type (handles codecs like audio/webm;codecs=opus)
                    if (preg_match('#^data:audio/([^;,]+)#i', $base64, $matches)) {
                        $extension = $matches[1]; // e.g. webm, mp3, wav
                    } else {
                        $extension = 'webm'; // default
                    }
                    
                    // Remove the entire data URI prefix including any codec specifications
                    // Pattern matches: data:audio/webm;codecs=opus;base64, OR data:audio/webm;base64,
                    $audioData = base64_decode(preg_replace('#^data:audio/[^;]+[^,]*,#i', '', $base64));
                    
                    if ($audioData === false) {
                        Log::error('❌ Voice note base64 decoding failed');
                    } else {
                        $filename = 'voice_note_' . time() . '_' . uniqid() . '.' . $extension;
                        Storage::disk('public')->put('voice_notes/' . $filename, $audioData);
                        $proforma->update(['voice_note_path' => 'voice_notes/' . $filename]);
                        Log::info('🎤 Voice note saved', ['filename' => $filename, 'size' => strlen($audioData)]);
                    }
                }

                DB::commit();

                // 🔔 Broadcast to admin dashboard in real-time
                event(new ProformaCreated($proforma));

                // 🔹 Step 5 — Schedule Etera-Chereta AutoSelect
                if ($isEteraChereta) {
                    AutoSelectProformaOffers::dispatch($proforma->id)->delay(now()->addMinutes($timerMinutes));
                }

                return redirect()->back()->with('success', 'Proforma created successfully!');
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('❌ Proforma creation failed', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return redirect()->back()->withErrors(['general' => 'An unexpected error occurred.'])->withInput();
            }
        })->name('garage.create-file');
    });

        Route::get('/profile', function () {
            return view('spare-part.profile');
        });




        
    });

Route::post('apply/{proforma}', [ProformaApplicationController::class, 'store'])->name('proforma.apply');

Route::prefix('spare-part-shops')
    ->middleware([ShopMiddleware::class])
    ->group(function () {
        Route::get('/proformas', function () {
            return view('spare-part.proformas');
        });
        Route::get('/balance', [UserBalanceController::class, 'index'])->name('balance');
        Route::get('/inbox', function () {
            return view('spare-part.inbox');
        });
        Route::get('/other-proformas', function () {
            $proformas = Proforma::fromOthers()
                ->where('status', 'published')
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            return view('spare-part.others', compact('proformas'));
        });
        
        Route::get('/unified-proformas', function () {
            return view('spare-part.unified');
        });

        Route::get('proforma-details', function (Request $request) {
            $proforma = \App\Models\Proforma::find($request->query('proforma'));
            if (! $proforma) {
                return redirect()->back();
            }

            // Reset inbox count when user opens proforma details
            if (auth()->check()) {
                // Remove the proforma from user's inbox to reset the ticker
                \App\Models\Inbox::where('user_id', auth()->id())
                    ->where('proforma_id', $proforma->id)
                    ->delete();
            }

            return view('spare-part.details', compact('proforma'));
        })->name('proforma-details');

Route::post('apply/{proforma}', function (
    Request $request,
    Proforma $proforma
) {
    $request->validate([
        'amount' => 'required|numeric|min:1',
    ]);
    $application = $proforma->applications()->create([
        'application_by' => auth()->check()
            ? Auth::id()
            : \App\Models\User::Where('role', 'shop')->first()->id,
        'from' => 'shop',
        'amount' => $request->amount,
    ]);

    // Check if proforma should be closed (both garage and shop requirements met)
    $garageApplicationsCount = $proforma->applications()->where('from', 'garage')->count();
    $shopApplicationsCount = $proforma->applications()->where('from', 'shop')->count();
    $requiredGarages = (int) ($proforma->required_number_of_garages ?? 0);
    $requiredShops = (int) ($proforma->required_number_of_shops ?? 0);
    
    // Check if BOTH garage and shop requirements are met and not an Etera-Chereta (0,0) proforma
    $isEteraChereta = ($requiredGarages + $requiredShops) === 0;
    $garageRequirementMet = $requiredGarages === 0 || $garageApplicationsCount >= $requiredGarages;
    $shopRequirementMet = $requiredShops === 0 || $shopApplicationsCount >= $requiredShops;
    
    if (!$isEteraChereta && $garageRequirementMet && $shopRequirementMet && ($requiredGarages > 0 || $requiredShops > 0)) {
        if ($proforma->selected()) {
            $proforma->update(['status' => 'closed']);
        } 
        // The following line is what you need to remove or change:
        // else {
        //    $proforma->update(['status' => 'completed']);
        // }
        // The block is no longer needed since you don't want to set the status to 'completed'.
        $proforma->save();
    }

    return redirect('/role/proformas')
        ->with('success', 'Application submitted successfully');
});

        Route::get('/profile', function () {
            return view('spare-part.profile');
        });
    });

Route::get('/others', function () {
    return view('spare-part.others');
});

Route::get('/others-details', function () {
    return view('spare-part.others-details');
});

Route::get('/my-files', function () {
    return view('employee.my-proforma.view');
});

Route::post('/proformas', function (Request $request) {
    $proforma = \App\Models\Proforma::findOrFail($request->proforma);
    $telegram = new \App\Services\TelegramService();

    if ($request->spare_part_partners) {
        $uniqueSparePartPartners = array_unique($request->spare_part_partners);

        foreach ($uniqueSparePartPartners as $inbox) {
            if (empty($inbox)) {
                continue;
            }
            $inboxRecord = Inbox::firstOrCreate([
                'proforma_id' => $proforma->id,
                'user_id' => $inbox,
            ]);

            // Send Telegram + FCM notification to inboxed user
            if ($inboxRecord->wasRecentlyCreated) {
                try {
                    $user = \App\Models\User::find($inbox);
                    if ($user) {
                        if (!empty($user->telegram_chat_id) && $telegram->isConfigured()) {
                            $telegram->sendInboxReceivedNotification((string) $user->telegram_chat_id, $proforma);
                        }
                        if (!empty($user->device_token)) {
                            \App\Helpers\FcmHelper::send(
                                $user->device_token,
                                'New Proforma in Inbox',
                                "Proforma #{$proforma->file_number} has been sent to your inbox.",
                                ['type' => 'inbox', 'proforma_id' => (string) $proforma->id]
                            );
                        }
                    }
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('Inbox notification failed', [
                        'proforma_id' => $proforma->id,
                        'user_id' => $inbox,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }
    }

    if ($request->garage_partners) {
        $uniqueGaragePartners = array_unique($request->garage_partners);

        foreach ($uniqueGaragePartners as $inbox) {
            if (empty($inbox)) {
                continue;
            }
            $inboxRecord = Inbox::firstOrCreate([
                'proforma_id' => $proforma->id,
                'user_id' => $inbox,
            ]);

            // Send Telegram + FCM notification to inboxed user
            if ($inboxRecord->wasRecentlyCreated) {
                try {
                    $user = \App\Models\User::find($inbox);
                    if ($user) {
                        if (!empty($user->telegram_chat_id) && $telegram->isConfigured()) {
                            $telegram->sendInboxReceivedNotification((string) $user->telegram_chat_id, $proforma);
                        }
                        if (!empty($user->device_token)) {
                            \App\Helpers\FcmHelper::send(
                                $user->device_token,
                                'New Proforma in Inbox',
                                "Proforma #{$proforma->file_number} has been sent to your inbox.",
                                ['type' => 'inbox', 'proforma_id' => (string) $proforma->id]
                            );
                        }
                    }
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('Inbox notification failed', [
                        'proforma_id' => $proforma->id,
                        'user_id' => $inbox,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }
    }

    $proforma->update(['status' => 'published']);

    return redirect()->back()->with('success', 'Proforma updated successfully!');
})->name('proforma.store');

Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('markAllNotificationsRead');

Route::get('/debug-shops-and-brands', function () {
    $shops = \App\Models\User::where('role', 'shop')->with('brands')->get();
    $brands = \App\Models\Brand::all();
    return view('debug.shops-and-brands', compact('shops', 'brands'));
});

Route::post('proforma/{id}', function($id, \Illuminate\Http\Request $request){
// ... existing code ...
});
// Admin Analytics Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/admin/analytics', [App\Http\Controllers\AdminAnalyticsController::class, 'index'])
        ->name('admin.analytics.index');
    Route::post('/admin/analytics/mark-paid/{userId}', [App\Http\Controllers\AdminAnalyticsController::class, 'markPaid'])
        ->name('admin.analytics.markPaid');
    Route::post('/admin/analytics/receieve/{userId}', [App\Http\Controllers\AdminAnalyticsController::class, 'receivePayment'])
        ->name('admin.analytics.receivePayment');
    Route::get('/admin/analytics/export/{type}', [App\Http\Controllers\AdminAnalyticsController::class, 'exportData'])
        ->name('admin.analytics.export');
});



// Admin Settings Routes
Route::middleware(['auth'])->group(function () {

    // Admin Settings (Cost + Commission)
    Route::get('/admin/settings', [App\Http\Controllers\AdminSettingsController::class, 'index'])
        ->name('admin.settings.index');

    // Cost
    Route::post('/admin/settings/costs', [App\Http\Controllers\AdminSettingsController::class, 'storeCost'])
        ->name('admin.settings.store');

    Route::delete('/admin/settings/costs/{cost}', [App\Http\Controllers\AdminSettingsController::class, 'destroyCost'])
        ->name('admin.settings.destroy');

    // Commission
    Route::post('/admin/settings/commissions', [App\Http\Controllers\AdminSettingsController::class, 'storeCommission'])
        ->name('admin.commission.store');

    // Email Toggle (AJAX)
    Route::post('/admin/settings/email-toggle', [App\Http\Controllers\AdminSettingsController::class, 'toggleEmail'])
        ->name('admin.settings.email-toggle');
});


// Telegram Routes
Route::get('/telegram-connect', function (Request $request) {
    $user = auth()->user();
    // Allow guest access with userId query parameter (after signup)
    if (!$user && $request->query('userId')) {
        $user = \App\Models\User::find($request->query('userId'));
    }
    if (!$user || $user->telegram_chat_id) {
        return redirect('/');
    }
    $telegramService = app(\App\Services\TelegramService::class);
    $telegramLink = $telegramService->generateStartLink($user->id);
    $skipUrl = match($user->role) {
        'garage' => '/garage/',
        'shop' => '/spare-part-shops/',
        'admin' => '/admin',
        'insurance' => '/insurance',
        'others' => '/business-owner',
        'marketer' => '/marketer',
        'operator' => '/operator/dashboard',
        'employee' => '/employee',
        default => '/',
    };
    return view('authentication.telegram-connect', compact('telegramLink', 'skipUrl'));
})->name('telegram.connect');

// Telegram Webhook handler (called by Telegram servers — no CSRF, no session needed)
Route::post('/api/telegram/webhook', [\App\Http\Controllers\TelegramWebhookController::class, 'handle'])
    ->withoutMiddleware([
        \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
    ])
    ->name('telegram.webhook');

// Admin route to register the webhook with Telegram (visit once to set up)
Route::get('/admin/telegram/setup-webhook', function () {
    if (!auth()->user() || auth()->user()->role !== 'admin') {
        abort(403);
    }

    $botToken = config('services.telegram.bot_token', env('TELEGRAM_BOT_TOKEN', ''));
    if (empty($botToken)) {
        return response()->json([
            'success' => false,
            'error' => 'TELEGRAM_BOT_TOKEN is not set in .env',
        ]);
    }

    // Build your webhook URL (must be HTTPS on production)
    $webhookUrl = url('/api/telegram/webhook');

    $response = \Illuminate\Support\Facades\Http::post(
        "https://api.telegram.org/bot{$botToken}/setWebhook",
        ['url' => $webhookUrl]
    );

    $result = $response->json();

    // Also get current webhook info for debugging
    $infoResponse = \Illuminate\Support\Facades\Http::get(
        "https://api.telegram.org/bot{$botToken}/getWebhookInfo"
    );

    return response()->json([
        'success' => $result['ok'] ?? false,
        'setWebhook_result' => $result,
        'webhookInfo' => $infoResponse->json(),
        'webhook_url_used' => $webhookUrl,
    ]);
})->middleware('auth')->name('telegram.setup-webhook');

// Proforma Status API (for live polling)
Route::get('/api/proforma-statuses', function (\Illuminate\Http\Request $request) {
    $ids = $request->input('ids', []);
    if (empty($ids)) return response()->json([]);
    $proformas = \App\Models\Proforma::whereIn('id', $ids)->get(['id', 'status', 'verified']);
    $result = [];
    foreach ($proformas as $p) {
        $statusClass = match($p->status) {
            'pending' => 'bg-warning',
            'published' => 'bg-info',
            'closed' => 'bg-secondary',
            'completed' => 'bg-success',
            'rejected' => 'bg-danger',
            'sent_to_owner' => 'bg-primary',
            'returned' => 'bg-warning',
            'payment collected' => 'bg-success',
            default => 'bg-secondary',
        };
        $result[] = ['id' => $p->id, 'status' => $p->status, 'badge_class' => $statusClass];
    }
    return response()->json($result);
})->middleware('auth')->name('api.proforma-statuses');

// Admin User Approval Routes
Route::get('/admin/users/approvals', [App\Http\Controllers\AdminController::class, 'userApprovals'])->name('admin.users.approvals');
Route::patch('/admin/users/{id}/approve', [App\Http\Controllers\AdminController::class, 'approveUser'])->name('admin.users.approve');
Route::patch('/admin/users/{id}/revoke', [App\Http\Controllers\AdminController::class, 'revokeUser'])->name('admin.users.revoke');
Route::get('/admin/users/{id}/view', [App\Http\Controllers\AdminController::class, 'viewUser'])->name('admin.users.view');
Route::delete('/admin/users/{id}/delete', [App\Http\Controllers\AdminController::class, 'deleteUser'])->name('admin.users.delete');

// Admin Proforma Routes
Route::get('/admin/proformas/{id}/details', [App\Http\Controllers\AdminController::class, 'proformaDetails'])->name('admin.proformas.details');
Route::delete('/admin/proformas/{id}', [App\Http\Controllers\AdminController::class, 'deleteProforma'])->name('admin.proformas.delete');
Route::post('/admin/proformas/{id}/approve', [App\Http\Controllers\AdminController::class, 'approveProforma'])->name('admin.proformas.approve');
Route::post('/admin/applications/{id}/accept', [App\Http\Controllers\AdminController::class, 'acceptApplication'])->name('admin.applications.accept');
Route::post('/admin/applications/{id}/reject', [App\Http\Controllers\AdminController::class, 'rejectApplication'])->name('admin.applications.reject');
Route::post('/admin/proformas/{proforma}/reject', [ProformaController::class, 'reject'])->name('proformas.reject');

 

// Admin Send to Owner Routes
Route::post('/admin/proformas/{id}/send-to-garage', [App\Http\Controllers\AdminController::class, 'sendToGarage'])->name('admin.proformas.send-to-garage');
Route::post('/admin/proformas/{id}/send-to-insurance', [App\Http\Controllers\AdminController::class, 'sendToInsurance'])->name('admin.proformas.send-to-insurance');
Route::post('/admin/proformas/{id}/send-to-business-owner', [App\Http\Controllers\AdminController::class, 'sendToBusinessOwner'])->name('admin.proformas.send-to-business-owner');
Route::post('/admin/proformas/{id}/send-to-spare-part', [App\Http\Controllers\AdminController::class, 'sendToSparePart'])->name('admin.proformas.send-to-spare-part');
Route::prefix('business-owner')
    ->middleware([\App\Http\Middleware\BusinessOwnerMiddleware::class])
    ->group(function () {
        // Create file (GET)
        Route::get('create-file', function () {
            $userIsTest = auth()->user()?->is_test ?? false;

$brands = \App\Models\Brand::where('is_test', $userIsTest)
    ->orderBy('name', 'asc')
    ->get();
 	    $parts = \App\Models\CarPart::orderBy('name', 'asc')->get();
            $spare_part_partners = auth()->user()->sparePartPartners();
            $garage_partners = auth()->user()->garagePartners();

            return view('business-owner.create-file', compact('brands','parts','spare_part_partners','garage_partners'));
        })->name('business-owner.create-file');
        
Route::prefix('business-owner')
    ->middleware([\App\Http\Middleware\BusinessOwnerMiddleware::class])
    ->group(function () {

        /**
         * Display the proforma creation form
         */
        Route::get('create-file', function () {
            Log::info('🔍 GET request to business-owner/create-file');
            $brands = Brand::orderBy('name', 'asc')->get();
            $parts = CarPart::orderBy('name', 'asc')->get();
            $spare_part_partners = auth()->user()->sparePartPartners();
            $garage_partners = auth()->user()->garagePartners();

            return view('business-owner.create-file', compact('brands','parts','spare_part_partners','garage_partners'));
        })->name('business-owner.create-file');
        Route::post('/proforma/{proforma}/request-close', function ($proformaId) {

    Log::info("🔵 Route hit: Start request-close", [
        'proforma_id' => $proformaId,
    ]);

    // Try to fetch the model normally
    $proforma = \App\Models\Proforma::find($proformaId);

    if (!$proforma) {
        Log::error("❌ Proforma not found!", [
            'proforma_id' => $proformaId,
        ]);

        return back()->with('error', 'Proforma not found.');
    }

    Log::info("🟡 Proforma loaded", [
        'id' => $proforma->id,
        'current_close_request' => $proforma->close_request,
    ]);

    // Try updating
    $updated = $proforma->update([
        'close_request' => true,
    ]);

    Log::info("🟢 Update executed", [
        'result' => $updated,
        'new_close_request' => $proforma->fresh()->close_request,
    ]);

    return back()->with('success', 'Close request submitted.');
})->name('business-owner.proforma.request-close');

        /**
         * Handle FilePond uploads
         */
        Route::post('/upload-part-image', [TempController::class, 'uploadPartImage'])->name('business-owner.upload-part-image');
        Route::delete('/delete-part-image', [TempController::class, 'revert'])->name('business-owner.delete-part-image');

        /**
         * Handle form submission (POST)
         */
        Route::post('create-file', function (Request $request) {
            Log::info('📝 POST request to business-owner/create-file received', [
                'user_id' => auth()->id(),
                'user_type' => 'business-owner',
                'has_files' => $request->hasFile('parts.photo'),
                'all_input_keys' => array_keys($request->all()),
                'files_count' => $request->hasFile('parts.photo') ? count($request->file('parts.photo')) : 0,
            ]);

            Log::debug('📥 Full Request Data Snapshot', [
                'raw_input' => $request->except(['voice_note']),
                'voice_note_present' => $request->filled('voice_note'),
            ]);

            // 🔹 Step 1 — Validate input
            try {
                $validatedData = $request->validate([
                    'number_of_proformas' => ['required', 'integer', 'min:-1', 'max:4'],
                    'etera_chereta_hours' => ['nullable', 'integer', 'in:4,8,12,24,48,72'],
                    'brand_id' => ['required', 'integer', 'exists:brands,id'],
                    'car_type' => 'required|in:ICE,EV,Hybrid,Others',

                    'model' => ['required', 'string', 'max:255'],
                    'year' => ['required', 'regex:/^(#N\/A|19\d{2}|20\d{2})$/'],
                    'customer_phone_number' => ['required', 'string'],
                    'license_plate_number' => ['required', 'string'],
                    'chassis_number' => ['nullable', 'string'],
                    'parts.condition' => ['required', 'array', 'min:1'],
                    'parts.condition.*' => ['required', 'string', 'in:New'],
                    'parts.number' => ['required', 'array', 'min:1'],
                    'parts.number.*' => ['required', 'string'],
                    'parts.grade' => ['required', 'array', 'min:1'],
                    'parts.grade.*' => ['required', 'string'],
                    'parts.country' => ['required', 'array'],
                    'parts.country.*' => ['required', 'string'],
                    'parts.quantity' => ['required', 'array'],
                    'parts.quantity.*' => ['required', 'integer'],
                    'parts.component' => ['required', 'array', 'min:1'],
                    'parts.component.*' => ['required', 'string', 'in:Body Parts,Mechanical Parts'],
                    // ⚠️ FilePond now sends uploaded paths, not actual image files
                    'parts.photo' => ['nullable', 'array'],
                    'parts.photo.*' => ['nullable', 'array'],
                    'parts.photo.*.*' => ['nullable', 'string'],
                    'voice_note' => ['nullable', 'string'],
                ]);

                Log::info('✅ Validation passed successfully for business-owner');
            } catch (\Illuminate\Validation\ValidationException $e) {
                Log::error('❌ Validation failed for business-owner', [
                    'errors' => $e->errors(),
                    'input_data' => $request->except(['parts.photo', 'voice_note'])
                ]);
                return redirect()->back()->withErrors($e->errors())->withInput();
            }

            // 🔹 Step 2 — Process transaction
            try {
                DB::beginTransaction();

                $isEteraChereta = $request->input('number_of_proformas') === '-1';
                $eteraHours = (int) $request->input('etera_chereta_hours', 24);
                $requiredShops = $isEteraChereta ? 0 : (int) $request->input('number_of_proformas', 3);
                $timerMinutes = $isEteraChereta ? $eteraHours * 60 : null;
                $timerExpiresAt = $isEteraChereta ? now()->addMinutes($timerMinutes) : null;

                $proforma = Proforma::create([
                    'poster_id' => auth()->id(),
                    'file_number' => '#' . auth()->id() . '-' . substr(time(), -4),
                    'car_brand_id' => $request->brand_id,
                    'car_type' => $request->input('car_type', 'ICE'),

                    'customer_name' => auth()->user()->name,
                    'customer_phone_number' => $request->customer_phone_number,
                    'license_plate_number' => $request->license_plate_number,
                    'chassis_number' => $request->chassis_number,
                    'year' => $request->year,
                    'model' => $request->model,
                    'required_number_of_shops' => $requiredShops,
                    'required_number_of_garages' => 0,
                    'timer_duration' => $timerMinutes,
                    'timer_expires_at' => $timerExpiresAt,
                ]);

                Log::info('✅ Proforma created for business-owner', ['proforma_id' => $proforma->id]);

                // 🔹 Step 3 — Handle spare parts with FilePond image handling
                $partsData = $request->input('parts');

                foreach ($partsData['condition'] as $index => $condition) {
                    $part = $proforma->parts()->create([
                        'number'    => $partsData['number'][$index] ?? null,
                        'grade'     => $partsData['grade'][$index] ?? null,
                        'country'   => $partsData['country'][$index] ?? null,
                        'quantity'  => $partsData['quantity'][$index] ?? 1,
                        'condition' => $condition,
                        'component' => $partsData['component'][$index] ?? null,
                    ]);

                    Log::info('✅ Proforma part created for business-owner', [
                        'part_id' => $part->id, 
                        'index' => $index,
                        'part_number' => $part->number
                    ]);

                    // 🔹 Move temp images and attach them (FilePond async uploads)
                    if (isset($partsData['photo'][$index]) && is_array($partsData['photo'][$index])) {
                        foreach ($partsData['photo'][$index] as $photoPath) {
                            if (!empty($photoPath) && str_contains($photoPath, 'uploads/temp/')) {
                                $tempPath = $photoPath;
                                $filename = basename($tempPath);
                                $finalPath = 'uploads/part-images/' . $filename;

                                // Move the file from temp to permanent location
                                if (Storage::disk('public')->exists($tempPath)) {
                                    Storage::disk('public')->move($tempPath, $finalPath);

                                    \App\Models\PartsImage::create([
                                        'proforma_part_id' => $part->id,
                                        'image_path' => $finalPath,
                                    ]);

                                    Log::info('✅ Temp image moved and saved for business-owner', [
                                        'from' => $tempPath,
                                        'to' => $finalPath,
                                        'proforma_part_id' => $part->id,
                                    ]);
                                } else {
                                    Log::warning('⚠️ Temp image not found for business-owner', ['path' => $tempPath]);
                                }
                            }
                        }
                    } else {
                        Log::info('ℹ️ No images found for part in business-owner request', ['index' => $index]);
                    }
                }

                // 🔹 Step 4 — Voice note (optional)
                if ($request->filled('voice_note')) {
                    try {
                        $base64 = $request->voice_note;
                        
                        // Extract the extension from the MIME type (handles codecs like audio/webm;codecs=opus)
                        if (preg_match('#^data:audio/([^;,]+)#i', $base64, $matches)) {
                            $extension = $matches[1]; // e.g. webm, mp3, wav
                        } else {
                            $extension = 'webm'; // default
                        }
                        
                        // Remove the entire data URI prefix including any codec specifications
                        $audioData = base64_decode(preg_replace('#^data:audio/[^;]+[^,]*,#i', '', $base64));
                        
                        if ($audioData === false) {
                            Log::error('❌ Voice note base64 decoding failed for business-owner');
                        } else {
                            $filename = 'voice_note_' . time() . '_' . uniqid() . '.' . $extension;
                            Storage::disk('public')->put('voice_notes/' . $filename, $audioData);
                            $proforma->update(['voice_note_path' => 'voice_notes/' . $filename]);
                            Log::info('🎤 Voice note saved for business-owner', ['filename' => $filename, 'size' => strlen($audioData)]);
                        }
                    } catch (\Exception $e) {
                        Log::error('❌ Error saving voice note for business-owner', [
                            'error' => $e->getMessage()
                        ]);
                    }
                }

                DB::commit();

                // 🔔 Broadcast to admin dashboard in real-time
                event(new ProformaCreated($proforma));

                // 🔹 Step 5 — Schedule Etera-Chereta AutoSelect
                if ($isEteraChereta) {
                    AutoSelectProformaOffers::dispatch($proforma->id)->delay(now()->addMinutes($timerMinutes));
                    Log::info('⏰ Etera-Chereta scheduled for business-owner', [
                        'proforma_id' => $proforma->id,
                        'delay_minutes' => $timerMinutes
                    ]);
                }

                return redirect()->back()->with('success', 'Proforma created successfully!');
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('❌ Proforma creation failed for business-owner', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return redirect()->back()->withErrors(['general' => 'An unexpected error occurred.'])->withInput();
            }
        })->name('business-owner.create-file');
    });

	// Received Proforma
Route::get('received-proformas', function () {
    $user = auth()->user();

    // Mark all new proformas for this user as viewed
    \App\Models\Proforma::where('poster_id', $user->id)
        ->where('status', 'completed')
        ->where('verified', true)
        ->where('is_new', true)
        ->update(['is_new' => false]);

    // Fetch updated proformas
    $proformas = \App\Models\Proforma::with('applications')
        ->where('status', 'completed')
        ->where('verified', true)
        ->where('poster_id', $user->id)
        ->orderBy('updated_at', 'desc')
        ->paginate(10);

    return view('business-owner.proformas', compact('proformas'));
});

// Proforma Details 
Route::get('proforma-details', function (Request $request) {

    $proforma = Proforma::with(['proformaInvoice', 'applications.prices'])
        ->findOrFail($request->query('proforma_id'));

    $reciept = $proforma->proformaInvoice;

    // Load applications once
    $allApplications = $proforma->applications;

    // Attach calculated price
    $allApplications = $allApplications->map(function ($application) {

        if ($application->from === 'shop' && $application->prices->isNotEmpty()) {
            $subtotal = $application->prices->sum('part_total');
            $discount = (float) ($application->discount ?? 0);

            $application->final_price = $subtotal - ($subtotal * $discount / 100);
        } else {
            $application->final_price = (float) ($application->amount ?? 0);
        }

        return $application;
    });

    // Sort applications by lowest price
    $applications = $allApplications
        ->sortBy('final_price')
        ->values();

    // Shops sorted by price
    $shops = $allApplications
        ->where('from', 'shop')
        ->sortBy('final_price')
        ->values();

    // Garages sorted by price
    $garages = $allApplications
        ->where('from', 'garage')
        ->sortBy('final_price')
        ->values();

    // Apply limits
    $requiredShops = (int) ($proforma->required_number_of_shops ?? 0);
    $requiredGarages = (int) ($proforma->required_number_of_garages ?? 0);

    if ($requiredShops > 0) {
        $applications = $applications->take($requiredShops);
        $shops = $shops->take($requiredShops);
    }

    if ($requiredGarages > 0) {
        $garages = $garages->take($requiredGarages);
    }

    // Etera Chereta (both 0): show only top 5 lowest price
    if ($requiredShops === 0 && $requiredGarages === 0) {
        $applications = $applications->take(5);
        $shops = $shops->take(5);
        $garages = $garages->take(5);
    }

    return view(
        'business-owner.proforma-details',
        compact('proforma', 'applications', 'shops', 'garages', 'reciept')
    );
});

		// Balance
		Route::get('/balance', function () {
			return view('business-owner.balance');
		});

		// Inbox
		Route::get('/profile', function () {
		    
			return view('business-owner.profile');
		});

		Route::get('/', function () {
			return view('business-owner.index');
		});

		/********************FILE RELATED ROUTES*****************************/
		Route::post('upload/{type}', [TemporaryFileController::class, 'store']);
		Route::delete('delete', [TemporaryFileController::class, 'destroy']);
	});

// Etera-Chereta Service Status Route
Route::get('/etera-chereta/status', function () {
    try {
        $cacheKey = 'etera_chereta_service_running';
        $isRunning = Cache::has($cacheKey);
        $lastCheck = Cache::get($cacheKey);
        
        // Check if the process is actually running
        $processRunning = false;
        if (PHP_OS_FAMILY === 'Windows') {
            if (function_exists('shell_exec')) {
                $output = shell_exec('tasklist /FI "IMAGENAME eq php.exe" /FO CSV 2>nul');
                $processRunning = strpos($output, 'etera-chereta:check-expiration') !== false;
            } else {
                $processRunning = false;
            }
        } else {
            if (function_exists('shell_exec')) {
                $output = shell_exec('ps aux | grep "etera-chereta:check-expiration" | grep -v grep');
                $processRunning = !empty($output);
            } else {
                $processRunning = false;
            }
        }
        
        $status = [
            'status' => $isRunning && $processRunning ? 'running' : 'stopped',
            'last_check' => $lastCheck ? $lastCheck->toISOString() : null,
            'auto_start_enabled' => true,
            'platform' => PHP_OS_FAMILY,
            'process_running' => $processRunning,
            'cache_status' => $isRunning ? 'active' : 'inactive',
            'timestamp' => now()->toISOString(),
        ];
        
        return response()->json($status);
        
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'error' => $e->getMessage(),
            'timestamp' => now()->toISOString(),
        ], 500);
    }
})->name('etera-chereta.status');




// =====================
// Accountant Dashboard
// =====================
Route::middleware(['auth'])->group(function () {

    Route::get('/finance',
        [App\Http\Controllers\AdminAnalyticsController::class, 'index']
    )->name('accountant.dashboard');

    Route::get('/finance/mark-paid/{userId}',
        [App\Http\Controllers\AdminAnalyticsController::class, 'markPaid']
    )->name('finance.markPaid');
    
        Route::post('/finance/receieve/{userId}', [App\Http\Controllers\AdminAnalyticsController::class, 'receivePayment'])
        ->name('finance.receivePayment');

});




Route::prefix('role')
    ->middleware(['auth'])
    ->group(function () {
        Route::get('/proformas', function () {
            $user = auth()->user();
            
            if ($user->role === 'garage') {
                return redirect('/garage/');
            } elseif ($user->role === 'shop') {
                return redirect('/spare-part-shops/');
            } elseif ($user->role === 'insurance') {
                return redirect('/insurance/received-proformas');
            } else {
                return redirect('/');
            }
        })->name('role.proformas');
    });

    // Temporary file upload routes
    Route::post('/upload-temp', function(\Illuminate\Http\Request $request) {
        try {
            $tempService = new \App\Services\TemporaryFileService();
            $folder = $tempService->storeFile($request->file('filepond'), $request->input('type', 'file'));
            
            return response()->json(['folder' => $folder]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Temporary file upload error: ' . $e->getMessage());
            return response()->json(['error' => 'Upload failed'], 500);
        }
    })->name('upload.temp');
    
    Route::delete('/upload-temp', function(\Illuminate\Http\Request $request) {
        try {
            $tempService = new \App\Services\TemporaryFileService();
            $tempService->deleteFile($request->input('folder'));
            
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Temporary file delete error: ' . $e->getMessage());
            return response()->json(['error' => 'Delete failed'], 500);
        }
    })->name('delete.temp');
    
    // Debug route for testing part prices
    Route::get('/debug/part-prices/{applicationId}', function($applicationId) {
        $application = \App\Models\ProformaApplication::with('prices', 'proforma.parts')->findOrFail($applicationId);
        
        return response()->json([
            'application_id' => $application->id,
            'proforma_id' => $application->proforma_id,
            'from' => $application->from,
            'amount' => $application->amount,
            'discount' => $application->discount,
            'prices_count' => $application->prices->count(),
            'prices' => $application->prices->map(function($price) {
                return [
                    'id' => $price->id,
                    'car_part_id' => $price->car_part_id,
                    'quantity' => $price->quantity,
                    'unit_price' => $price->unit_price,
                    'part_total' => $price->part_total,
                ];
            }),
            'proforma_parts' => $application->proforma->parts->map(function($part) {
                return [
                    'id' => $part->id,
                    'component' => $part->component,
                    'quantity' => $part->quantity,
                ];
            })
        ]);
    })->name('debug.part-prices');
    
    // Debug route for testing voice notes
    Route::get('/debug/voice-notes/{applicationId}', function($applicationId) {
        $application = \App\Models\ProformaApplication::with('media', 'applicationBy')->findOrFail($applicationId);
        
        return response()->json([
            'application_id' => $application->id,
            'applicant_name' => $application->applicationBy->name,
            'applicant_role' => $application->applicationBy->role,
            'voice_notes_count' => $application->getMedia('voice_notes')->count(),
            'voice_notes' => $application->getMedia('voice_notes')->map(function($voiceNote) {
                return [
                    'id' => $voiceNote->id,
                    'file_name' => $voiceNote->file_name,
                    'mime_type' => $voiceNote->mime_type,
                    'size' => $voiceNote->size,
                    'url' => $voiceNote->getUrl(),
                    'created_at' => $voiceNote->created_at,
                ];
            }),
            'all_media_count' => $application->getMedia()->count(),
            'all_media' => $application->getMedia()->map(function($media) {
                return [
                    'id' => $media->id,
                    'collection_name' => $media->collection_name,
                    'file_name' => $media->file_name,
                    'mime_type' => $media->mime_type,
                    'url' => $media->getUrl(),
                ];
            })
        ]);
    })->name('debug.voice-notes');

// Include Manager & Operator Routes
require __DIR__.'/manager_operator_routes.php';
