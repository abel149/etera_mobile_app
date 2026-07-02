<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Helpers\FcmHelper;
use App\Services\AfroMessageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    // POST /api/auth/login
    public function login(Request $request)
    {
        $request->validate([
            'phone_number' => ['required', 'string'],
            'password'     => ['required', 'string'],
            
        
        ]);
         
        $username = $request->phone_number;
        // Check if it's phone (10 digits)
        if (preg_match('/^\d{10}$/', $username)) {
            $user = User::where('phone_number', $username)->first();
        } else {
            // Otherwise treat as store_id
            $user = User::where('store_id', $username)->first();
        }
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid phone number or password.',
            ], 401);
        }

        if (!$user->approved) {
            return response()->json([
                'success' => false,
                'message' => 'Your account is pending admin approval.',
                'code'    => 'PENDING_APPROVAL',
                'data'    => [
                    'approved' => (bool) $user->approved,
                    'user'     => new UserResource($user),
                ],
            ], 403);
        }

        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'data'    => [
                'token'      => $token,
                'token_type' => 'Bearer',
                'user'       => new UserResource($user),
            ],
        ]);
    }

    private function userPayload(User $user): array
    {
        return [
            'id'           => $user->id,
            'name'         => $user->name,
            'phone_number' => $user->phone_number,
            'role'         => $user->role,
            'approved'     => (bool) $user->approved,
        ];
    }

    // POST /api/auth/logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);
    }

   
    /**
     * GET /api/v1/profile
     */
    public function profile()
    {
        return response()->json([
            'success' => true,
            'data'    => new UserResource(auth()->user()),
        ]);
    }

    /**
     * PUT /api/v1/profile
     */
    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name'         => ['sometimes', 'string', 'max:255'],
            'email'        => ['sometimes', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone_number' => ['sometimes', 'string', 'max:20', 'unique:users,phone_number,' . $user->id],
            'password'     => ['sometimes', 'string', 'min:6', 'confirmed'],
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data'    => new UserResource($user->fresh()),
        ]);
    }

    /**
     * POST /api/v1/device-token
     * Stores the FCM device token for push notifications.
     */
    public function registerDeviceToken(Request $request)
    {
        $request->validate([
            'device_token' => ['required', 'string'],
            'platform'     => ['nullable', 'in:android,ios'],
        ]);

        $request->user()->update([
            'device_token' => $request->device_token,
        ]);

        return response()->json(['success' => true, 'message' => 'Device token registered.']);
    }

    /**
     * GET /api/v1/notifications
     * Returns the user's recent database notifications.
     */
    public function notifications(Request $request)
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->take(50)
            ->get()
            ->map(fn ($n) => [
                'id'         => $n->id,
                'type'       => $n->type,
                'data'       => $n->data,
                'read_at'    => $n->read_at?->toISOString(),
                'created_at' => $n->created_at->toISOString(),
            ]);

        $unread = $request->user()->unreadNotifications()->count();

        return response()->json([
            'success' => true,
            'unread'  => $unread,
            'data'    => $notifications,
        ]);
    }

    /**
     * PUT /api/v1/notifications/read
     * Mark all notifications as read.
     */
    public function markNotificationsRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();
        return response()->json(['success' => true, 'message' => 'Marked as read.']);
    }

    /**
     * POST /api/v1/auth/forgot-password
     * Sends a password reset OTP to the user's phone number via Afromessage.
     */
    public function forgotPassword(Request $request, AfroMessageService $afroMessage)
    {
        $request->validate([
            'identifier' => ['required', 'string', 'max:50'],
        ]);

        $user = $this->findUserForPasswordReset($request->identifier);

        if (!$user) {
            return response()->json([
                'success' => true,
                'message' => 'If an account exists, a reset code has been sent.',
            ]);
        }

        $otp = (string) random_int(100000, 999999);
        $identifier = $user->phone_number;

        DB::table('password_reset_tokens')->where('email', $identifier)->delete();
        DB::table('password_reset_tokens')->insert([
            'email' => $identifier,
            'token' => Hash::make($otp),
            'created_at' => now(),
        ]);

        $to = $this->normalizeSmsPhoneNumber($user->phone_number);
        $message = "Your etera password reset code is {$otp}. It expires in 5 minutes.";

        if (!$to) {
            Log::warning('Password reset SMS phone normalization failed.', [
                'user_id' => $user->id,
                'identifier' => $request->identifier,
                'phone_number' => $user->phone_number,
            ]);

            DB::table('password_reset_tokens')->where('email', $identifier)->delete();

            return response()->json([
                'success' => false,
                'message' => 'Unable to send reset code. Please try again later.',
            ], 502);
        }

        if (!$afroMessage->send($to, $message)) {
            Log::warning('Password reset SMS provider send failed.', [
                'user_id' => $user->id,
                'identifier' => $request->identifier,
                'phone_number' => $user->phone_number,
                'normalized_phone_number' => $to,
            ]);

            DB::table('password_reset_tokens')->where('email', $identifier)->delete();

            return response()->json([
                'success' => false,
                'message' => 'Unable to send reset code. Please try again later.',
            ], 502);
        }

        return response()->json([
            'success' => true,
            'message' => 'Reset code sent to your phone number.',
        ]);
    }

    /**
     * POST /api/v1/auth/reset-password
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'identifier' => ['required', 'string', 'max:50'],
            'otp' => ['required', 'digits:6'],
            'password' => ['required', 'string', 'min:6', 'max:6', 'confirmed'],
        ]);

        $user = $this->findUserForPasswordReset($request->identifier);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired reset code.',
            ], 422);
        }

        $record = DB::table('password_reset_tokens')
            ->where('email', $user->phone_number)
            ->first();

        if (!$record || !Hash::check($request->otp, $record->token)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired reset code.',
            ], 422);
        }

        if (now()->diffInMinutes($record->created_at) > 5) {
            DB::table('password_reset_tokens')->where('email', $user->phone_number)->delete();

            return response()->json([
                'success' => false,
                'message' => 'This reset code has expired. Please request a new one.',
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        DB::table('password_reset_tokens')->where('email', $user->phone_number)->delete();
        $user->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully. You can now sign in.',
        ]);
    }

    private function findUserForPasswordReset(string $identifier): ?User
    {
        $identifier = trim($identifier);

        $query = User::query()->where('store_id', $identifier);

        $phoneCandidates = $this->phoneCandidates($identifier);
        if (!empty($phoneCandidates)) {
            $query->orWhereIn('phone_number', $phoneCandidates);
        }

        return $query->first();
    }

    private function phoneCandidates(string $phone): array
    {
        $raw = preg_replace('/\s+/', '', $phone);
        $raw = preg_replace('/[^0-9\+]/', '', $raw);
        $withoutPlus = ltrim($raw, '+');

        $candidates = [$raw, $withoutPlus];

        if (Str::startsWith($withoutPlus, '251')) {
            $local = '0' . substr($withoutPlus, 3);
            $candidates[] = $local;
            $candidates[] = '+' . $withoutPlus;
        } elseif (Str::startsWith($withoutPlus, '0')) {
            $intl = '251' . substr($withoutPlus, 1);
            $candidates[] = $intl;
            $candidates[] = '+' . $intl;
        } elseif (preg_match('/^9\d{8}$/', $withoutPlus)) {
            $candidates[] = '0' . $withoutPlus;
            $candidates[] = '251' . $withoutPlus;
            $candidates[] = '+251' . $withoutPlus;
        }

        return array_values(array_unique(array_filter($candidates)));
    }

    private function normalizeSmsPhoneNumber(string $phone): ?string
    {
        $withoutPlus = ltrim(preg_replace('/[^0-9\+]/', '', $phone), '+');

        if (preg_match('/^2519\d{8}$/', $withoutPlus)) {
            return $withoutPlus;
        }

        if (preg_match('/^09\d{8}$/', $withoutPlus)) {
            return '251' . substr($withoutPlus, 1);
        }

        if (preg_match('/^9\d{8}$/', $withoutPlus)) {
            return '251' . $withoutPlus;
        }

        return null;
    }

}
