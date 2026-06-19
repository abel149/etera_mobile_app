<?php

namespace App\Http\Controllers\Api;

use App\Events\ProformaCreated;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApplicationResource;
use App\Http\Resources\PartResource;
use App\Http\Resources\ProformaResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\WithdrawalResource;
use App\Jobs\AutoSelectProformaOffers;
use App\Models\Inbox;
use App\Models\Proforma;
use App\Models\ProformaApplication;
use App\Models\User;
use App\Models\WithdrawalRequest;
use App\Notifications\ProformaApplicationReceived;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GarageController extends Controller
{
    private function getOwnerId(): int
    {
        $user = auth()->user();
        return $user->registered_by ?? $user->id;
    }

    public function dashboard()
    {
        $ownerId = $this->getOwnerId();
        $owner   = User::find($ownerId);

        $applications = ProformaApplication::with('proforma')
            ->where('application_by', $ownerId)
            ->get();

        $inboxCount = Inbox::where('user_id', $ownerId)->count();

        return response()->json([
            'success' => true,
            'data'    => [
                'balance'         => (float) $owner->balance,
                'inbox_count'     => $inboxCount,
                'total'           => $applications->count(),
                'pending_count'   => $applications->filter(
                    fn($a) => in_array(optional($a->proforma)->status, ['pending', 'opened', 'published'])
                )->count(),
                'closed_count'    => $applications->filter(
                    fn($a) => optional($a->proforma)->status === 'closed'
                )->count(),
                'completed_count' => $applications->filter(
                    fn($a) => optional($a->proforma)->status === 'completed'
                )->count(),
            ],
        ]);
    }

    public function myApplications()
    {
        $ownerId = $this->getOwnerId();

        $applications = ProformaApplication::with(['proforma.brand', 'proforma.parts'])
            ->where('application_by', $ownerId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $applications->map(fn($app) => [
                'application_id' => $app->id,
                'amount'         => (float) $app->amount,
                'discount'       => (float) ($app->discount ?? 0),
                'submitted_at'   => $app->created_at?->toIso8601String(),
                'proforma'       => $app->proforma ? new ProformaResource($app->proforma) : null,
            ]),
        ]);
    }

    public function inbox()
    {
        $ownerId = $this->getOwnerId();

        $inboxItems = Inbox::where('user_id', $ownerId)
            ->with(['proforma.brand', 'proforma.parts'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'count'   => $inboxItems->count(),
            'data'    => $inboxItems->map(fn($item) => [
                'inbox_id'    => $item->proforma_id,
                'proforma'    => $item->proforma ? new ProformaResource($item->proforma) : null,
                'received_at' => $item->created_at?->toIso8601String(),
            ]),
        ]);
    }

    public function proformaDetail($id)
    {
        $ownerId = $this->getOwnerId();

        $proforma = Proforma::with(['brand', 'parts'])
            ->whereIn('status', ['pending', 'opened', 'published'])
            ->find($id);

        if (!$proforma) {
            return response()->json(['success' => false, 'message' => 'Proforma not found'], 404);
        }

        Inbox::where('user_id', $ownerId)->where('proforma_id', $proforma->id)->delete();

        $alreadyApplied = ProformaApplication::where('application_by', $ownerId)
            ->where('proforma_id', $proforma->id)
            ->exists();

        return response()->json([
            'success'         => true,
            'already_applied' => $alreadyApplied,
            'data'            => [
                'proforma' => new ProformaResource($proforma),
                'parts'    => \App\Http\Resources\PartResource::collection($proforma->parts),
            ],
        ]);
    }

    public function applyProforma(Request $request, $id)
    {
        $user    = auth()->user();
        $ownerId = $this->getOwnerId();

        $proforma = Proforma::whereIn('status', ['pending', 'opened', 'published'])->find($id);

        if (!$proforma) {
            return response()->json(['success' => false, 'message' => 'Proforma not found or no longer accepting applications'], 404);
        }

        $alreadyApplied = ProformaApplication::where('application_by', $ownerId)
            ->where('proforma_id', $proforma->id)
            ->exists();

        if ($alreadyApplied) {
            return response()->json(['success' => false, 'message' => 'You have already applied to this proforma'], 422);
        }

        $validated = $request->validate([
            'amount'   => 'required|numeric|min:1',
            'discount' => 'nullable|numeric|min:0|max:100',
        ]);

        $discount    = $validated['discount'] ?? 0;
        $finalAmount = max($validated['amount'] - ($validated['amount'] * $discount / 100), 1);

        $owner = User::find($ownerId);
        $fromRole = in_array($owner->role, ['shop']) ? 'shop' : 'garage';

        DB::beginTransaction();
        try {
            $application = ProformaApplication::create([
                'proforma_id'    => $proforma->id,
                'application_by' => $ownerId,
                'from'           => $fromRole,
                'amount'         => $finalAmount,
                'discount'       => $discount,
            ]);

            Inbox::where('user_id', $ownerId)->where('proforma_id', $proforma->id)->delete();

            if ($proforma->poster && $proforma->poster->id !== $ownerId) {
                $proforma->poster->notify(new ProformaApplicationReceived($proforma, $application, $user));
            }

            $requiredGarages = (int) ($proforma->required_number_of_garages ?? 0);
            $requiredShops   = (int) ($proforma->required_number_of_shops ?? 0);
            $isEteraChereta  = ($requiredGarages + $requiredShops) === 0;

            if (!$isEteraChereta) {
                $garageCount = $proforma->applications()->where('from', 'garage')->count();
                $shopCount   = $proforma->applications()->where('from', 'shop')->count();
                $garageMet   = $requiredGarages === 0 || $garageCount >= $requiredGarages;
                $shopMet     = $requiredShops === 0 || $shopCount >= $requiredShops;

                if ($garageMet && $shopMet && ($requiredGarages > 0 || $requiredShops > 0)) {
                    $proforma->update(['status' => 'closed']);
                    $proforma->inboxes()->delete();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Price quote submitted successfully',
                'data'    => [
                    'application_id'  => $application->id,
                    'amount'          => (float) $application->amount,
                    'discount'        => (float) $application->discount,
                    'proforma_status' => $proforma->fresh()->status,
                ],
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Garage apply proforma failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to submit quote. Please try again.'], 500);
        }
    }

    public function myFiles()
    {
        $ownerId = $this->getOwnerId();

        $proformas = Proforma::with('brand')
            ->where('poster_id', $ownerId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => ProformaResource::collection($proformas),
        ]);
    }

    public function createProforma(Request $request)
    {
        $validated = $request->validate([
            'number_of_proformas'   => ['required', 'integer', 'min:-1', 'max:4'],
            'etera_chereta_hours'   => ['nullable', 'integer', 'in:4,8,12,24,48,72'],
            'brand_id'              => ['required', 'integer', 'exists:brands,id'],
            'car_type'              => ['required', 'in:ICE,EV,Hybrid,Others'],
            'model'                 => ['required', 'string', 'max:255'],
            'year'                  => ['required', 'regex:/^(#N\/A|19\d{2}|20\d{2})$/'],
            'customer_phone_number' => ['required', 'string'],
            'chassis_number'        => ['nullable', 'string'],
            'parts'                 => ['required', 'array', 'min:1'],
            'parts.*.number'        => ['required', 'string'],
            'parts.*.name'          => ['required', 'string'],
            'parts.*.component'     => ['required', 'string', 'in:Body Parts,Mechanical Parts'],
            'parts.*.condition'     => ['required', 'string', 'in:New,Used,Refurbished'],
            'parts.*.grade'         => ['required', 'string'],
            'parts.*.country'       => ['required', 'string'],
            'parts.*.quantity'      => ['required', 'integer', 'min:1'],
            'parts.*.photo_paths'   => ['nullable', 'array'],
            'parts.*.photo_paths.*' => ['nullable', 'string'],
            'voice_note'            => ['nullable', 'string'],
        ]);

        $ownerId = $this->getOwnerId();
        $owner   = User::find($ownerId);

        DB::beginTransaction();
        try {
            $isEteraChereta = (int) $validated['number_of_proformas'] === -1;
            $eteraHours     = (int) ($validated['etera_chereta_hours'] ?? 24);
            $requiredShops  = $isEteraChereta ? 0 : (int) $validated['number_of_proformas'];
            $timerMinutes   = $isEteraChereta ? $eteraHours * 60 : null;
            $timerExpiresAt = $isEteraChereta ? now()->addMinutes($timerMinutes) : null;

            $proforma = Proforma::create([
                'poster_id'                  => $ownerId,
                'file_number'                => 'GR' .'-'. $ownerId,
                'car_brand_id'               => $validated['brand_id'],
                'car_type'                   => $validated['car_type'],
                'customer_name'              => $owner->name,
                'customer_phone_number'      => $validated['customer_phone_number'],
                'chassis_number'             => $validated['chassis_number'] ?? null,
                'year'                       => $validated['year'],
                'model'                      => $validated['model'],
                'required_number_of_shops'   => $requiredShops,
                'required_number_of_garages' => 0,
                'timer_duration'             => $timerMinutes,
                'timer_expires_at'           => $timerExpiresAt,
            ]);

            foreach ($validated['parts'] as $partData) {
                $part = $proforma->parts()->create([
                    'name'      =>$partData['name'],
                    'number'    => $partData['number'],
                    'grade'     => $partData['grade'],
                    'country'   => $partData['country'],
                    'quantity'  => $partData['quantity'],
                    'condition' => $partData['condition'],
                    'component' => $partData['component'],
                ]);

                foreach ($partData['photo_paths'] ?? [] as $photoPath) {
                    if (!empty($photoPath) && str_contains($photoPath, 'uploads/temp/')) {
                        $finalPath = 'uploads/part-images/' . basename($photoPath);
                        if (Storage::disk('public')->exists($photoPath)) {
                            Storage::disk('public')->move($photoPath, $finalPath);
                            \App\Models\PartsImage::create([
                                'proforma_part_id' => $part->id,
                                'image_path'       => $finalPath,
                            ]);
                        }
                    }
                }
            }

            if (!empty($validated['voice_note'])) {
                $base64 = $validated['voice_note'];
                preg_match('#^data:audio/([^;,]+)#i', $base64, $matches);
                $extension = $matches[1] ?? 'webm';
                $audioData = base64_decode(preg_replace('#^data:audio/[^;]+[^,]*,#i', '', $base64));

                if ($audioData !== false) {
                    $filename = 'voice_note_' . time() . '_' . uniqid() . '.' . $extension;
                    Storage::disk('public')->put('voice_notes/' . $filename, $audioData);
                    $proforma->update(['voice_note_path' => 'voice_notes/' . $filename]);
                }
            }

            DB::commit();

            event(new ProformaCreated($proforma));

            if ($isEteraChereta) {
                AutoSelectProformaOffers::dispatch($proforma->id)->delay(now()->addMinutes($timerMinutes));
            }

            return response()->json([
                'success' => true,
                'message' => 'Proforma created successfully',
                'data'    => new ProformaResource($proforma->load('brand')),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Garage create proforma failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to create proforma. Please try again.'], 500);
        }
    }

    public function requestClose($id)
    {
        $ownerId  = $this->getOwnerId();
        $proforma = Proforma::where('id', $id)->where('poster_id', $ownerId)->first();

        if (!$proforma) {
            return response()->json(['success' => false, 'message' => 'Proforma not found'], 404);
        }

        if ($proforma->close_request) {
            return response()->json(['success' => false, 'message' => 'Close request already submitted'], 422);
        }

        $proforma->update(['close_request' => true]);

        return response()->json(['success' => true, 'message' => 'Close request submitted successfully']);
    }

    public function receivedProformas()
    {
        $ownerId = $this->getOwnerId();

        $proformas = Proforma::with('brand')
            ->where('poster_id', $ownerId)
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success'    => true,
            'data'       => ProformaResource::collection($proformas),
            'pagination' => [
                'current_page' => $proformas->currentPage(),
                'last_page'    => $proformas->lastPage(),
                'per_page'     => $proformas->perPage(),
                'total'        => $proformas->total(),
            ],
        ]);
    }

    public function balance()
    {
        $owner = User::find($this->getOwnerId());

        return response()->json([
            'success' => true,
            'data'    => [
                'balance'             => (float) $owner->balance,
                'withdrawal_requests' => WithdrawalResource::collection(
                    $owner->withdrawalRequests()->orderBy('created_at', 'desc')->get()
                ),
            ],
        ]);
    }

    public function submitWithdrawal(Request $request)
    {
        $owner = User::find($this->getOwnerId());

        $validated = $request->validate([
            'amount'         => ['required', 'numeric', 'min:1', 'max:' . $owner->balance],
            'bank_name'      => ['required', 'string', 'in:CBE,Abyssiniya,Awash,Dashen,Enat,Wegagen,Tsedey'],
            'account_number' => ['required', 'string'],
        ]);

        $withdrawal = WithdrawalRequest::create([
            'from'           => $owner->id,
            'amount'         => $validated['amount'],
            'bank_name'      => $validated['bank_name'],
            'account_number' => $validated['account_number'],
            'status'         => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Withdrawal request submitted',
            'data'    => new WithdrawalResource($withdrawal),
        ], 201);
    }

    public function createEmployee(Request $request)
    {
        $ownerId      = $this->getOwnerId();
        $currentCount = User::where('registered_by', $ownerId)->where('role', 'employee')->count();
        $isBulk       = $request->has('employees');

        if ($isBulk) {
            $validated = $request->validate([
                'employees'                => ['required', 'array', 'min:1', 'max:10'],
                'employees.*.name'         => ['required', 'string', 'max:255'],
                'employees.*.phone_number' => ['required', 'string', 'regex:/^\d{10}$/', 'distinct', 'unique:users,phone_number'],
                'employees.*.email'        => ['nullable', 'email', 'distinct', 'unique:users,email'],
                'employees.*.password'     => ['required', 'string', 'min:6', 'confirmed'],
            ]);
            $newCount = count($validated['employees']);
        } else {
            $validated = $request->validate([
                'name'         => ['required', 'string', 'max:255'],
                'phone_number' => ['required', 'string', 'regex:/^\d{10}$/', 'unique:users,phone_number'],
                'email'        => ['nullable', 'email', 'unique:users,email'],
                'password'     => ['required', 'string', 'min:6', 'confirmed'],
            ]);
            $newCount = 1;
        }

        if ($currentCount + $newCount > 10) {
            return response()->json([
                'success' => false,
                'message' => "Cannot add {$newCount} employee(s). You have {$currentCount}/10 already. Limit is 10.",
            ], 422);
        }

        $created = [];
        DB::beginTransaction();
        try {
            foreach ($isBulk ? $validated['employees'] : [$validated] as $data) {
                $created[] = User::create([
                    'name'          => $data['name'],
                    'phone_number'  => $data['phone_number'],
                    'email'         => $data['email'] ?? null,
                    'password'      => Hash::make($data['password']),
                    'role'          => 'employee',
                    'approved'      => true,
                    'registered_by' => $ownerId,
                ]);
            }
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => count($created) . ' employee(s) created successfully.',
                'data'    => UserResource::collection(collect($created)),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Garage employee creation failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Employee creation failed. Please try again.'], 500);
        }
    }

    public function showMyFile($id)
    {
        $ownerId  = $this->getOwnerId();
        $proforma = Proforma::with([
            'brand',
            'parts',
            'proformaInvoice',
            'applications.prices',
            'applications.applicationBy',
        ])->where('poster_id', $ownerId)->find($id);

        if (!$proforma) {
            return response()->json(['success' => false, 'message' => 'Proforma not found'], 404);
        }

        $allApplications = $proforma->applications->map(function ($app) {
            $subtotal         = $app->prices->sum('part_total');
            $discount         = (float) ($app->discount ?? 0);
            $app->final_price = $subtotal > 0
                ? $subtotal - ($subtotal * $discount / 100)
                : (float) ($app->amount ?? 0);
            return $app;
        });

        $requiredShops   = (int) ($proforma->required_number_of_shops ?? 0);
        $requiredGarages = (int) ($proforma->required_number_of_garages ?? 0);

        $shops   = $allApplications->where('from', 'shop')->sortBy('final_price')->values();
        $garages = $allApplications->where('from', 'garage')->sortBy('final_price')->values();

        if ($requiredShops > 0)   $shops   = $shops->take($requiredShops);
        if ($requiredGarages > 0) $garages = $garages->take($requiredGarages);
        if ($requiredShops === 0 && $requiredGarages === 0) {
            $shops   = $shops->take(5);
            $garages = $garages->take(5);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'proforma' => new ProformaResource($proforma),
                'parts'    => PartResource::collection($proforma->parts),
                'invoice'  => $proforma->proformaInvoice ? [
                    'sku'          => $proforma->proformaInvoice->sku,
                    'type'         => $proforma->proformaInvoice->type,
                    'subtotal'     => round((float) $proforma->proformaInvoice->total_amount / 1.15, 2),
                    'vat_amount'   => round((float) $proforma->proformaInvoice->vat_amount, 2),
                    'total_amount' => (float) $proforma->proformaInvoice->total_amount,
                    'is_paid'      => (bool) $proforma->proformaInvoice->is_paid,
                    'created_at'   => $proforma->proformaInvoice->created_at?->toDateTimeString(),
                    'url'          => url('/transaction/' . $proforma->proformaInvoice->sku),
                ] : null,
                'shops'   => ApplicationResource::collection($shops),
                'garages' => ApplicationResource::collection($garages),
            ],
        ]);
    }

    public function listEmployees()
    {
        $ownerId   = $this->getOwnerId();
        $employees = User::where('registered_by', $ownerId)
            ->where('role', 'employee')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => UserResource::collection($employees),
        ]);
    }



    public function deleteEmployee($id)
    {
        $ownerId  = $this->getOwnerId();
        $employee = User::where('id', $id)
            ->where('registered_by', $ownerId)
            ->where('role', 'employee')
            ->first();

        if (!$employee) {
            return response()->json(['success' => false, 'message' => 'Employee not found.'], 404);
        }

        $employee->delete();

        return response()->json(['success' => true, 'message' => 'Employee removed successfully.']);
    }



    // garage management from the admin side
    /**
     * Store a newly created resource in garage.
     */
  public function createGarage(Request $request)
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

   return response()->json([
    'success'=> true,
    'message' => 'successfully created',
    'data'=> [
        'user' =>$user
    ]
   ]);
}



    

    // Other methods remain unchanged (index, show, edit, etc.)
    public function editGarage($id)
    {
        // Check if the authenticated user is an admin or marketer
        if (!Auth::check() || !in_array(Auth::user()->role, ['admin', 'marketer'])) {
            return redirect()->back()->with('error', 'Unauthorized access');
        }
    
        // Find the garage by ID
        $garage = User::findOrFail($id);
    
        // Return different views based on the user role
        return response()->json([
        'success'=> true,
        'data'=> [
            'garage' =>$garage
        ]
       ]);
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

     return response()->json([
    'success'=> true,
    'message' => 'successfully updated',
   
   ]);
}



    public function destroy($id)
    {
        $garage = User::findOrFail($id); // Get the insurance by ID
        $garage->delete(); // Delete the insurance record

         return response()->json([
        'success'=> true,
        'message' => 'successfully deleted',
        
    ]);
    }








}