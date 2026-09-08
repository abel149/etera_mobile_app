<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\InsuranceCost;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
class InsuranceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
    
        // Fetch insurances based on the search query
        $insurances = User::where('role', 'insurance')
            ->when($search, function ($query, $search) {
                return $query->where('name', 'like', '%' . $search . '%')
                             ->orWhere('email', 'like', '%' . $search . '%');
            })
            ->get();
    
// return view('insurances', compact('insurances'));
return redirect()->to('/admin/insurances');

        
    }
    
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.insurances.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required',
                'email' => 'nullable|email|unique:users,email',
                'phone_number' => 'required|unique:users,phone_number',
                'password' => 'nullable|min:6', // password can be null
                'stamp_image' => 'required|file|image|max:10240',
                'insured_cost' => 'nullable|numeric|min:0',
                'insurance_proforma' => 'nullable|numeric|min:0',
            ]);

            // If password is null, default to 123456
            $password = $request->password ?: '123456';

            // Upload stamp image (use 'public' disk for consistent path format)
            $stampImagePath = $request->file('stamp_image')->store('stamps', 'public');

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'password' => bcrypt($password),
                'role' => 'insurance',
                'registered_by' => auth()->user()->id,
                'stamp_image' => $stampImagePath,
            ]);

            if ($request->filled('insured_cost') || $request->filled('insurance_proforma')) {
                InsuranceCost::create([
                    'user_id'            => $user->id,
                    'insured_cost'       => $request->insured_cost ?: null,
                    'insurance_proforma' => $request->insurance_proforma ?: null,
                ]);
            }

            if (auth()->user()->role === 'admin') {
                return redirect()->to('/admin/insurances')->with(['user' => $user]);
            } else {
                return redirect()->to('/marketer/insurances')->with(['user' => $user]);
            }
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
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => implode(' ', $errorMessages)]);
        } catch (\Exception $e) {
            // Catch any other unexpected errors (e.g., database issues)
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'An unexpected error occurred. Please try again.']);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    // public function ($id)
    // {
    //     $insurance = User::findOrFail($id); // Get the insurance by ID
    //     return view('admin.users.insurances.edit', compact('insurance')); // Pass to view for editing
  
      

       
    // }
    public function edit($id)
    {
        // Check if the authenticated user is an admin or marketer
        if (!Auth::check() || !in_array(Auth::user()->role, ['admin', 'marketer'])) {
            return redirect()->back()->with('error', 'Unauthorized access');
        }
    
        // Get the insurance by ID
        $insurance = User::findOrFail($id);
    
        // Return different views based on the user role
        if (Auth::user()->role === 'admin') {
            return view('admin.users.insurances.edit', compact('insurance'));
        } elseif (Auth::user()->role === 'marketer') {
            return view('marketer.users.insurances.edit', compact('insurance'));
        }
    
        // Default redirect if none match (optional safeguard)
        return redirect()->back()->with('error', 'Unauthorized access');
    }



    public function update(Request $request, $id)
    {
        $insurance = User::findOrFail($id);
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required',
            'email' => 'nullable|email|unique:users,email,' . $id,
            'phone_number' => 'required|unique:users,phone_number,' . $id,
            'stamp_image' => 'nullable|file|image|max:10240',
            'insured_cost' => 'nullable|numeric|min:0',
            'insurance_proforma' => 'nullable|numeric|min:0',
        ]);

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
        ];

        // Upload new stamp image if provided
        if ($request->hasFile('stamp_image')) {
            // Delete old stamp image if exists
            if ($insurance->stamp_image) {
                $oldPath = preg_replace('#^public/#', '', $insurance->stamp_image);
                Storage::disk('public')->delete($oldPath);
            }
            $updateData['stamp_image'] = $request->file('stamp_image')->store('stamps', 'public');
        }

        $insurance->update($updateData);

        InsuranceCost::updateOrCreate(
            ['user_id' => $insurance->id],
            [
                'insured_cost'       => $request->insured_cost ?: null,
                'insurance_proforma' => $request->insurance_proforma ?: null,
            ]
        );

        if (auth()->user()->role === 'admin') {
            return redirect()->to('admin/insurances')->with(['user' => $user]);
        } elseif (auth()->user()->role === 'marketer') {
            return redirect()->to('/marketer/insurances')->with(['user' => $user]);
        }
    }
    








    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $insurance = User::findOrFail($id); // Get the insurance by ID
        $insurance->delete(); // Delete the insurance record

        return redirect()->to('admin/insurances');    
    }



    
}
