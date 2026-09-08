<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\ProformaApplication;
use App\Models\Proforma;
use App\Models\Inbox;
use App\Models\Partial;
use App\Models\PaidUser;
use App\Models\BrandUser;

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
        'license_image' => 'nullable|file|image',
        'stamp_image' => 'nullable|file|image',
    ]);

    // If password is null, default to 123456
    $password = $request->password ?: '123456';

    // Store the images (optional)
    $licenseImagePath = $request->hasFile('license_image') ? $request->file('license_image')->store('public/licenses') : null;
    $stampImagePath = $request->hasFile('stamp_image') ? $request->file('stamp_image')->store('public/stamps') : null;

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
        'shop_garage' => $request->has('shop_garage') ? 1 : 0,
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

    // Handle license image - FilePond async upload, direct file, or removal
    if ($request->filled('remove_license_image') && $request->remove_license_image === '1') {
        if ($garage->license_image && \Storage::disk('public')->exists($garage->license_image)) {
            \Storage::disk('public')->delete($garage->license_image);
        }
        $data['license_image'] = null;
    } elseif ($request->filled('license_image_data')) {
        $tempPath = $request->license_image_data;
        if (\Storage::disk('public')->exists($tempPath)) {
            $filename = time() . '_' . basename($tempPath);
            $newPath = 'licenses/' . $filename;
            \Storage::disk('public')->move($tempPath, $newPath);
            // Delete old image if exists
            if ($garage->license_image && \Storage::disk('public')->exists($garage->license_image)) {
                \Storage::disk('public')->delete($garage->license_image);
            }
            $data['license_image'] = $newPath;
        }
    } elseif ($request->hasFile('license_image')) {
        $data['license_image'] = $request->file('license_image')->store('licenses', 'public');
    }

    // Handle stamp image - FilePond async upload, direct file, or removal
    if ($request->filled('remove_stamp_image') && $request->remove_stamp_image === '1') {
        if ($garage->stamp_image && \Storage::disk('public')->exists($garage->stamp_image)) {
            \Storage::disk('public')->delete($garage->stamp_image);
        }
        $data['stamp_image'] = null;
    } elseif ($request->filled('stamp_image_data')) {
        $tempPath = $request->stamp_image_data;
        if (\Storage::disk('public')->exists($tempPath)) {
            $filename = time() . '_' . basename($tempPath);
            $newPath = 'stamps/' . $filename;
            \Storage::disk('public')->move($tempPath, $newPath);
            // Delete old image if exists
            if ($garage->stamp_image && \Storage::disk('public')->exists($garage->stamp_image)) {
                \Storage::disk('public')->delete($garage->stamp_image);
            }
            $data['stamp_image'] = $newPath;
        }
    } elseif ($request->hasFile('stamp_image')) {
        $data['stamp_image'] = $request->file('stamp_image')->store('stamps', 'public');
    }

    if ($request->has('shop_garage_form')) {
        $data['shop_garage'] = $request->has('shop_garage') ? 1 : 0;
    }

    $garage->update($data);

    if (in_array(auth()->user()->role, ['admin', 'superadmin'])) {
        return redirect()->to('/admin/garages')->with('success', 'Garage updated successfully');
    } elseif (auth()->user()->role === 'marketer') {
        return redirect()->to('/marketer/garages')->with('success', 'Garage updated successfully');
    } elseif (auth()->user()->role === 'garage') {
        return redirect()->to('/garage/proformas')->with('success', 'Profile updated successfully');
    }

    return redirect()->to('/admin/garages')->with('success', 'Garage updated successfully');
}



    public function destroy($id)
    {
        $garage = User::findOrFail($id);

        DB::beginTransaction();
        try {
            // Clean up related records that lack ON DELETE CASCADE
            ProformaApplication::where('application_by', $garage->id)->delete();
            Proforma::where('poster_id', $garage->id)->delete();

            // Records with cascade will be auto-deleted, but clean explicitly to be safe
            Inbox::where('user_id', $garage->id)->delete();
            Partial::where('user_id', $garage->id)->delete();
            PaidUser::where('user_id', $garage->id)->delete();
            BrandUser::where('user_id', $garage->id)->delete();

            $garage->delete();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to delete garage: ' . $e->getMessage());
        }

        return redirect()->to('admin/garages')->with('success', 'Garage deleted successfully.');
    }








}
