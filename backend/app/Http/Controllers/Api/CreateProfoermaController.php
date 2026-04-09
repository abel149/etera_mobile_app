<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Events\ProformaCreated;
use App\Jobs\AutoSelectProformaOffers;
use App\Models\Proforma;
use App\Models\WithdrawalRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CreateProfoermaController extends Controller {

    public function store(Request $request){
        
        //log file for debug
    
       Log::info('📝 POST request to business-owner/create-file received', [
                'user_id' => auth()->id(),
                'user_type' => 'business-owner',
                'has_files' => $request->hasFile('parts.photo'),
                'all_input_keys' => array_keys($request->all()),
                'files_count' => $request->hasFile('parts.photo') ? count($request->file('parts.photo')) : 0,
            ]);
        Log::debug('📥 Full Request Data Snapshot', [
                'raw_input' => $request->except(['voice_note']),
                'voice_note_present' => $request->filled('voice_note'),
            ]);
            
            // 🔹 Step 1 — Validate input
            try {
                $validatedData = $request->validate([
                    'number_of_proformas' => ['required', 'integer', 'min:-1', 'max:4'],
                    'etera_chereta_hours' => ['nullable', 'integer', 'in:4,8,12,24,48,72'],
                    'brand_id' => ['required', 'integer', 'exists:brands,id'],
                    'car_type' => 'required|in:ICE,EV,Hybrid,Others',

                    'model' => ['required', 'string', 'max:255'],
                    'year' => ['required', 'regex:/^(#N\/A|19\d{2}|20\d{2})$/'],
                    'customer_phone_number' => ['required', 'string'],
                    'license_plate_number' => ['required', 'string'],
                    'chassis_number' => ['nullable', 'string'],
                    'parts.condition' => ['required', 'array', 'min:1'],
                    'parts.condition.*' => ['required', 'string', 'in:New'],
                    'parts.number' => ['required', 'array', 'min:1'],
                    'parts.number.*' => ['required', 'string'],
                    'parts.grade' => ['required', 'array', 'min:1'],
                    'parts.grade.*' => ['required', 'string'],
                    'parts.country' => ['required', 'array'],
                    'parts.country.*' => ['required', 'string'],
                    'parts.quantity' => ['required', 'array'],
                    'parts.quantity.*' => ['required', 'integer'],
                    'parts.component' => ['required', 'array', 'min:1'],
                    'parts.component.*' => ['required', 'string', 'in:Body Parts,Mechanical Parts'],
                    // ⚠️ FilePond now sends uploaded paths, not actual image files
                    'parts.photo' => ['nullable', 'array'],
                    'parts.photo.*' => ['nullable', 'array'],
                    'parts.photo.*.*' => ['nullable', 'string'],
                    'voice_note' => ['nullable', 'string'],
                ]);

                Log::info('✅ Validation passed successfully for business-owner');
            } catch (\Illuminate\Validation\ValidationException $e) {
                Log::error('❌ Validation failed for business-owner', [
                    'errors' => $e->errors(),
                    'input_data' => $request->except(['parts.photo', 'voice_note'])
                ]);
                return response()->json([
                'success' => false,
                'message' => 'validation failed',
                'errors' => $e->errors(),
            ], 422);
            }
            // 🔹 Step 2 — Process transaction
            try {

                DB::beginTransaction();

                $isEteraChereta = $request->input('number_of_proformas') === '-1';
                $eteraHours = (int) $request->input('etera_chereta_hours', 24);
                $requiredShops = $isEteraChereta ? 0 : (int) $request->input('number_of_proformas', 3);
                $timerMinutes = $isEteraChereta ? $eteraHours * 60 : null;
                $timerExpiresAt = $isEteraChereta ? now()->addMinutes($timerMinutes) : null;

                $proforma = Proforma::create([
                    'poster_id' => auth()->id(),
                    'file_number' => '#' . auth()->id() . '-' . substr(time(), -4),
                    'car_brand_id' => $request->brand_id,
                    'car_type' => $request->input('car_type', 'ICE'),

                    'customer_name' => auth()->user()->name,
                    'customer_phone_number' => $request->customer_phone_number,
                    'license_plate_number' => $request->license_plate_number,
                    'chassis_number' => $request->chassis_number,
                    'year' => $request->year,
                    'model' => $request->model,
                    'required_number_of_shops' => $requiredShops,
                    'required_number_of_garages' => 0,
                    'timer_duration' => $timerMinutes,
                    'timer_expires_at' => $timerExpiresAt,
                ]);

                Log::info('✅ Proforma created for business-owner', ['proforma_id' => $proforma->id]);
            
                // 🔹 Step 3 — Handle spare parts with FilePond image handling
                $partsData = $request->input('parts');

                foreach ($partsData['condition'] as $index => $condition) {
                    $part = $proforma->parts()->create([
                        'number'    => $partsData['number'][$index] ?? null,
                        'grade'     => $partsData['grade'][$index] ?? null,
                        'country'   => $partsData['country'][$index] ?? null,
                        'quantity'  => $partsData['quantity'][$index] ?? 1,
                        'condition' => $condition,
                        'component' => $partsData['component'][$index] ?? null,
                    ]);

                    Log::info('✅ Proforma part created for business-owner', [
                        'part_id' => $part->id, 
                        'index' => $index,
                        'part_number' => $part->number
                    ]);

                    // 🔹 Move temp images and attach them (FilePond async uploads)
                    if (isset($partsData['photo'][$index]) && is_array($partsData['photo'][$index])) {
                        foreach ($partsData['photo'][$index] as $photoPath) {
                            if (!empty($photoPath) && str_contains($photoPath, 'uploads/temp/')) {
                                $tempPath = $photoPath;
                                $filename = basename($tempPath);
                                $finalPath = 'uploads/part-images/' . $filename;

                                // Move the file from temp to permanent location
                                if (Storage::disk('public')->exists($tempPath)) {
                                    Storage::disk('public')->move($tempPath, $finalPath);

                                    \App\Models\PartsImage::create([
                                        'proforma_part_id' => $part->id,
                                        'image_path' => $finalPath,
                                    ]);

                                    Log::info('✅ Temp image moved and saved for business-owner', [
                                        'from' => $tempPath,
                                        'to' => $finalPath,
                                        'proforma_part_id' => $part->id,
                                    ]);
                                } else {
                                    Log::warning('⚠️ Temp image not found for business-owner', ['path' => $tempPath]);
                                }
                            }
                        }
                    } else {
                        Log::info('ℹ️ No images found for part in business-owner request', ['index' => $index]);
                    }
                }

                // 🔹 Step 4 — Voice note (optional)
                if ($request->filled('voice_note')) {
                    try {
                        $base64 = $request->voice_note;
                        
                        // Extract the extension from the MIME type (handles codecs like audio/webm;codecs=opus)
                        if (preg_match('#^data:audio/([^;,]+)#i', $base64, $matches)) {
                            $extension = $matches[1]; // e.g. webm, mp3, wav
                        } else {
                            $extension = 'webm'; // default
                        }
                        
                        // Remove the entire data URI prefix including any codec specifications
                        $audioData = base64_decode(preg_replace('#^data:audio/[^;]+[^,]*,#i', '', $base64));
                        
                        if ($audioData === false) {
                            Log::error('❌ Voice note base64 decoding failed for business-owner');
                        } else {
                            $filename = 'voice_note_' . time() . '_' . uniqid() . '.' . $extension;
                            Storage::disk('public')->put('voice_notes/' . $filename, $audioData);
                            $proforma->update(['voice_note_path' => 'voice_notes/' . $filename]);
                            Log::info('🎤 Voice note saved for business-owner', ['filename' => $filename, 'size' => strlen($audioData)]);
                        }
                    } catch (\Exception $e) {
                        Log::error('❌ Error saving voice note for business-owner', [
                            'error' => $e->getMessage()
                        ]);
                    }
                }

                DB::commit();

                // 🔔 Broadcast to admin dashboard in real-time
                event(new ProformaCreated($proforma));

                // 🔹 Step 5 — Schedule Etera-Chereta AutoSelect
                if ($isEteraChereta) {
                    AutoSelectProformaOffers::dispatch($proforma->id)->delay(now()->addMinutes($timerMinutes));
                    Log::info('⏰ Etera-Chereta scheduled for business-owner', [
                        'proforma_id' => $proforma->id,
                        'delay_minutes' => $timerMinutes
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Registration successful. Awaiting for application.',
                ], 201);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Proforma creation failed for business-owner', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'prforma request failed. Please try again.',
                ], 500);
            }
    }

    public function index()
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }
        Proforma::where('poster_id', $user->id)
        ->where('status', 'completed')
        ->where('verified', true)
        ->where('is_new', true)
        ->update(['is_new' => false]);
        $proformas = Proforma::where('poster_id', $user->id)
            ->where('status', 'completed')
            ->where('verified', true)
            ->orderBy('updated_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $proformas->items(),
            'pagination' => [
                'current_page' => $proformas->currentPage(),
                'last_page' => $proformas->lastPage(),
                'per_page' => $proformas->perPage(),
                'total' => $proformas->total(),
            ],
        ], 200);
    }
    public function dashboard()
        {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            // Load proformas with relationships (IMPORTANT)
            $proformas = $user->proformas()
                ->with(['brand', 'applications'])
                ->orderBy('created_at', 'desc')
                ->get();

            $data = $proformas->map(function ($proforma) {

                $applicationsCount = $proforma->applications->count();

                return [
                    'id' => $proforma->id,
                    'file_number' => $proforma->file_number,
                    'customer_name' => $proforma->customer_name,
                    'brand' => $proforma->brand?->name,
                    'model' => $proforma->model,
                    'year' => $proforma->year,
                    'license_plate' => $proforma->license_plate_number,
                    'phone' => $proforma->customer_phone_number,

                    'applications_count' => $applicationsCount,
                    'required_shops' => $proforma->required_number_of_shops == 0
                        ? '∞'
                        : $proforma->required_number_of_shops,

                    // 👇 This replaces your Blade logic
                    'can_request_close' => (
                        $proforma->status === 'published' &&
                        !$proforma->close_request &&
                        $applicationsCount > 0
                    ),

                    'close_requested' => $proforma->close_request,
                    'status' => $proforma->status,
                ];
            });

            return response()->json([
                'success' => true,
                'total_proformas' => $proformas->count(),
                'data' => $data
            ]);
        }
    public function requestClose($id)
    {
        $user = auth()->user();
        $proforma = Proforma::where('id', $id)
            ->where('poster_id', $user->id)
            ->first();

        if (!$proforma) {
            return response()->json([
                'success' => false,
                'message' => 'Proforma not found'
            ], 404);
        }

        $applicationsCount = $proforma->applications()->count();

        if (
            $proforma->status === 'published' &&
            !$proforma->close_request &&
            $applicationsCount > 0
        ) {
            $proforma->update(['close_request' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Close request submitted successfully'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Action not allowed'
        ], 400);
    }

    /**
     * GET /api/proformas/{id}
     * Proforma details with applications, prices, parts, invoice
     */
    public function show($id)
    {
        $user = auth()->user();
        $proforma = Proforma::with(['brand', 'parts', 'proformaInvoice', 'applications.prices', 'applications.applicationBy'])
            ->where('poster_id', $user->id)
            ->find($id);

        if (!$proforma) {
            return response()->json([
                'success' => false,
                'message' => 'Proforma not found'
            ], 404);
        }

        // Calculate final price per application (same logic as web)
        $allApplications = $proforma->applications->map(function ($application) {
            if ($application->from === 'shop' && $application->prices->isNotEmpty()) {
                $subtotal = $application->prices->sum('part_total');
                $discount = (float) ($application->discount ?? 0);
                $application->final_price = $subtotal - ($subtotal * $discount / 100);
            } else {
                $application->final_price = (float) ($application->amount ?? 0);
            }
            return $application;
        })->sortBy('final_price')->values();

        // Apply limits (same as web)
        $requiredShops = (int) ($proforma->required_number_of_shops ?? 0);
        $requiredGarages = (int) ($proforma->required_number_of_garages ?? 0);

        $shops = $allApplications->where('from', 'shop')->sortBy('final_price')->values();
        $garages = $allApplications->where('from', 'garage')->sortBy('final_price')->values();

        if ($requiredShops > 0) {
            $shops = $shops->take($requiredShops);
        }
        if ($requiredGarages > 0) {
            $garages = $garages->take($requiredGarages);
        }
        // Etera Chereta mode: show top 5
        if ($requiredShops === 0 && $requiredGarages === 0) {
            $shops = $shops->take(5);
            $garages = $garages->take(5);
        }

        // Format applications for mobile
        $formatApplication = function ($app) use ($proforma) {
            $subtotal = $app->prices->sum('part_total');
            $discount = (float) ($app->discount ?? 0);
            $discountAmt = $subtotal > 0 ? ($subtotal * $discount / 100) : 0;
            $netTotal = $subtotal > 0 ? ($subtotal - $discountAmt) : (float) ($app->amount ?? 0);

            return [
                'id' => $app->id,
                'from' => $app->from,
                'applicant' => [
                    'name' => $app->applicationBy->name ?? null,
                    'phone' => $app->applicationBy->phone_number ?? null,
                    'store_id' => $app->applicationBy->store_id ?? null,
                    'tin_number' => $app->applicationBy->tin_number ?? null,
                    'location' => $app->applicationBy->location ?? null,
                ],
                'parts_pricing' => $app->prices->map(function ($price) {
                    return [
                        'car_part_id' => $price->car_part_id,
                        'unit_price' => (float) $price->unit_price,
                        'part_total' => (float) $price->part_total,
                    ];
                }),
                'subtotal' => round($subtotal, 2),
                'discount_pct' => $discount,
                'discount_amount' => round($discountAmt, 2),
                'net_total' => round($netTotal, 2),
                'final_price' => round($app->final_price, 2),
            ];
        };

        return response()->json([
            'success' => true,
            'data' => [
                'proforma' => [
                    'id' => $proforma->id,
                    'file_number' => $proforma->file_number,
                    'brand' => $proforma->brand?->name,
                    'model' => $proforma->model,
                    'year' => $proforma->year,
                    'car_type' => $proforma->car_type,
                    'customer_name' => $proforma->customer_name,
                    'customer_phone' => $proforma->customer_phone_number,
                    'license_plate' => $proforma->license_plate_number,
                    'chassis_number' => $proforma->chassis_number,
                    'status' => $proforma->status,
                    'close_request' => (bool) $proforma->close_request,
                    'voice_note_url' => $proforma->voice_note_path
                        ? asset('storage/' . $proforma->voice_note_path)
                        : null,
                    'timer_duration' => $proforma->timer_duration,
                    'timer_expires_at' => $proforma->timer_expires_at,
                    'created_at' => $proforma->created_at?->toIso8601String(),
                ],
                'parts' => $proforma->parts->map(function ($part) {
                    return [
                        'id' => $part->id,
                        'number' => $part->number,
                        'component' => $part->component,
                        'condition' => $part->condition,
                        'grade' => $part->grade,
                        'country' => $part->country,
                        'quantity' => $part->quantity,
                    ];
                }),
                'invoice' => $proforma->proformaInvoice ? [
                    'sku' => $proforma->proformaInvoice->sku,
                    'url' => url('/transaction/' . $proforma->proformaInvoice->sku),
                ] : null,
                'shops' => $shops->map($formatApplication)->values(),
                'garages' => $garages->map($formatApplication)->values(),
            ],
        ], 200);
    }

   
    /**
     * GET /api/balance
     */
    public function balance()
    {
        $user = auth()->user();

        $withdrawalRequests = $user->withdrawalRequests()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($req) {
                return [
                    'id' => $req->id,
                    'amount' => (float) $req->amount,
                    'bank_name' => $req->bank_name,
                    'account_number' => $req->account_number,
                    'status' => $req->status,
                    'created_at' => $req->created_at?->toIso8601String(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'balance' => (float) $user->balance,
                'withdrawal_requests' => $withdrawalRequests,
            ],
        ], 200);
    }

    /**
     * POST /api/withdraw
     */
    public function submitWithdrawal(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1', 'max:' . $user->balance],
            'bank_name' => ['required', 'string', 'in:CBE,Abyssiniya,Awash,Dashen,Enat,Wegagen,Tsedey'],
            'account_number' => ['required', 'string'],
        ]);

        $withdrawal = WithdrawalRequest::create([
            'from' => $user->id,
            'amount' => $validated['amount'],
            'bank_name' => $validated['bank_name'],
            'account_number' => $validated['account_number'],
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Withdrawal request submitted',
            'data' => [
                'id' => $withdrawal->id,
                'amount' => (float) $withdrawal->amount,
                'status' => $withdrawal->status,
            ],
        ], 201);
    }
}
