<?php

namespace App\Http\Controllers\Api;

use App\Events\ProformaCreated;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApplicationResource;
use App\Http\Resources\PartResource;
use App\Http\Resources\ProformaResource;
use App\Jobs\AutoSelectProformaOffers;
use App\Models\Inbox;
use App\Models\PaidUser;
use App\Models\Partner;
use App\Models\Proforma;
use App\Models\User;
use App\Models\ProformaInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class InsuranceController extends Controller
{
    // =========================================================================
    // GET /api/v1/insurance/dashboard
    // Summary card + all own proformas
    // =========================================================================
     private function getOwnerId(): int
    {
        $user = auth()->user();
        if($user->role == insurance){
            return $user->id;
        }
        return $user->registered_by ?? $user->id;
    }
    public function dashboard()
    {
        $ownerId = $this->getOwnerId();

        $proformas = Proforma::with('brand')
            ->where('poster_id', $ownerId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => [
                'total_files' => $proformas->count(),
                'pending'     => $proformas->where('status', 'pending')->count(),
                'completed'   => $proformas->where('status', 'completed')->count(),
                'proformas'   => ProformaResource::collection($proformas),
            ],
        ]);
    }

    // =========================================================================
    // GET /api/v1/insurance/proformas
    // Paginated list of all own proformas (optional ?status= filter)
    // =========================================================================
    public function index(Request $request)
    {
        $ownerId = $this->getOwnerId();
        $query = Proforma::with('brand')->where('poster_id', $ownerId);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $proformas = $query->orderBy('created_at', 'desc')->paginate(10);

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

    // =========================================================================
    // POST /api/v1/insurance/create-file
    // Create an insurance proforma
    // Body (JSON):
    //   customer_name*, customer_phone_number*, license_plate_number*,
    //   brand_id*, model*, year*, car_type, chassis_number, customer_email,
    //   insured (bool), number_of_proformas (-1 = Etera Chereta),
    //   etera_chereta_hours, spare_part_partners[] (IDs), garage_partners[] (IDs),
    //   parts[]*: [{number*, name, grade*, country, quantity, condition, component}],
    //   voice_note (base64)
    // =========================================================================
    public function store(Request $request)
    {
        $validated = $request->validate([
             'number_of_spare_parts' => ['required', 'integer'],
             'number_of_garages'      => ['required', 'integer'],
             'customer_name'         => ['required', 'string', 'max:255'],
            'customer_phone_number' => ['required', 'string'],
            'customer_email'        => ['nullable', 'email'],
            'license_plate_number'  => ['required', 'string', 'max:50'],
            'brand_id'              => ['required', 'integer', 'exists:brands,id'],
            'car_type'              => ['nullable', 'in:ICE,EV,Hybrid,Others'],
            'model'                 => ['required', 'string', 'max:255'],
            'year'                  => ['required', 'regex:/^(#N\/A|19\d{2}|20\d{2})$/'],
            'chassis_number'        => ['nullable', 'string'],
            'insured'               => ['nullable', 'boolean'],
            'number_of_proformas'   => ['nullable', 'integer', 'min:-1', 'max:5'],
            'etera_chereta_hours'   => ['nullable', 'integer', 'in:4,8,12,24,48,72'],
            'spare_part_partners'   => ['nullable', 'array'],
            'spare_part_partners.*' => ['integer', 'exists:users,id'],
            'garage_partners'       => ['nullable', 'array'],
            'garage_partners.*'     => ['integer', 'exists:users,id'],
            'parts'                 => ['required', 'array', 'min:1'],
            'parts.*.number'        => ['required', 'string'],
            'parts.*.name'          => ['nullable', 'string'],
            'parts.*.grade'         => ['required', 'string'],
            'parts.*.country'       => ['nullable', 'string'],
            'parts.*.quantity'      => ['nullable', 'integer', 'min:1'],
            'parts.*.condition'     => ['nullable', 'string', 'in:New,Used,Refurbished'],
            'parts.*.component'     => ['nullable', 'string', 'in:Body Parts,Mechanical Parts'],
            'voice_note'            => ['nullable', 'string'],
        ]);

        $isEteraChereta = (int) ($validated['number_of_proformas'] ?? 3) === -1;

        if ($isEteraChereta) {
            $eteraHours      = (int) ($validated['etera_chereta_hours'] ?? 24);
            $timerMinutes    = $eteraHours * 60;
            $requiredShops   = 0;
            $requiredGarages = 0;
            $timerExpiresAt  = now()->addMinutes($timerMinutes);
        } else {
            $requiredShops   = $validated['number_of_spare_parts'];
            $requiredGarages = $validated['number_of_garages'];
            $timerMinutes    = null;
            $timerExpiresAt  = null;
        }

        DB::beginTransaction();
        try {
            $ownerId = $this->getOwnerId();
            $proforma = Proforma::create([
                'poster_id'                  => $ownerId,
                'file_number'                => 'IN' .'-'. $ownerId,
                'car_brand_id'               => $validated['brand_id'],
                'car_type'                   => $validated['car_type'] ?? 'ICE',
                'customer_name'              => $validated['customer_name'],
                'customer_phone_number'      => $validated['customer_phone_number'],
                'customer_email'             => $validated['customer_email'] ?? null,
                'license_plate_number'       => $validated['license_plate_number'],
                'chassis_number'             => $validated['chassis_number'] ?? null,
                'year'                       => $validated['year'],
                'model'                      => $validated['model'],
                'insured'                    => $validated['insured'] ?? false,
                'required_number_of_shops'   => $requiredShops,
                'required_number_of_garages' => $requiredGarages,
                'timer_duration'             => $timerMinutes,
                'timer_expires_at'           => $timerExpiresAt,
            ]);

            // Parts
            foreach ($validated['parts'] as $partData) {
                $proforma->parts()->create([
                    'number'    => $partData['number'],
                    'name'      => $partData['name']      ?? null,
                    'grade'     => $partData['grade'],
                    'country'   => $partData['country']   ?? null,
                    'quantity'  => $partData['quantity']  ?? 1,
                    'condition' => $partData['condition'] ?? null,
                    'component' => $partData['component'] ?? null,
                ]);
            }

            // Direct inbox — shop partners
            foreach ($validated['spare_part_partners'] ?? [] as $partnerId) {
                Inbox::create(['proforma_id' => $proforma->id, 'user_id' => $partnerId]);
            }

            // Direct inbox — garage partners
            foreach ($validated['garage_partners'] ?? [] as $partnerId) {
                Inbox::create(['proforma_id' => $proforma->id, 'user_id' => $partnerId]);
            }

            // Voice note (base64)
            if (!empty($validated['voice_note'])) {
                try {
                    $base64 = $validated['voice_note'];
                    $ext    = 'webm';
                    if (preg_match('#^data:audio/([^;,]+)#i', $base64, $m)) {
                        $ext = $m[1];
                    }
                    $audioData = base64_decode(preg_replace('#^data:audio/[^;]+[^,]*,#i', '', $base64));
                    if ($audioData !== false) {
                        $filename = 'voice_note_' . $proforma->id . '_' . time() . '.' . $ext;
                        Storage::disk('public')->put('voice_notes/' . $filename, $audioData);
                        $proforma->update(['voice_note_path' => 'voice_notes/' . $filename]);
                    }
                } catch (\Throwable $e) {
                    Log::warning('Insurance voice note failed', ['error' => $e->getMessage()]);
                }
            }

            DB::commit();

            event(new ProformaCreated($proforma));

            if ($isEteraChereta) {
                AutoSelectProformaOffers::dispatch($proforma->id)->delay(now()->addMinutes($timerMinutes));
            }

            return response()->json([
                'success' => true,
                'message' => 'Proforma created successfully.',
                'data'    => new ProformaResource($proforma),
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Insurance create-file failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Proforma creation failed. Please try again.',
            ], 500);
        }
    }

    // =========================================================================
    // GET /api/v1/insurance/proformas/{id}
    // Full proforma detail with applications sorted by price
    // =========================================================================
    public function show($id)
    {
        $ownerId = $this->getOwnerId();
        $proforma = Proforma::with([
            'brand',
            'parts',
            'proformaInvoice',
            'applications.prices',
            'applications.applicationBy',
        ])->where('poster_id', $ownerId)->find($id);

        if (!$proforma) {
            return response()->json(['success' => false, 'message' => 'Proforma not found.'], 404);
        }

        $applications = $proforma->applications->map(function ($app) {
            if ($app->from === 'shop' && $app->prices->isNotEmpty()) {
                $subtotal         = $app->prices->sum('part_total');
                $discount         = (float) ($app->discount ?? 0);
                $app->final_price = $subtotal - ($subtotal * $discount / 100);
            } else {
                $app->final_price = (float) ($app->amount ?? 0);
            }
            return $app;
        })->sortBy('final_price')->values();

        $requiredShops   = (int) ($proforma->required_number_of_shops ?? 0);
        $requiredGarages = (int) ($proforma->required_number_of_garages ?? 0);

        $shops   = $applications->where('from', 'shop')->values();
        $garages = $applications->where('from', 'garage')->values();

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
                    'sku' => $proforma->proformaInvoice->sku,
                    'url' => url('/transaction/' . $proforma->proformaInvoice->sku),
                ] : null,
                'shops'    => ApplicationResource::collection($shops),
                'garages'  => ApplicationResource::collection($garages),
            ],
        ]);
    }

    // =========================================================================
    // POST /api/v1/insurance/proformas/{id}/request-close
    // =========================================================================
    public function requestClose($id)
    {
        $ownerId = $this->getOwnerId();
        $proforma = Proforma::where('poster_id', $ownerId)->find($id);

        if (!$proforma) {
            return response()->json(['success' => false, 'message' => 'Proforma not found.'], 404);
        }

        if ($proforma->close_request) {
            return response()->json(['success' => false, 'message' => 'Close request already submitted.'], 422);
        }

        $proforma->update(['close_request' => true]);

        return response()->json(['success' => true, 'message' => 'Close request submitted successfully.']);
    }

    // =========================================================================
    // GET /api/v1/insurance/received-proformas
    // Completed and verified proformas only
    // =========================================================================
    public function receivedProformas()
    {
        $ownerId = $this->getOwnerId();
        $proformas = Proforma::with('brand')
            ->where('poster_id', $ownerId)
            ->where('status', 'completed')
            ->where('verified', true)
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

    // =========================================================================
    // GET /api/v1/insurance/balance
    // IN  — commissions from Etera (PaidUser)
    // OUT — invoice charges to Etera (ProformaInvoice on insured proformas)
    // =========================================================================
    public function balance()
    {
        $ownerId = $this->getOwnerId();

        $commissions = PaidUser::where('user_id', $ownerId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($p) => [
                'date'      => $p->created_at->toIso8601String(),
                'type'      => 'commission',
                'reference' => 'Commission from Etera',
                'amount'    => abs((float) $p->amount),
                'is_paid'   => (bool) $p->is_paid,
                'flow'      => 'in',
            ]);

        $insuredProformaIds = Proforma::where('poster_id', $ownerId)
            ->where('insured', true)
            ->pluck('id');

        $invoices = ProformaInvoice::whereIn('proforma_id', $insuredProformaIds)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($inv) => [
                'date'      => $inv->created_at->toIso8601String(),
                'type'      => 'invoice',
                'reference' => 'Insured Proforma Invoice',
                'amount'    => abs((float) $inv->total_amount),
                'is_paid'   => (bool) $inv->is_paid,
                'flow'      => 'out',
            ]);

        $transactions = $commissions->merge($invoices)->sortByDesc('date')->values();

        return response()->json([
            'success' => true,
            'summary' => [
                'pending_from_etera'      => $commissions->where('is_paid', false)->sum('amount'),
                'paid_from_etera'         => $commissions->where('is_paid', true)->sum('amount'),
                'total_earned_from_etera' => $commissions->sum('amount'),

                'pending_to_etera'        => $invoices->where('is_paid', false)->sum('amount'),
                'paid_to_etera'           => $invoices->where('is_paid', true)->sum('amount'),
                'total_paid_to_etera'     => $invoices->sum('amount'),
            ],
            'transactions' => $transactions,
        ]);
    }

    // =========================================================================
    // GET /api/v1/insurance/partners
    // List all partner shops and garages
    // =========================================================================
    public function listPartners()
    {
        $ownerId = $this->getOwnerId();

        $shopPartners = Partner::where('insurance_id', $ownerId)
            ->whereHas('partner', fn($q) => $q->where('role', 'shop'))
            ->with('partner:id,name,phone_number,store_id,location')
            ->get()
            ->map(fn($p) => ['partner_record_id' => $p->id, 'user' => $p->partner]);

        $garagePartners = Partner::where('insurance_id', $ownerId)
            ->whereHas('partner', fn($q) => $q->where('role', 'garage'))
            ->with('partner:id,name,phone_number,store_id,location')
            ->get()
            ->map(fn($p) => ['partner_record_id' => $p->id, 'user' => $p->partner]);

        return response()->json([
            'success' => true,
            'data'    => [
                'shop_partners'   => $shopPartners->values(),
                'garage_partners' => $garagePartners->values(),
            ],
        ]);
    }

    // =========================================================================
    // POST /api/v1/insurance/partners
    // Add partner(s) — Body: { "partners": [1, 5, 9] }
    // =========================================================================
    public function addPartner(Request $request)
    {
        $validated = $request->validate([
            'partners'   => ['required', 'array', 'min:1'],
            'partners.*' => ['integer', 'exists:users,id'],
        ]);

        $ownerId = $this->getOwnerId();
        $created = 0;

        foreach ($validated['partners'] as $partnerId) {
            $exists = Partner::where('insurance_id', $ownerId)
                ->where('partner_id', $partnerId)
                ->exists();

            if (!$exists) {
                Partner::create(['insurance_id' => $ownerId, 'partner_id' => $partnerId]);
                $created++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => $created . ' partner(s) added.',
        ], 201);
    }

    // =========================================================================
    // DELETE /api/v1/insurance/partners/{id}
    // Remove partner by partner record ID
    // =========================================================================
    public function removePartner($id)
    {
        $ownerId = $this->getOwnerId();
        $partner = Partner::where('insurance_id', $ownerId)->find($id);

        if (!$partner) {
            return response()->json(['success' => false, 'message' => 'Partner not found.'], 404);
        }

        $partner->delete();

        return response()->json(['success' => true, 'message' => 'Partner removed successfully.']);
    }

    // =========================================================================
    // GET /api/v1/insurance/employees
    // =========================================================================
    public function listEmployees()
    {
        $user      = auth()->user();
        $employees = \App\Models\User::where('registered_by', $user->id)
            ->where('role', 'employee')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => \App\Http\Resources\UserResource::collection($employees),
        ]);
    }

    // =========================================================================
    // POST /api/v1/insurance/employees
    // Single: { "name":…, "phone_number":…, "password":…, "password_confirmation":… }
    // Bulk:   { "employees": [ {…}, {…} ] }   max 10 total
    // =========================================================================
    public function createEmployee(Request $request)
    {
        $user         = auth()->user();
        $currentCount = \App\Models\User::where('registered_by', $user->id)->where('role', 'employee')->count();
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
                $created[] = \App\Models\User::create([
                    'name'          => $data['name'],
                    'phone_number'  => $data['phone_number'],
                    'email'         => $data['email'] ?? null,
                    'password'      => \Illuminate\Support\Facades\Hash::make($data['password']),
                    'role'          => 'employee',
                    'approved'      => true,
                    'registered_by' => $user->id,
                ]);
            }
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => count($created) . ' employee(s) created successfully.',
                'data'    => \App\Http\Resources\UserResource::collection(collect($created)),
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Insurance employee creation failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Employee creation failed. Please try again.'], 500);
        }
    }

    // =========================================================================
    // DELETE /api/v1/insurance/employees/{id}
    // =========================================================================
    public function deleteEmployee($id)
    {
        $user     = auth()->user();
        $employee = \App\Models\User::where('id', $id)
            ->where('registered_by', $user->id)
            ->where('role', 'employee')
            ->first();

        if (!$employee) {
            return response()->json(['success' => false, 'message' => 'Employee not found.'], 404);
        }

        $employee->delete();

        return response()->json(['success' => true, 'message' => 'Employee removed successfully.']);
    }



    ///insurance creation
    /**
     * for admin page.
     */
    public function createInsurance(Request $request)
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
                'approved' => true,
                'registered_by' => auth()->user()->id
            ]);

             return response()->json([
                'success' => true, 
                'message' => 'Employee created successfully.',
                'data' => [
                    'user' => $user,
                ]
             ]);
    
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
           return response()->json([
        'success' => false,
        'message' => implode(' ', $errorMessages),
        'errors' => $e->errors(),
    ], 422);

} catch (\Exception $e) {

    return response()->json([
        'success' => false,
        'message' => 'An unexpected error occurred. Please try again.',
    ], 500);
}
    }

    public function updateInsurance(Request $request, $id)
    {
        $insurance = User::findOrFail($id);
        $user = User::findOrFail($id);
        $insurance->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
        ]);
    
        // return redirect()->to('admin/insurances');

   
        return response()->json([
        'success' => true,
        'message' => 'insurance updated successfully',
       
    ]);


    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroyInsurance($id)
    {
        $insurance = User::findOrFail($id); // Get the insurance by ID
        $insurance->delete(); // Delete the insurance record

         return response()->json([
        'success' => true,
        'message' => 'insurance deleted successfully',
       
    ]);
    }



    
}


