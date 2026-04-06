<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User; // Make sure you import the User model

class MarketerBusinessController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $businessOwners = User::all(); // Fetch all business owners
        return view('marketer.users.business-owners.index', compact('businessOwners'));
    }

    /**
     * Show the form for creating a new resource.
     */
    // public function create()
    // {
    //     return view('marketer.users.business-owners.create');
    // }

    /**
     * Store a newly created resource in storage.
     */
    // public function store(Request $request)
    // {
    //     // Validation logic (example, you can customize it)
    //     $request->validate([
    //         'name' => 'required',
    //         'email' => 'required|email|unique:users,email',
    //         'phone_number' => 'required|unique:users,phone_number',
    //         'tin_number' => 'required|unique:users,tin_number',
    //         'business_license_number' => 'required|unique:users,business_license_number',
    //         'license_expire_date' => 'required|date',
    //     ]);

    //     // Store the business owner data
    //     User::create($request->all());

    //     return redirect()->route('marketer.business-owners.index')->with('success', 'Business Owner created successfully!');
    // }

    /**
     * Show the form for editing the specified resource.
     */
    // public function edit($id)
    // {
    //     $businessOwner = User::findOrFail($id);
    //     return view('marketer.users.business-owners.edit', compact('businessOwner'));
    // }
//  public function edit($id)
//     {
//         $businessOwner = User::findOrFail($id); // Fetch the business owner by ID
//         // return view('marketer.users.business-owners.edit', compact('businessOwner')); // Return the edit view with the business owner data
//     }
    /**
     * Update the specified resource in storage.
     */
    // public function update(Request $request, string $id)
    // {
    //     // Validate the input, excluding the current user from unique constraints
    //     $request->validate([
    //         'name' => 'required',
    //         'email' => 'required|email|unique:users,email,' . $id,
    //         'phone_number' => 'required|unique:users,phone_number,' . $id,
    //         'tin_number' => 'required|unique:users,tin_number,' . $id,
    //         'business_license_number' => 'required|unique:users,business_license_number,' . $id,
    //         'license_expire_date' => 'required|date',
    //         'password' => 'nullable|min:8|confirmed',  // Ensure password and confirmation match
    //     ]);
    
    //     // Find the business owner to update
    //     $businessOwner = User::findOrFail($id);
    
    //     // Handle password update if it's provided
    //     if ($request->filled('password')) {
    //         $businessOwner->password = bcrypt($request->password);
    //     }
    
    //     // Update other fields
    //     $businessOwner->name = $request->name;
    //     $businessOwner->email = $request->email;
    //     $businessOwner->phone_number = $request->phone_number;
    //     $businessOwner->tin_number = $request->tin_number;
    //     $businessOwner->business_license_number = $request->business_license_number;
    //     $businessOwner->license_expire_date = $request->license_expire_date;
    
    //     // Save the updated business owner
    //     $businessOwner->save();
    
    //     // Redirect back with a success message
    //     return redirect()->route('marketer.business-owners.index')->with('success', 'Business Owner updated successfully!');
    // }
    
    /**
     * Remove the specified resource from storage.
     */
    // public function destroy($id)
    // {
    //     $businessOwner = User::findOrFail($id);
    //     $businessOwner->delete();

    //     return redirect()->route('marketer.business-owners.index')->with('success', 'Business Owner deleted successfully!');
    // }
}
