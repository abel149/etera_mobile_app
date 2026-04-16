<?php

namespace App\Http\Controllers\Api;

use App\Events\ProformaCreated;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProformaResource;
use App\Http\Resources\WithdrawalResource;
use App\Jobs\AutoSelectProformaOffers;
use App\Models\Inbox;
use App\Models\Proforma;
use App\Models\ProformaApplication;
use App\Models\WithdrawalRequest;
use App\Notifications\ProformaApplicationReceived;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GarageController extends Controller
{
    // -----------------------------------------------------------------------
    // GET /api/v1/garage/dashboard
    // Summary stats for the mobile home screen
    // -----------------------------------------------------------------------
    public function dashboard()
    {
        $user = auth()->user();

        $applications = ProformaApplication::with('proforma')
            ->where('application_by', $user->id)
            ->get();

        $inboxCount = Inbox::where('user_id', $user->id)->count();

        return response()->json([
            'success' => true,
            'data'    => [
                'balance'        => (float) $user->balance,
                'inbox_count'    => $inboxCount,
                'total'          => $applications->count(),
                'pending_count'  => $applications->filter(
                    fn($a) => in_array(optional($a->proforma)->status, ['pending', 'opened', 'published'])
                )->count(),
                'closed_count'   => $applications->filter(
                    fn($a) => optional($a->proforma)->status === 'closed'
                )->count(),
                'completed_count' => $applications->filter(
                    fn($a) => optional($a->proforma)->status === 'completed'
                )->count(),
            ],
        ]);
    }

    // -----------------------------------------------------------------------
    // GET /api/v1/garage/my-applications
    // All proformas this garage has applied to
    // -----------------------------------------------------------------------
    public function myApplications()
    {
        $user = auth()->user();

        $applications = ProformaApplication::with(['proforma.brand', 'proforma.parts'])
            ->where('application_by', $user->id)
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

    // -----------------------------------------------------------------------
    // GET /api/v1/garage/inbox
    // Proformas waiting in the garage's inbox (available to bid on)
    // -----------------------------------------------------------------------
    public function inbox()
    {
        $user = auth()->user();

        $inboxItems = Inbox::where('user_id', $user->id)
            ->with(['proforma.brand', 'proforma.parts'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'count'   => $inboxItems->count(),
            'data'    => $inboxItems->map(fn($item) => [
                'inbox_id'   => $item->proforma_id,
                'proforma'   => $item->proforma ? new ProformaResource($item->proforma) : null,
                'received_at' => $item->created_at?->toIso8601String(),
            ]),
        ]);
    }

    // -----------------------------------------------------------------------
    // GET /api/v1/garage/proformas/{id}
    // Details of one available proforma (clears inbox entry on view)
    // -----------------------------------------------------------------------
    public function proformaDetail($id)
    {
        $user = auth()->user();

        $proforma = Proforma::with(['brand', 'parts'])
            ->whereIn('status', ['pending', 'opened', 'published'])
            ->find($id);

        if (!$proforma) {
            return response()->json(['success' => false, 'message' => 'Proforma not found'], 404);
        }

        // Clear inbox entry when garage views the proforma
        Inbox::where('user_id', $user->id)
            ->where('proforma_id', $proforma->id)
            ->delete();

        $alreadyApplied = ProformaApplication::where('application_by', $user->id)
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

    // -----------------------------------------------------------------------
    // POST /api/v1/garage/proformas/{id}/apply
    // Submit a price quote for a proforma
    // -----------------------------------------------------------------------
    public function applyProforma(Request $request, $id)
    {
        $user = auth()->user();

        $proforma = Proforma::whereIn('status', ['pending', 'opened', 'published'])->find($id);

        if (!$proforma) {
            return response()->json(['success' => false, 'message' => 'Proforma not found or no longer accepting applications'], 404);
        }

        $alreadyApplied = ProformaApplication::where('application_by', $user->id)
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
                'application_by' => $user->id,
                'from'           => 'garage',
                'amount'         => $finalAmount,
                'discount'       => $discount,
            ]);

            // Clear inbox entry
            Inbox::where('user_id', $user->id)->where('proforma_id', $proforma->id)->delete();

            // Notify poster
            if ($proforma->poster && $proforma->poster->id !== $user->id) {
                $proforma->poster->notify(new ProformaApplicationReceived($proforma, $application, $user));
            }

            // Auto-close if requirements met
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
                    'application_id' => $application->id,
                    'amount'         => (float) $application->amount,
                    'discount'       => (float) $application->discount,
                    'proforma_status' => $proforma->fresh()->status,
                ],
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Garage apply proforma failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to submit quote. Please try again.'], 500);
        }
    }

    // -----------------------------------------------------------------------
    // GET /api/v1/garage/my-files
    // Proformas the garage created themselves
    // -----------------------------------------------------------------------
    public function myFiles()
    {
        $user = auth()->user();

        $proformas = Proforma::with('brand')
            ->where('poster_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => ProformaResource::collection($proformas),
        ]);
    }

    // -----------------------------------------------------------------------
    // POST /api/v1/garage/create-file
    // Create a new proforma (garage posting their own)
    // -----------------------------------------------------------------------
    public function createProforma(Request $request)
    {
        Log::info('POST /api/v1/garage/create-file', ['user_id' => auth()->id()]);

        $validated = $request->validate([
            'number_of_proformas'  => ['required', 'integer', 'min:-1', 'max:4'],
            'etera_chereta_hours'  => ['nullable', 'integer', 'in:4,8,12,24,48,72'],
            'brand_id'             => ['required', 'integer', 'exists:brands,id'],
            'car_type'             => ['required', 'in:ICE,EV,Hybrid,Others'],
            'model'                => ['required', 'string', 'max:255'],
            'year'                 => ['required', 'regex:/^(#N\/A|19\d{2}|20\d{2})$/'],
            'customer_phone_number' => ['required', 'string'],
            'chassis_number'       => ['nullable', 'string'],
            'parts'                => ['required', 'array', 'min:1'],
            'parts.*.number'       => ['required', 'string'],
            'parts.*.component'    => ['required', 'string', 'in:Body Parts,Mechanical Parts'],
            'parts.*.condition'    => ['required', 'string', 'in:New,Used,Refurbished'],
            'parts.*.grade'        => ['required', 'string'],
            'parts.*.country'      => ['required', 'string'],
            'parts.*.quantity'     => ['required', 'integer', 'min:1'],
            'parts.*.photo_paths'  => ['nullable', 'array'],
            'parts.*.photo_paths.*' => ['nullable', 'string'],
            'voice_note'           => ['nullable', 'string'],
        ]);

        DB::beginTransaction();
        try {
            $isEteraChereta = (int) $validated['number_of_proformas'] === -1;
            $eteraHours     = (int) ($validated['etera_chereta_hours'] ?? 24);
            $requiredShops  = $isEteraChereta ? 0 : (int) $validated['number_of_proformas'];
            $timerMinutes   = $isEteraChereta ? $eteraHours * 60 : null;
            $timerExpiresAt = $isEteraChereta ? now()->addMinutes($timerMinutes) : null;

            $proforma = Proforma::create([
                'poster_id'               => auth()->id(),
                'file_number'             => '#' . auth()->id() . '-' . substr(time(), -4),
                'car_brand_id'            => $validated['brand_id'],
                'car_type'                => $validated['car_type'],
                'customer_name'           => auth()->user()->name,
                'customer_phone_number'   => $validated['customer_phone_number'],
                'chassis_number'          => $validated['chassis_number'] ?? null,
                'year'                    => $validated['year'],
                'model'                   => $validated['model'],
                'required_number_of_shops' => $requiredShops,
                'required_number_of_garages' => 0,
                'timer_duration'          => $timerMinutes,
                'timer_expires_at'        => $timerExpiresAt,
            ]);

            foreach ($validated['parts'] as $partData) {
                $part = $proforma->parts()->create([
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

            // Voice note (base64 string)
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

    // -----------------------------------------------------------------------
    // POST /api/v1/garage/proformas/{id}/request-close
    // Request to close a proforma the garage created
    // -----------------------------------------------------------------------
    public function requestClose($id)
    {
        $user     = auth()->user();
        $proforma = Proforma::where('id', $id)->where('poster_id', $user->id)->first();

        if (!$proforma) {
            return response()->json(['success' => false, 'message' => 'Proforma not found'], 404);
        }

        if ($proforma->close_request) {
            return response()->json(['success' => false, 'message' => 'Close request already submitted'], 422);
        }

        $proforma->update(['close_request' => true]);

        return response()->json(['success' => true, 'message' => 'Close request submitted successfully']);
    }

    // -----------------------------------------------------------------------
    // GET /api/v1/garage/received-proformas
    // Completed proformas the garage received (they applied and won)
    // -----------------------------------------------------------------------
    public function receivedProformas()
    {
        $user = auth()->user();

        $proformas = Proforma::with('brand')
            ->where('poster_id', $user->id)
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

    // -----------------------------------------------------------------------
    // GET /api/v1/garage/balance
    // -----------------------------------------------------------------------
    public function balance()
    {
        $user = auth()->user();

        return response()->json([
            'success' => true,
            'data'    => [
                'balance'             => (float) $user->balance,
                'withdrawal_requests' => WithdrawalResource::collection(
                    $user->withdrawalRequests()->orderBy('created_at', 'desc')->get()
                ),
            ],
        ]);
    }

    // -----------------------------------------------------------------------
    // POST /api/v1/garage/withdraw
    // -----------------------------------------------------------------------
    public function submitWithdrawal(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'amount'         => ['required', 'numeric', 'min:1', 'max:' . $user->balance],
            'bank_name'      => ['required', 'string', 'in:CBE,Abyssiniya,Awash,Dashen,Enat,Wegagen,Tsedey'],
            'account_number' => ['required', 'string'],
        ]);

        $withdrawal = WithdrawalRequest::create([
            'from'           => $user->id,
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
}