<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BrandUser;
use App\Models\User;
use Illuminate\Support\Facades\Storage; // Correct import for Storage
use Illuminate\Support\Facades\Auth;


class ShopController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
public function store(Request $request)
{
    $request->validate([
        'name' => 'required',
        'email' => 'nullable|email|unique:users,email',
        'phone_number' => 'required|unique:users,phone_number',
        'location' => 'required',

        // Password is optional; if null, default will be applied
        'password' => 'nullable|min:6|max:6|confirmed',

        'tin_number' => 'required|unique:users,tin_number',
        'brands' => 'required',
        'brands.*' => 'required|exists:brands,id',

        'license_image' => 'required|file|image',
        'stamp_image' => 'required|file|image',
    ]);

    // Default password handling
    $password = $request->password ?? '123456';

    // Generate UNIQUE store_id
    do {
        $lastNumber = User::whereNotNull('store_id')
            ->selectRaw("MAX(CAST(SUBSTRING(store_id, 4) AS UNSIGNED)) as max_id")
            ->value('max_id');

        $newStoreId = 'ES-' . str_pad(($lastNumber + 1), 4, '0', STR_PAD_LEFT);

    } while (User::where('store_id', $newStoreId)->exists());

    // Upload images
    $licenseImagePath = $request->file('license_image')->store('public/licenses');
    $stampImagePath = $request->file('stamp_image')->store('public/stamps');

    // Create the shop user
    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'phone_number' => $request->phone_number,
        'password' => bcrypt($password),
        'location' => $request->location,
        'role' => 'shop',
        'tin_number' => $request->tin_number,
        'registered_by' => auth()->user()->id,
        'license_image' => $licenseImagePath,
        'stamp_image' => $stampImagePath,
        'store_id' => $newStoreId,
    ]);

    // Attach brands
    foreach ($request->brands as $brand) {
        BrandUser::create([
            'brand_id' => $brand,
            'user_id' => $user->id,
        ]);
    }

    // Redirect based on role
    if (auth()->user()->role === 'admin') {
        return redirect()->to('/admin/spare-part-shops')->with(['user' => $user]);
    }

    if (auth()->user()->role === 'marketer') {
        return redirect()->to('/marketer/spare-part-shops')->with(['user' => $user]);
    }
}


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
//     public function edit(string $id)
//     {
      
//  // Find the shop by ID
//     $shop = User::findOrFail($id);
    
//     // You may want to fetch the brands and pass them for the form
//     $brands = BrandUser::where('user_id', $id)->pluck('brand_id')->toArray();

//     return view('admin.users.spare-part-shops.edit', compact('shop', 'brands'));
//     }
// public function edit(string $id)
// {
//     // Find the shop by ID
//     $shop = User::findOrFail($id);

//     // Fetch the brands associated with the shop (user)
//     // This will give you an array of brand IDs that the shop is associated with
//     $brands = BrandUser::where('user_id', $id)->pluck('brand_id')->toArray();

//     // Fetch all available brands to display in the form (optional)

//     return view('admin.users.spare-part-shops.edit', compact('shop', 'brands'));
// }

