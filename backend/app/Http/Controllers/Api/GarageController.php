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

        DB::beginTransaction();
        try {
            $application = ProformaApplication::create([
                'proforma_id'    => $proforma->id,
                'application_by' => $ownerId,
                'from'           => 'garage',
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
            'parts.name'            => ['required', 'string'],
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
                'file_number'                => '#' . $ownerId . '-' . substr(time(), -4),
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
        $ownerId = $this->getOwnerId();

        $validated = $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'regex:/^\d{10}$/', 'unique:users,phone_number'],
            'password'     => ['required', 'string', 'min:6', 'confirmed'],
            'email'        => ['nullable', 'email', 'unique:users,email'],
        ]);

        $employee = User::create([
            'name'          => $validated['name'],
            'phone_number'  => $validated['phone_number'],
            'email'         => $validated['email'] ?? null,
            'password'      => Hash::make($validated['password']),
            'role'          => 'employee',
            'approved'      => true,
            'registered_by' => $ownerId,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Employee created successfully',
            'data'    => new UserResource($employee),
        ], 201);
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
                    'sku' => $proforma->proformaInvoice->sku,
                    'url' => url('/transaction/' . $proforma->proformaInvoice->sku),
                ] : null,
                'shops'   => ApplicationResource::collection($shops),
                'garages' => ApplicationResource::collection($garages),
            ],
        ]);
    }

    public function listEmployees()
    {
        $ownerId = $this->getOwnerId();

        $employees = User::where('registered_by', $ownerId)
            ->where('role', 'employee')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => UserResource::collection($employees),
        ]);
    }
}