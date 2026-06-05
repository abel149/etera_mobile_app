<?php

namespace App\Http\Controllers\Api;

use App\Models\User;  // Changed to User model
use Illuminate\Http\Request;

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

            // If password is null, default to 123456
            $password = $request->password ?: '123456';

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'password' => bcrypt($password),
                'role' => 'Marketer',
                'registered_by' => auth()->user()->id
            ]);

             return response()->json([
                'success' => true, 
                'message' => 'Employee createdsuccessfully.',
                'data' => [
                    'user' => $user,
                ]
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

        // Return the edit view with the user data
        return view('admin.users.marketers.edit', compact('marketer'));
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
        // Hash the new password using bcrypt
        $marketer->password = bcrypt($request->password);
        $marketer->save();  // Save the new password
    }
    
        // Redirect with a success message
        return redirect()->to('admin/marketers')->with('success', 'Marketer updated successfully.');
    }
    
    // Delete the user (marketer) from the database
    public function destroyMarketer($id)
    {
        // Find the user (marketer) by ID
        $marketer = User::findOrFail($id);

        // Delete the user
        $marketer->delete();

        // Redirect with a success message
        return redirect()->to('admin/marketers');    
    }
}
