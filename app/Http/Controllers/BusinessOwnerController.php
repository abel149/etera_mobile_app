<?php
namespace App\Http\Controllers;

use App\Models\User;  // Using User model for business owners
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BusinessOwnerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $businessOwners = User::all(); // Fetch all business owners
        return view('admin.users.business-owners.index', compact('businessOwners'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.users.business-owners.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validation logic (example, you can customize it)
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'phone_number' => 'required|unique:users,phone_number',
            'tin_number' => 'required|unique:users,tin_number',
            // 'business_license_number' => 'required|unique:users,business_license_number',
            'business_license_number' => 'unique:users,business_license_number',

            'license_expire_date' => 'required|date',
        ]);

        // Store the business owner data
        User::create($request->all());

        return redirect()->route('admin.business-owners.index')->with('success', 'Business Owner created successfully!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    // public function edit($id)
    // {
    //     $businessOwner = User::findOrFail($id);
    //     return view('admin.users.business-owners.edit', compact('businessOwner'));
    // }



    public function edit($id)
    {
        // Check if the authenticated user is an admin or marketer
        if (!Auth::check() || !in_array(Auth::user()->role, ['admin', 'marketer'])) {
            return redirect()->back()->with('error', 'Unauthorized access');
        }
    
        // Get the insurance by ID
        $businessOwner = User::findOrFail($id);
    
        // Return different views based on the user role
        if (Auth::user()->role === 'admin') {
            return view('admin.users.business-owners.edit', compact('businessOwner'));
        } elseif (Auth::user()->role === 'marketer') {
            return view('marketer.users.business-owners.edit', compact('businessOwner'));
        }
    
        // Default redirect if none match (optional safeguard)
        return redirect()->back()->with('error', 'Unauthorized access');
    }












    

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $id,
            'phone_number' => 'required|unique:users,phone_number,' . $id,
            'tin_number' => 'required|unique:users,tin_number,' . $id,
            'password' => 'nullable|min:8|confirmed',  // Ensure password and confirmation match
        ]);
    
        // Find the business owner to update
        $businessOwner = User::findOrFail($id);
    
        // Handle password update if it's provided
        if ($request->filled('password')) {
            $businessOwner->password = bcrypt($request->password);
        }
    
        // Update other fields
        $businessOwner->name = $request->name;
        $businessOwner->email = $request->email;
        $businessOwner->phone_number = $request->phone_number;
        $businessOwner->tin_number = $request->tin_number;
    
        // Save the updated business owner
        $businessOwner->save();
    
    


        if (auth()->user()->role === 'admin') {
            return redirect()->to('admin/business-owners')->with(['user' => $businessOwner]);
        } elseif (auth()->user()->role === 'marketer') {
            return redirect()->to('/marketer/business-owners')->with(['user' => $businessOwner]);
        }

   
    }
    
    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $businessOwner = User::findOrFail($id);
        $businessOwner->delete();

        return redirect()->to('admin/business-owners');    
    }










}

