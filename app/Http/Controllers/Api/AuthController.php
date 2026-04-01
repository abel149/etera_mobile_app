<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
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
                    'user'     => $this->userPayload($user),
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
                'user'       => $this->userPayload($user),
            ],
        ]);
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

    // GET /api/auth/me
    public function me(Request $request)
    {
        return response()->json([
            'success' => true,
            'data'    => $this->userPayload($request->user()),
        ]);
    }

    // GET /api/brands  — helper for registration (public)
    public function brands()
    {
        $brands = \App\Models\Brand::select('id', 'name')->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data'    => $brands,
        ]);
    }

    private function userPayload(User $user): array
    {
        return [
            'id'           => $user->id,
            'name'         => $user->name,
            'email'        => $user->email,
            'phone_number' => $user->phone_number,
            'role'         => $user->role,
            'store_id'     => $user->store_id,
            'approved'     => $user->approved,
            'balance'      => $user->balance,
            'location'     => $user->location,
            'created_at'   => $user->created_at?->toISOString(),
        ];
    }
}
