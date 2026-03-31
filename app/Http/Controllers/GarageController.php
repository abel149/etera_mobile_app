<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class GarageController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
  public function store(Request $request)
{
    // Validate the input
    $request->validate([
        'name' => 'required',
        'email' => 'nullable|email|unique:users,email',
        'phone_number' => 'required|unique:users,phone_number',
        'location' => 'required',
        'password' => 'nullable|min:6|confirmed', // password can be null
        'tin_number' => 'required|unique:users,tin_number',
        'license_image' => 'required|file|image',
        'stamp_image' => 'required|file|image',
    ]);

    // If password is null, default to 123456
    $password = $request->password ?: '123456';

    // Store the images
    $licenseImagePath = $request->file('license_image')->store('public/licenses');
    $stampImagePath = $request->file('stamp_image')->store('public/stamps');

    // Create a new user with the additional fields
    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'phone_number' => $request->phone_number,
        'password' => bcrypt($password),
        'role' => 'garage',  // Set role to garage
        'location' => $request->location,
        'tin_number' => $request->tin_number,
        'registered_by' => auth()->user()->id,
        'license_image' => $licenseImagePath,
        'stamp_image' => $stampImagePath,
    ]);

    // Redirect based on user role
    if (auth()->user()->role === 'admin') {
        return redirect()->to('/admin/garages')->with(['user' => $user]);
    } elseif (auth()->user()->role === 'marketer') {
        return redirect()->to('/marketer/garages')->with(['user' => $user]);
    }
}



    

    // Other methods remain unchanged (index, show, edit, etc.)
    public function edit($id)
    {
        // Check if the authenticated user is an admin or marketer
        if (!Auth::check() || !in_array(Auth::user()->role, ['admin', 'marketer'])) {
            return redirect()->back()->with('error', 'Unauthorized access');
        }
    
        // Find the garage by ID
        $garage = User::findOrFail($id);
    
        // Return different views based on the user role
        return match (Auth::user()->role) {
            'admin' => view('admin.users.garages.edit', compact('garage')),
            'marketer' => view('marketer.users.garages.edit', compact('garage')),
            default => redirect()->back()->with('error', 'Unauthorized access'),
        };
    }





    public function update(Request $request, $id)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'phone_number' => 'required|string|max:255',
        'tin_number' => 'required|string|max:255',
        'location' => 'required|string|max:255',
        'business_license_number' => 'nullable|string|max:255',
        'license_expire_date' => 'nullable|date',
        'email' => 'nullable|email|max:255',
        'license_image' => 'nullable|file|image',
        'stamp_image' => 'nullable|file|image',
    ]);

    $garage = User::findOrFail($id);

    $data = $request->only([
        'name', 'phone_number', 'tin_number', 'location',
        'business_license_number', 'license_expire_date', 'email'
    ]);

    // Handle license image upload
    if ($request->hasFile('license_image')) {
        $licenseImagePath = $request->file('license_image')->store('public/licenses');
        $data['license_image'] = $licenseImagePath;
    }

    // Handle stamp image upload
    if ($request->hasFile('stamp_image')) {
        $stampImagePath = $request->file('stamp_image')->store('public/stamps');
        $data['stamp_image'] = $stampImagePath;
    }

    $garage->update($data);

    if (auth()->user()->role === 'admin') {
        return redirect()->to('admin/garages')->with(['user' => $garage]);
    } elseif (auth()->user()->role === 'marketer') {
        return redirect()->to('/marketer/garages')->with(['user' => $garage]);
    }
}



    public function destroy($id)
    {
        $garage = User::findOrFail($id); // Get the insurance by ID
        $garage->delete(); // Delete the insurance record

        return redirect()->to('admin/garages');    
    }








}
