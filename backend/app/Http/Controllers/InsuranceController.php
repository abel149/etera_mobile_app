<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
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
                'password' => 'nullable|min:6' // password can be null
            ]);

            // If password is null, default to 123456
            $password = $request->password ?: '123456';

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'password' => bcrypt($password),
                'role' => 'insurance',
                'registered_by' => auth()->user()->id
            ]);

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
        $insurance->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
        ]);
    
        // return redirect()->to('admin/insurances');

   
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
