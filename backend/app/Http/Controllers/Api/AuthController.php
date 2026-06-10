<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Helpers\FcmHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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

}
