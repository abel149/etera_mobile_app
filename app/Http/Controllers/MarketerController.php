<?php

namespace App\Http\Controllers;

use App\Models\User;  // Changed to User model
use Illuminate\Http\Request;

class MarketerController extends Controller
{



  
    // Show the form for editing the user (marketer)
    public function edit($id)
    {
        // Find the user (marketer) by ID
        $marketer = User::findOrFail($id);

        // Return the edit view with the user data
        return view('admin.users.marketers.edit', compact('marketer'));
    }

    // Update the user's (marketer's) data in the database
    public function update(Request $request, $id)
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
    public function destroy($id)
    {
        // Find the user (marketer) by ID
        $marketer = User::findOrFail($id);

        // Delete the user
        $marketer->delete();

        // Redirect with a success message
        return redirect()->to('admin/marketers');    
    }
}
