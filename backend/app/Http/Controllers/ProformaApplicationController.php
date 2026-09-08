<?php

namespace App\Http\Controllers;

use App\Models\ApplicationPdf;
use App\Models\Inbox;
use App\Models\Partial;
use App\Models\Proforma;
use App\Models\ProformaApplication;
use App\Models\ProformaPartPrice;
use App\Models\User;
use App\Notifications\ProformaApplicationReceived;
use App\Services\ProformaGroupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProformaApplicationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // This method is not yet implemented.
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // This method is not yet implemented.
    }

    /**
     * Store a newly created resource in storage.
     * This method handles the complex logic of submitting a price quote.
     */
    public function store(Request $request, Proforma $proforma)
    {
        try {
            // Wrap everything in a transaction with row-level locking to prevent race conditions
            return \Illuminate\Support\Facades\DB::transaction(function () use ($request, $proforma) {

                // Lock the proforma row to prevent simultaneous applications
                $proforma = Proforma::where('id', $proforma->id)->lockForUpdate()->first();

                if (!$proforma || !in_array($proforma->status, ['pending', 'published', 'opened'])) {
                    $redirectUrl = auth()->user()->role === 'garage' ? '/garage/proformas' : '/spare-part-shops/proformas';
                    return redirect($redirectUrl)->with('error', 'This proforma is no longer accepting applications.');
                }

                // Guard: prevent duplicate applications from the same user (concurrent session safety)
                // Exception: if the user has an active Partial record, they may apply to a different group.
                $alreadyApplied = ProformaApplication::where('proforma_id', $proforma->id)
                    ->where('application_by', auth()->id())
                    ->exists();
                $hasActivePartial = \App\Models\Partial::where('proforma_id', $proforma->id)
                    ->where('user_id', auth()->id())
                    ->where('active', true)
                    ->exists();
                if ($alreadyApplied && !$hasActivePartial) {
                    $redirectUrl = auth()->user()->role === 'garage' ? '/garage/proformas' : '/spare-part-shops/proformas';
                    return redirect($redirectUrl)->with('error', 'You have already applied to this proforma.');
                }

                // Step 1: Determine proforma type
                $requiredGarages = (int) ($proforma->required_number_of_garages ?? 0);
                $requiredShops = (int) ($proforma->required_number_of_shops ?? 0);
                $isEteraChereta = ($requiredGarages + $requiredShops) === 0;
                $isDualService = $proforma->isShopGarageInsurance();
                $isShopRole = auth()->user()->role === 'shop';

                if ($isDualService && auth()->user()->shop_garage != 1) {
                    $redirectUrl = auth()->user()->role === 'garage' ? '/garage/proformas' : '/spare-part-shops/proformas';
                    return redirect($redirectUrl)
                        ->with('error', 'This proforma is only available to dual-service providers.');
                }

                // Step 1b: Logging
                Log::info('Price quote submission: started', [
                    'proforma_id' => $proforma->id ?? null,
                    'user_id' => auth()->id(),
                    'role' => auth()->user()->role ?? null,
                    'applications_count' => $proforma->applications()->count(),
                ]);

                $totalApplications = $proforma->applications()->count();
                $isInboxedUser = $proforma->inboxes()->where('user_id', auth()->id())->exists();
                $hasInboxedUsers = $proforma->inboxes()->exists();

                // Step 2: Validate the request data based on the user's role.
                $isEncrypted = $request->boolean('prices_encrypted', false);

                // For dual service proformas, any user with shop_garage=1 goes through the shop path
                // (parts pricing + garage amount). For non-dual-service, garages use the garage-only path.
                $useShopPath = $isShopRole || ($isDualService && auth()->user()->shop_garage == 1);

                if (!$useShopPath) {
                    if ($isEncrypted) {
                        $request->validate(['encrypted_amount' => 'required|string']);
                    } else {
                        $garagePdfBypass = auth()->user()->shop_garage == 1
                            && ($request->filled('encrypted_pdf') || $request->filled('pdf_data'));
                        if (!$garagePdfBypass) {
                            $request->validate([
                                'amount' => 'required|numeric|min:1',
                                'expiry_date' => 'nullable|date|after:today',
                            ], [
                                'amount.required' => 'Price is required.',
                                'amount.numeric' => 'Price must be a valid number.',
                                'amount.min' => 'Price must be at least 1.',
                                'expiry_date.date' => 'Expiry date must be a valid date.',
                                'expiry_date.after' => 'Expiry date must be after today.',
                            ]);
                        }
                    }
                } else { // Shop role or dual-service garage with shop_garage=1
                    // Check for PDF early so we can bypass price validation for PDF-only
                    $hasPdf = $request->filled('encrypted_pdf') || $request->filled('pdf_data');

                    if ($isEncrypted) {
                        $encryptedRules = [];
                        if (!$hasPdf) {
                            $encryptedRules['encrypted_total'] = 'required|array';
                        }
                        if ($isDualService) {
                            $encryptedRules['encrypted_amount'] = 'required|string';
                        }
                        if (!empty($encryptedRules)) {
                            $request->validate($encryptedRules);
                        }
                    } else {
                        $request->validate([
                            'total' => 'nullable|array',
                            'total.*' => 'nullable|numeric|min:1',
                            'expiry_date' => 'nullable|date|after:today',
                            'garage_amount' => $isDualService ? 'required|numeric|min:1' : 'nullable|numeric|min:1',
                            'garage_expiry_date' => 'nullable|date|after:today',
                        ], [
                            'total.*.numeric' => 'Unit price must be a valid number.',
                            'total.*.min' => 'Unit price must be at least 1. Leave the field blank if you do not carry this part.',
                            'expiry_date.date' => 'Expiry date must be a valid date.',
                            'expiry_date.after' => 'Expiry date must be after today.',
                        ]);

                        $hasAtLeastOnePrice = collect($request->input('total', []))
                            ->filter(fn($v) => $v !== null && floatval($v) > 0)
                            ->isNotEmpty();

                        if (!$hasAtLeastOnePrice && !$hasPdf) {
                            return redirect()->back()
                                ->withErrors(['total' => 'Please enter a price for at least one part. Leave fields blank only for parts you do not carry.'])
                                ->withInput();
                        }
                    }
                }

                Log::info('Price quote submission: validation passed', [
                    'proforma_id' => $proforma->id,
                    'role' => auth()->user()->role ?? null,
                    'shop_parts_count' => is_array($request->total ?? null) ? count($request->total) : null,
                ]);

                // Resolve $hasPdf for garage role (shop sets it above)
                $hasPdf = $hasPdf ?? ($request->filled('encrypted_pdf') || $request->filled('pdf_data'));
                $isPdfOnly = $useShopPath
                    && $hasPdf
                    && !$request->filled('encrypted_total')
                    && empty(array_filter($request->input('total', [])));

                // Step 2b: Insurance proformas require encrypted submissions — when the poster
                // has encryption set up. Posters without keys accept plain submissions.
                // Exception: a PDF-only submission counts as acceptable (PDF is encrypted client-side).
                if (!$isEncrypted && in_array(optional($proforma->poster)->role, ['insurance', 'insurance_agent']) && optional($proforma->poster)->has_encryption && !$hasPdf) {
                    $redirectUrl = auth()->user()->role === 'garage' ? '/garage/proformas' : '/spare-part-shops/proformas';
                    return redirect($redirectUrl)
                        ->withErrors(['general' => 'Encrypted price submission is required for this proforma. Please contact the insurance.'])
                        ->withInput();
                }

                // Step 3: Calculate the final amount (0 placeholder when encrypted).
                // Prices arrive as NET from the frontend; add 15% VAT to get the stored amount.
                $vatRate = 0.15;
                $finalAmount = 0;

                if ($isEncrypted) {
                    // Encrypted mode: amount is a ciphertext; store 0 as numeric placeholder
                    $finalAmount = 0;
                } elseif (!$useShopPath) {
                    if (isset($garagePdfBypass) && $garagePdfBypass) {
                        $finalAmount = 0; // PDF-only submission; no price entered
                    } else {
                        $netAmount = max($request->amount, 1);
                        $finalAmount = round($netAmount * (1 + $vatRate), 2);
                    }
                } else { // Shop role or dual-service garage with shop_garage=1
                    $totalAmount = 0;
                    foreach ($proforma->parts->sortBy('id')->values() as $index => $part) {
                        $unitPrice = floatval($request->total[$index] ?? 0);
                        if ($unitPrice > 0) {
                            $quantity = $part->quantity ?? 1;
                            $partTotal = $unitPrice * $quantity;
                            $totalAmount += $partTotal;
                        }
                    }
                    // Add 15% VAT to the net total
                    $finalAmount = round($totalAmount * (1 + $vatRate), 2);
                    // PDF-only submission has no price; avoid forcing min:1
                    $isPdfOnlyCalc = $hasPdf && $totalAmount == 0;
                    if (!$isPdfOnlyCalc) {
                        $finalAmount = max($finalAmount, 1);
                    }
                }

                // Dual-service proformas store the GARAGE estimate as the application amount (NET).
                // Part totals are derived from the per-part prices table.
                if (!$isEncrypted && $useShopPath && $isDualService) {
                    $finalAmount = max((float) ($request->garage_amount ?? 0), 1);
                }

                Log::info('Price quote submission: totals computed', [
                    'proforma_id' => $proforma->id,
                    'final_amount' => $finalAmount,
                    'role' => auth()->user()->role ?? null,
                ]);

                // Step 4: Detect inbox source AND group BEFORE deleting inbox
                $role     = auth()->user()->role;
                // For dual service proformas, a garage with shop_garage=1 submits as 'shop'
                // (parts pricing + garage amount) so group assignment and billing work correctly.
                $effectiveFrom = ($isDualService && auth()->user()->shop_garage == 1) ? 'shop' : $role;
                $ownInbox = $proforma->inboxes()->where('user_id', auth()->id())->first();
                $isInsuranceInboxed = $ownInbox && $ownInbox->source === 'insurance';
                $isAdminInboxed     = $ownInbox && $ownInbox->source === 'admin';
                $inboxGroup         = $ownInbox?->inbox_group; // 1, 2, 3, or null (legacy)

                $applicationSource = $isInsuranceInboxed ? 'partner' : ($isAdminInboxed ? 'admin' : 'public');

                // Step 4a: For shop submissions, resolve the actual group number.
                $groupService = new ProformaGroupService();
                $isPartialApplication = false;

                if ($effectiveFrom === 'shop' && $requiredShops > 0 && !$isDualService) {
                    $applicationMode = $request->input('application_mode');
                    $requestedGroup = $request->integer('assigned_group');

                    if ($applicationMode === 'full') {
                        $inboxGroup = $groupService->autoAssignGroup($proforma);

                        if ($inboxGroup === null) {
                            return redirect('/spare-part-shops/proformas')
                                ->with('error', 'No empty proforma group is available. Please use one of the partial proforma cards.');
                        }
                    } elseif ($applicationMode === 'partial') {
                        $ownPartial = Partial::where('proforma_id', $proforma->id)
                            ->where('user_id', auth()->id())
                            ->where('inbox_group', $requestedGroup)
                            ->where('active', true)
                            ->first();

                        if (! $ownPartial) {
                            return redirect('/spare-part-shops/proformas')
                                ->with('error', 'This partial proforma is no longer available.');
                        }

                        $inboxGroup = $ownPartial->inbox_group;
                        $isPartialApplication = true;
                    } else {
                        $ownPartial = Partial::where('proforma_id', $proforma->id)
                            ->where('user_id', auth()->id())
                            ->where('active', true)
                            ->first();

                        if ($ownPartial) {
                            $inboxGroup = $ownPartial->inbox_group;
                            $isPartialApplication = true;
                        } elseif ($inboxGroup === null) {
                            $inboxGroup = $groupService->autoAssignGroup($proforma);

                            if ($inboxGroup === null && $isAdminInboxed) {
                                $inboxGroup = $groupService->findFirstIncompleteGroup($proforma);
                            }

                            if ($inboxGroup === null) {
                                return redirect()->back()->with('error', 'All available slots are currently being filled. You may receive a notification if additional pricing is needed.');
                            }
                        }
                    }
                }

                // For non-group proformas (required_number_of_shops = 0), the inbox_group on the
                // shop's inbox record is just a counter with no slot meaning.  Writing it into
                // proforma_part_prices would hit the unique constraint when two shops share the
                // same counter value.  Use null so the constraint is bypassed for normal flow.
                $priceGroup = ($requiredShops > 0) ? $inboxGroup : null;

                // Step 4b: Create a new application record.
                $appData = [
                    'application_by'    => auth()->id(),
                    'from'              => $effectiveFrom,
                    'amount'            => $finalAmount,
                    'discount'          => 0,
                    'notes'             => $request->filled('notes') ? trim($request->notes) : null,
                    'application_source'=> $applicationSource,
                    'inbox_group'       => $inboxGroup,
                    'expiry_date'       => $request->filled('expiry_date') ? $request->expiry_date : null,
                ];
                if ($isEncrypted && $request->filled('encrypted_amount')) {
                    $appData['encrypted_amount']   = $request->encrypted_amount;
                    $appData['amount_is_encrypted'] = true;
                }
                if ($isDualService) {
                    $appData['notes'] = $request->filled('garage_notes') ? trim($request->garage_notes) : $appData['notes'];
                    $appData['expiry_date'] = $request->filled('garage_expiry_date') ? $request->garage_expiry_date : $appData['expiry_date'];
                }
                $application = $proforma->applications()->create($appData);

                // Remove own inbox record (insurance or admin)
                \App\Models\Inbox::where('user_id', auth()->id())
                    ->where('proforma_id', $proforma->id)
                    ->delete();

                if ($isDualService && $isInsuranceInboxed && $inboxGroup !== null) {
                    // Clear all shop and dual-service garage inboxes for this group
                    $dualServiceUserIds = User::where(function ($q) {
                        $q->where('role', 'shop')->orWhere(function ($q2) {
                            $q2->where('role', 'garage')->where('shop_garage', 1);
                        });
                    })->pluck('id');
                    $proforma->inboxes()
                        ->where('source', 'insurance')
                        ->where('inbox_group', $inboxGroup)
                        ->whereIn('user_id', $dualServiceUserIds)
                        ->delete();
                }

                // Chereta (legacy null-group only): when quota of insurance partners applied, clear null-group inboxes.
                // Per-group chereta is now deferred to after prices are saved (fires on group completion).
                if ($isInsuranceInboxed && $inboxGroup === null) {
                    $roleUserIds = User::where('role', $effectiveFrom)->pluck('id');
                    $partnerApplied = $proforma->applications()
                        ->where('from', $effectiveFrom)
                        ->where('application_source', 'partner')
                        ->count();
                    $quota = $effectiveFrom === 'shop'
                        ? (int) ($proforma->insurance_shop_quota ?? 1)
                        : (int) ($proforma->insurance_garage_quota ?? 1);
                    if ($partnerApplied >= $quota) {
                        $proforma->inboxes()
                            ->where('source', 'insurance')
                            ->whereNull('inbox_group')
                            ->whereIn('user_id', $roleUserIds)
                            ->delete();
                    }
                }

                Log::info('Price quote submission: application created', [
                    'proforma_id' => $proforma->id,
                    'application_id' => $application->id,
                    'from' => $application->from,
                    'amount' => $application->amount,
                ]);

                // Step 4b: Handle PDF/image upload — save to local disk, not DB column
                if ($hasPdf) {
                    try {
                        $originalFilename = $request->pdf_filename ?? 'quotation.pdf';
                        $ext = strtolower(pathinfo($originalFilename, PATHINFO_EXTENSION)) ?: 'pdf';

                        if ($request->filled('encrypted_pdf')) {
                            // Store encrypted binary on disk; keep AES key + IV in DB (they are short strings)
                            $encBytes  = base64_decode($request->encrypted_pdf);
                            $filename  = $application->id . '_' . \Illuminate\Support\Str::uuid() . '.enc';
                            Storage::disk('local')->put('application-files/' . $filename, $encBytes);

                            ApplicationPdf::create([
                                'application_id'    => $application->id,
                                'storage_type'      => 'encrypted',
                                'file_path'         => 'application-files/' . $filename,
                                'encrypted_aes_key' => $request->encrypted_aes_key,
                                'aes_iv'            => $request->aes_iv,
                                'original_filename' => $originalFilename,
                            ]);
                        } elseif ($request->filled('pdf_data')) {
                            // Store plain file binary on disk
                            $fileBytes = base64_decode($request->pdf_data);
                            $filename  = $application->id . '_' . \Illuminate\Support\Str::uuid() . '.' . $ext;
                            Storage::disk('local')->put('application-files/' . $filename, $fileBytes);

                            ApplicationPdf::create([
                                'application_id'    => $application->id,
                                'storage_type'      => 'plain',
                                'file_path'         => 'application-files/' . $filename,
                                'original_filename' => $originalFilename,
                            ]);
                        }
                        Log::info('Application file stored on disk', ['application_id' => $application->id]);
                    } catch (\Exception $e) {
                        Log::error('Failed to store application file: ' . $e->getMessage());
                        throw $e;
                    }
                }

                // Step 5: Handle voice note uploads.
                if ($request->has('voice_note') && !empty($request->voice_note)) {
                    try {
                        $voiceNoteData = $request->voice_note;
                        if (strpos($voiceNoteData, 'data:audio') === 0) {
                            $base64Data = explode(',', $voiceNoteData)[1];
                            $audioData = base64_decode($base64Data);
                            $filename = 'voice_note_' . time() . '_' . uniqid() . '.webm';
                            $path = 'voice_notes/' . $filename;
                            Storage::disk('public')->put($path, $audioData);

                            $application->addMediaFromDisk($path, 'public')
                                ->toMediaCollection('voice_notes');
                            
                            Log::info('Voice note uploaded successfully', [
                                'application_id' => $application->id,
                                'filename' => $filename,
                                'path' => $path
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::error('Error uploading voice note: ' . $e->getMessage());
                    }
                }

                // Step 6: Calculate progress for notifications
                $requiredShopsForNotif = (int) ($proforma->required_number_of_shops ?? 0);
                $requiredGaragesForNotif = (int) ($proforma->required_number_of_garages ?? 0);
                $totalRequired = $requiredShopsForNotif + $requiredGaragesForNotif;
                $currentCount = $proforma->applications()->count();

                // Step 6a: Send Telegram notification to the poster about the new application
                try {
                    if ($proforma->poster && !empty($proforma->poster->telegram_chat_id)) {
                        $telegram = new \App\Services\TelegramService();
                        $telegram->sendApplicationReceivedNotification(
                            $proforma->poster->telegram_chat_id,
                            $proforma,
                            auth()->user()->role
                        );
                    }
                } catch (\Throwable $e) {
                    Log::warning('Failed to send application received Telegram notification', [
                        'proforma_id' => $proforma->id,
                        'error' => $e->getMessage(),
                    ]);
                }

                // Step 7: Save individual part prices for shops.
                $filledPartsCount = 0;
                $skippedPartsCount = 0;

                if ($useShopPath && !$isPdfOnly) {
                    $partsProcessed = 0;
                    $totalPartsCount = $proforma->parts()->count();

                    // Pre-fetch already-priced car_part_ids for this group to skip locked parts.
                    // Only count rows with a real price (unit_price > 0 OR encrypted) so that
                    // legacy zero-price rows never permanently block a part from being priced.
                    // $priceGroup is null for normal (non-group) proformas; skip the lookup.
                    $alreadyPricedCarPartIds = ($priceGroup !== null)
                        ? ProformaPartPrice::where('proforma_id', $proforma->id)
                            ->where('inbox_group', $priceGroup)
                            ->where(function ($q) {
                                $q->where('unit_price', '>', 0)
                                  ->orWhere('price_is_encrypted', true);
                            })
                            ->pluck('car_part_id')
                            ->toArray()
                        : [];

                    foreach ($proforma->parts->sortBy('id')->values() as $index => $part) {
                        $quantity = $part->quantity ?? 1;

                        // Use the ProformaPart's own id as the CarPart name so each part always
                        // maps to a unique car_part_id — prevents duplicate-key errors when
                        // multiple proforma parts share the same component/category name.
                        $resolvedCarPartId = \App\Models\CarPart::firstOrCreate([
                            'name' => 'ppart_' . $part->id,
                        ], [
                            'component' => $part->component ?: 'Mechanical Parts',
                        ])->id;

                        // Skip if this part is already priced in this group (locked by an earlier shop)
                        if (in_array($resolvedCarPartId, $alreadyPricedCarPartIds)) {
                            $skippedPartsCount++;
                            continue;
                        }

                        if ($isEncrypted) {
                            $encryptedPrice = $request->encrypted_total[$index] ?? null;
                            // Skip entirely if no encrypted value provided for this slot
                            if (!$encryptedPrice) { continue; }
                            $application->prices()->create([
                                'car_part_id'          => $resolvedCarPartId,
                                'proforma_id'          => $proforma->id,
                                'inbox_group'          => $priceGroup,
                                'quantity'             => $quantity,
                                'unit_price'           => 0,
                                'part_total'           => 0,
                                'encrypted_unit_price' => $encryptedPrice,
                                'encrypted_part_total' => null,
                                'price_is_encrypted'   => true,
                            ]);
                            $partsProcessed++; $filledPartsCount++;
                        } else {
                            $unitPrice = floatval($request->total[$index] ?? 0);
                            // Skip blank/zero-price entries — don't pollute the group with
                            // zero rows that would block partial triggering and the unique constraint.
                            if ($unitPrice <= 0) { continue; }
                            $partTotal = $unitPrice * $quantity;
                            $application->prices()->create([
                                'car_part_id' => $resolvedCarPartId,
                                'proforma_id' => $proforma->id,
                                'inbox_group' => $priceGroup,
                                'quantity'    => $quantity,
                                'unit_price'  => $unitPrice,
                                'part_total'  => $partTotal,
                            ]);
                            $partsProcessed++; $filledPartsCount++;
                        }
                    }

                    // Track partial fill stats on the application record
                    $application->update([
                        'filled_parts_count' => $filledPartsCount,
                        'total_parts_count'  => $totalPartsCount,
                        'is_partial'         => !$isDualService && $filledPartsCount < $totalPartsCount,
                    ]);

                    if ($skippedPartsCount > 0) {
                        Log::info('Price quote submission: some parts were already priced in this group', [
                            'proforma_id'        => $proforma->id,
                            'inbox_group'        => $inboxGroup,
                            'skipped_parts'      => $skippedPartsCount,
                            'filled_parts'       => $filledPartsCount,
                        ]);
                    }

                    // Per-group chereta: if the group is now complete, delete remaining group inboxes.
                    // Only SHOP inboxes — garage inboxes share the same group numbers on standard
                    // proformas and must NOT be removed when a shop group completes.
                    if (!$isDualService && $inboxGroup !== null && $groupService->isGroupComplete($proforma, $inboxGroup)) {
                        $proforma->inboxes()
                            ->whereIn('source', ['insurance', 'admin'])
                            ->where('inbox_group', $inboxGroup)
                            ->whereHas('user', fn ($q) => $q->where('role', 'shop'))
                            ->delete();
                        Partial::deactivateGroup($proforma->id, $inboxGroup);

                        Log::info('Price quote submission: group complete, chereta fired', [
                            'proforma_id' => $proforma->id,
                            'inbox_group' => $inboxGroup,
                        ]);
                    }
                }

                if ($isPdfOnly && $application->pdf()->exists()) {
                    $totalPartsCount = $proforma->parts()->count();
                    $application->update([
                        'filled_parts_count' => $totalPartsCount,
                        'total_parts_count'  => $totalPartsCount,
                        'is_partial'         => false,
                    ]);

                    if ($inboxGroup !== null) {
                        $proforma->inboxes()
                            ->where('source', 'insurance')
                            ->where('inbox_group', $inboxGroup)
                            ->whereHas('user', fn ($q) => $q->where('role', 'shop'))
                            ->delete();
                        Partial::deactivateGroup($proforma->id, $inboxGroup);
                    }
                }

                // Clear any Partial records for this shop on this proforma (one-submission rule)
                Partial::clearForUser($proforma->id, auth()->id());

                // Step 8: Check if the proforma should be closed.
                // For shop-only insurance proformas: close when all required GROUPS are fully priced.
                // For garage/etera proformas: use existing application-count logic (unchanged).
                $garageApplicationsCount = $proforma->applications()->where('from', 'garage')->count();

                $garageRequirementMet = $requiredGarages === 0 || $garageApplicationsCount >= $requiredGarages;

                if ($requiredShops > 0 && !$isEteraChereta && !$isDualService) {
                    // New: count groups where all parts are priced
                    $totalPartsForClose = $proforma->parts()->count();
                    $completePriceGroups = $totalPartsForClose > 0
                        ? ProformaPartPrice::where('proforma_id', $proforma->id)
                            ->whereNotNull('inbox_group')
                            ->select('inbox_group')
                            ->groupBy('inbox_group')
                            ->havingRaw('COUNT(DISTINCT car_part_id) >= ?', [$totalPartsForClose])
                            ->pluck('inbox_group')
                        : collect();
                    $pdfGroups = $proforma->applications()
                        ->where('from', 'shop')
                        ->whereNotNull('inbox_group')
                        ->whereHas('pdf')
                        ->pluck('inbox_group');
                    $completeGroupCount = $completePriceGroups->merge($pdfGroups)->unique()->count();
                    $shopRequirementMet = $completeGroupCount >= $requiredShops;
                } else {
                    $shopApplicationsCount = $proforma->applications()->where('from', 'shop')->count();
                    $shopRequirementMet = $requiredShops === 0 || $shopApplicationsCount >= $requiredShops;
                }

                if (!$isEteraChereta && $garageRequirementMet && $shopRequirementMet) {
                    $closingService = new \App\Services\ProformaClosingService();
                    $closingService->closeProforma($proforma, auth()->id());

                    Log::info('Price quote submission: proforma closed via service (requirements met)', [
                        'proforma_id' => $proforma->id,
                        'application_id' => $application->id,
                    ]);
                }

                // Step 8b: Post-submit partial trigger — check if this group now needs broadcast help.
                // Runs outside the closing check so partials fire even when the proforma stays open.
                if ($effectiveFrom === 'shop' && !$isDualService && !$isPdfOnly && $inboxGroup !== null && !($garageRequirementMet && $shopRequirementMet)) {
                    $groupService->checkAndTriggerPartials($proforma, $inboxGroup);
                }

                // Step 9: Redirect with a success message.
                $redirectUrl = route('role.proformas');
                Log::info('Price quote submission: completed', [
                    'proforma_id' => $proforma->id,
                    'application_id' => $application->id,
                    'redirect' => $redirectUrl,
                ]);
                return redirect($redirectUrl)->with('success', 'Price quote submitted successfully!');

            }); // end DB::transaction

        } catch (\Throwable $e) {
            Log::error('Price quote submission: failed', [
                'proforma_id' => $proforma->id ?? null,
                'user_id' => auth()->id() ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'Failed to submit price quote. Please try again.');
        }
    }
    
    /**
     * Display the specified resource.
     * This method is not yet implemented.
     */
    public function show(ProformaApplication $proformaApplication)
    {
        // This method is intentionally empty. The logic to set a proforma as "not new"
        // should be in the ProformaController's `show` method, as that is when a proforma is viewed.
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProformaApplication $proformaApplication)
    {
        // This method is not yet implemented.
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProformaApplication $proformaApplication)
    {
        // This method is not yet implemented.
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProformaApplication $proformaApplication)
    {
        // This method is not yet implemented.
    }
}