public function edit(string $id)
{

     // Check if the authenticated user is an admin or marketer
     if (!Auth::check() || !in_array(Auth::user()->role, ['admin', 'marketer'])) {
        return redirect()->back()->with('error', 'Unauthorized access');
    }


    // Find the shop by ID
    $shop = User::findOrFail($id);

    // Fetch the brands associated with the shop (user)
    $brands = BrandUser::where('user_id', $id)->pluck('brand_id')->toArray();

    // Fetch all available brands to display in the form (optional)
    $allBrands = \App\Models\Brand::latest()->get();

    // return view('admin.users.spare-part-shops.edit', compact('shop', 'brands', 'allBrands'));
    // Return different views based on the user role
    if (Auth::user()->role === 'admin') {
        return view('admin.users.spare-part-shops.edit', compact('shop', 'brands', 'allBrands'));
    } elseif (Auth::user()->role === 'marketer') {
        return view('marketer.users.spare-part-shops.edit', compact('shop', 'brands', 'allBrands'));
    }

    // Default redirect if none match (optional safeguard)
    return redirect()->back()->with('error', 'Unauthorized access');





}
    // /**
    //  * Update the specified resource in storage.
    //  */
    // public function update(Request $request, string $id)
    // {
    //     $request->validate([
    //         'name' => 'required',
    //         'email' => 'required|email|unique:users,email,' . $id,
    //         'phone_number' => 'required|unique:users,phone_number,' . $id,
    //         'location' => 'required',
    //         'password' => 'nullable|min:8|confirmed', // Password is optional during update
    //         'business_license_number' => 'required|unique:users,business_license_number,' . $id,
    //         'license_expire_date' => 'required|date',
    //         'tin_number' => 'required|unique:users,tin_number,' . $id,
    //         'brands' => 'required',
    //         'brands.*' => 'required|exists:brands,id', // Ensure the brand exists
    //         'license_image' => 'nullable|file|image',
    //         'stamp_image' => 'nullable|file|image',
    //     ]);
    
    //     // Find the shop to update
    //     $shop = User::findOrFail($id);
    
    //     // Handle password update if it's provided
    //     if ($request->filled('password')) {
    //         $shop->password = bcrypt($request->password);
    //     }
    
    //     // Handle image update if a new image is uploaded
    //     if ($request->hasFile('license_image')) {
    //         // Delete the old image
    //         if (file_exists(storage_path('app/public/licenses' . $shop->license_image))) {
    //             unlink(storage_path('app/public/licenses' . $shop->license_image));
    //         }
    //         $shop->license_image = $request->file('license_image')->store('licenses');
    //     }
    
    //     if ($request->hasFile('stamp_image')) {
    //         // Delete the old image
    //         if (file_exists(storage_path('app/public/stamps' . $shop->stamp_image))) {
    //             unlink(storage_path('app/public/stamps' . $shop->stamp_image));
    //         }
    //         $shop->stamp_image = $request->file('stamp_image')->store('stamps');
    //     }
    
    //     // Update other fields
    //     $shop->name = $request->name;
    //     $shop->email = $request->email;
    //     $shop->phone_number = $request->phone_number;
    //     $shop->location = $request->location;
    //     $shop->business_license_number = $request->business_license_number;
    //     $shop->license_expire_date = $request->license_expire_date;
    //     $shop->tin_number = $request->tin_number;
    
    //     // Save the updated shop
    //     $shop->save();
    
    //     // Remove existing brands and add new ones
    //     BrandUser::where('user_id', $id)->delete();
    //     foreach ($request->brands as $brand) {
    //         BrandUser::create([
    //             'brand_id' => $brand,
    //             'user_id' => $shop->id,
    //         ]);
    //     }
    
    //     // Redirect based on the user role
    //     if (auth()->user()->role === 'admin') {
    //         return redirect()->to('/admin/spare-part-shops')->with('success', 'Shop updated successfully');
    //     } elseif (auth()->user()->role === 'marketer') {
    //         return redirect()->to('/marketer/spare-part-shops')->with('success', 'Shop updated successfully');
    //     }
    // }
    

    // /**
    //  * Remove the specified resource from storage.
    //  */
    // public function destroy(string $id)
    // {
    //     // Find the shop by ID
    //     $shop = User::findOrFail($id);
    
    //     // Delete the shop's associated brands
    //     BrandUser::where('user_id', $id)->delete();
    
    //     // Delete the shop's images if they exist
    //     if (file_exists(storage_path('app/public/licenses' . $shop->license_image))) {
    //         unlink(storage_path('app/public/licenses' . $shop->license_image));
    //     }
    //     if (file_exists(storage_path('app/public/stamps' . $shop->stamp_image))) {
    //         unlink(storage_path('app/public/licenses' . $shop->stamp_image));
    //     }
    
    //     // Delete the shop
    //     $shop->delete();
    
    //     // Redirect with a success message
    //     return redirect()->route('edit-shop', ['id' => $id])->with('success', 'Shop deleted successfully');
    // }
    


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {


        
        $request->validate([
            'name' => 'required',
            'email' => 'nullable|email|unique:users,email,' . $id,
            'phone_number' => 'required|unique:users,phone_number,' . $id,
            'location' => 'required',
            // 'business_license_number' => 'required|unique:users,business_license_number,' . $id,
            // 'license_expire_date' => 'required|date',
            'tin_number' => 'required|unique:users,tin_number,' . $id,
            'brands' => 'required',
            'brands.*' => 'required|exists:brands,id', // Ensure the brand exists
            'license_image' => 'nullable|file|image',
            'stamp_image' => 'nullable|file|image',


        ]);
    
        // Find the shop to update
        $shop = User::findOrFail($id);
    
    
        // Handle image update if a new image is uploaded
        if ($request->hasFile('license_image')) {
            // Delete the old image if it exists
            if ($shop->license_image && Storage::exists('public/licenses/' . $shop->license_image)) {
                Storage::delete('public/licenses/' . $shop->license_image);
            }
            // Store the new license image
            $shop->license_image = $request->file('license_image')->store('licenses', 'public');
        }
    
        if ($request->hasFile('stamp_image')) {
            // Delete the old image if it exists
            if ($shop->stamp_image && Storage::exists('public/stamps/' . $shop->stamp_image)) {
                Storage::delete('public/stamps/' . $shop->stamp_image);
            }
            // Store the new stamp image
            $shop->stamp_image = $request->file('stamp_image')->store('stamps', 'public');
        }
    
        // Update other fields
        $shop->name = $request->name;
        $shop->email = $request->email;
        $shop->phone_number = $request->phone_number;
        $shop->location = $request->location;
        // $shop->business_license_number = $request->business_license_number;
        // $shop->license_expire_date = $request->license_expire_date;
        $shop->tin_number = $request->tin_number;


        // Save the updated shop
        $shop->save();
    
        // Remove existing brands and add new ones
        BrandUser::where('user_id', $id)->delete();
        foreach ($request->brands as $brand) {
            BrandUser::create([
                'brand_id' => $brand,
                'user_id' => $shop->id,
            ]);
        }
    
        // Redirect based on the user role
        if (auth()->user()->role === 'admin') {
            return redirect()->to('/admin/spare-part-shops')->with('success', 'Shop updated successfully');
        } elseif (auth()->user()->role === 'marketer') {
            return redirect()->to('/marketer/spare-part-shops')->with('success', 'Shop updated successfully');
        }
    }
    
    
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Find the shop by ID
        $shop = User::findOrFail($id);
    
        // Delete the shop's associated brands
        BrandUser::where('user_id', $id)->delete();
    
        // Delete the shop's images if they exist
        if ($shop->license_image && Storage::exists('public/licenses/' . $shop->license_image)) {
            Storage::delete('public/licenses/' . $shop->license_image);
        }
        if ($shop->stamp_image && Storage::exists('public/stamps/' . $shop->stamp_image)) {
            Storage::delete('public/stamps/' . $shop->stamp_image);
        }
    
        // Delete the shop
        $shop->delete();
    
        // Redirect with a success message
        return redirect()->to('admin/spare-part-shops')->with('success', 'Shop deleted successfully');    

   
    }
    

}
