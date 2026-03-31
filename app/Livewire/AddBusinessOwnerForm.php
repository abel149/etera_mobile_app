<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User as BusinessOwner;
use Illuminate\Support\Facades\Hash;
use Livewire\WithFileUploads;


// class AddBusinessOwnerForm extends Component
// {
//     public $name;
//     public $phone_number;
//     public $tin_number;
//     public $location;
//     public $business_license_number;
//     public $license_expiry_date;
//     public $email;
//     public $password;
//     public $password_confirmation;

//     protected $rules = [
//         'name' => 'required|string|max:255',
//         'phone_number' => 'required|string|max:15',
//         'tin_number' => 'required|string|max:50',
//         'location' => 'required|string|max:255',
//         'business_license_number' => 'required|string|max:50',
//         'license_expiry_date' => 'required|date',
//         'email' => 'required|email|unique:users,email',
//         'password' => 'required|confirmed|min:8',
//     ];

//     public function updated($propertyName)
//     {
//         $this->validateOnly($propertyName);
//     }

//     public function submit()
//     {
//         $this->validate();

//         BusinessOwner::create([
//             'name' => $this->name,
//             'phone_number' => $this->phone_number,
//             'tin_number' => $this->tin_number,
//             'location' => $this->location,
//             'business_license_number' => $this->business_license_number,
//             'license_expire_date' => $this->license_expiry_date,
//             'role' => 'business_owner',
//             'email' => $this->email,
//             'password' => Hash::make($this->password),
//         ]);

//         session()->flash('success', 'Business Owner has been added successfully.');
//         return redirect('/marketer/business-owners');
//     }

//     public function render()
//     {
//         return view('livewire.add-business-owner-form');
//     }
// }



class AddBusinessOwnerForm extends Component
{
    use WithFileUploads;
    
    public $name;
    public $phone_number;
    public $tin_number;
    public $location;
    public $business_license_number;
    public $license_expiry_date;
    public $email;
    public $password;
    public $password_confirmation;
    public $stamp_image;
    public $license_image;

    protected $rules = [
        'name' => 'required|string|max:255',
        'phone_number' => 'required|string|max:15|unique:users,phone_number',
        'tin_number' => 'required|string|max:50|unique:users,tin_number',
        'location' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|confirmed|min:8',
       
    ];

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    // public function submit()
    // {
    //     $this->validate();

    //     // Handle file uploads
    //     $stampPath = $this->stamp_image ? $this->stamp_image->store('stamps', 'public') : null;
    //     $licensePath = $this->license_image ? $this->license_image->store('licenses', 'public') : null;

    //     BusinessOwner::create([
    //         'name' => $this->name,
    //         'phone_number' => $this->phone_number,
    //         'tin_number' => $this->tin_number,
    //         'location' => $this->location,
    //         'business_license_number' => $this->business_license_number,
    //         'license_expire_date' => $this->license_expiry_date,
    //         'role' => 'business_owner',
    //         'email' => $this->email,
    //         'password' => Hash::make($this->password),
    //         'stamp_image' => $stampPath,
    //         'license_image' => $licensePath,
    //     ]);

    //     session()->flash('success', 'Business Owner has been added successfully.');
    //     return redirect('/marketer/business-owners');
    // }
    public function submit()
    {
        $this->validate();
    
        // Debugging: Check if files are uploaded
        // if ($this->stamp_image) {
        //     \Log::info('Stamp image detected:', ['file' => $this->stamp_image->getClientOriginalName()]);
        // } else {
        //     \Log::error('Stamp image NOT detected');
        // }
    
        // if ($this->license_image) {
        //     \Log::info('License image detected:', ['file' => $this->license_image->getClientOriginalName()]);
        // } else {
        //     \Log::error('License image NOT detected');
        // }
    
        // Handle file uploads
        // $stampPath = $this->stamp_image ? $this->stamp_image->store('stamps', 'public') : null;
        // $licensePath = $this->license_image ? $this->license_image->store('licenses', 'public') : null;
    
        BusinessOwner::create([
            'name' => $this->name,
            'phone_number' => $this->phone_number,
            'tin_number' => $this->tin_number,
            'location' => $this->location,
            // 'business_license_number' => $this->business_license_number,
            // 'license_expire_date' => $this->license_expiry_date,
            'role' => 'others',
            'email' => $this->email,
            'password' => Hash::make($this->password),
	    'approved' => 1,
            // 'stamp_image' => $stampPath,
            // 'license_image' => $licensePath,
        ]);
    
        // session()->flash('success', 'Business Owner has been added successfully.');
        // return redirect('/marketer/business-owners');



        session()->flash('success', 'Business Owner has been added successfully.');

$user = auth()->user(); // Get the currently authenticated user

if ($user->role === 'admin') {
    // If the user is an admin, redirect to a different page
    return redirect('/admin/business-owners');
} else {
    // If the user is not an admin, redirect to the business owners page
    return redirect('/marketer/business-owners');
}
    }
    
    public function render()
    {
        return view('livewire.add-business-owner-form');
    }
}
