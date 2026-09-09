<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MarketerController extends Controller
{

     public function createMarketer(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required',
                'email' => 'nullable|email|unique:users,email',
                'phone_number' => 'required|unique:users,phone_number',
                'password' => 'nullable|min:6' // password can be null
            ]);

            // Use provided password or generate a secure random one
            $plainPassword = $request->filled('password') ? $request->password : Str::random(10);

            $user = User::create([
                'name'          => $request->name,
                'email'         => $request->email,
                'phone_number'  => $request->phone_number,
                'password'      => Hash::make($plainPassword),
                'role'          => 'marketer',
                'registered_by' => auth()->user()->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Marketer created successfully.',
                'data'    => [
                    'user'          => $user,
                    'temp_password' => $request->filled('password') ? null : $plainPassword,
                ],
            ]);
    
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Flatten validation errors to a single string for easier display
            $errors = $e->errors();
            $errorMessages = [];
            foreach ($errors as $field => $messages) {
                foreach ($messages as $message) {
                    // Customize the duplicate phone message
                    if ($field === 'phone_number' && str_contains($message, 'already been taken')) {
                        $errorMessages[] = 'This phone number already exists.';
                    } else {
                        $errorMessages[] = $message;
                    }
                }
            }
           return response()->json([
        'success' => false,
        'message' => implode(' ', $errorMessages),
        'errors' => $e->errors(),
    ], 422);

} catch (\Exception $e) {

    return response()->json([
        'success' => false,
        'message' => 'An unexpected error occurred. Please try again.',
    ], 500);
}
    }


  
    // Show the form for editing the user (marketer)
    public function editMarketer($id)
    {
        // Find the user (marketer) by ID
        $marketer = User::findOrFail($id);

        // Return JSON for API
        return response()->json([
            'success' => true,
            'data' => [
                'marketer' => $marketer
            ]
        ]);
    }

    // Update the user's (marketer's) data in the database
    public function updateMarketer(Request $request, $id)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:15',  // Add validation for phone number
            'email' => 'nullable|email|max:255|unique:users,email,' . $id,
            'password' => 'nullable|string|min:6|confirmed',  // Only validate if password is provided
        ]);
    
        // Find the user (marketer) by ID
        $marketer = User::findOrFail($id);
    
        // Update the user's data
        $marketer->update([
            'name' => $request->name,
            'phone_number' => $request->phone_number,  // Update phone number
            'email' => $request->email,
        ]);
    
       // Check if a new password is provided and update it
    if ($request->filled('password')) {
        $marketer->password = Hash::make($request->password);
        $marketer->save();
    }

        // Return JSON for API
        return response()->json([
            'success' => true,
            'message' => 'Marketer updated successfully.'
        ]);
    }
    
    // Delete the user (marketer) from the database
    public function destroyMarketer($id)
    {
        // Find the user (marketer) by ID
        $marketer = User::findOrFail($id);

        // Delete the user
        $marketer->delete();

        // Return JSON for API
        return response()->json([
            'success' => true,
            'message' => 'Marketer deleted successfully.'
        ]);
    }
}
