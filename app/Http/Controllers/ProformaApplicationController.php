<?php

namespace App\Http\Controllers;

use App\Models\Proforma;
use App\Models\ProformaApplication;
use App\Models\ProformaPartPrice;
use App\Models\User;
use App\Notifications\ProformaApplicationReceived;
use Illuminate\Http\Request;
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

                // Step 1: Determine proforma type
                $requiredGarages = (int) ($proforma->required_number_of_garages ?? 0);
                $requiredShops = (int) ($proforma->required_number_of_shops ?? 0);
                $isEteraChereta = ($requiredGarages + $requiredShops) === 0;

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
                if (auth()->user()->role === 'garage') {
                    $request->validate([
                        'amount' => 'required|numeric|min:1',
                        'discount' => 'nullable|numeric|min:0|max:100',
                    ], [
                        'amount.required' => 'Price is required.',
                        'amount.numeric' => 'Price must be a valid number.',
                        'amount.min' => 'Price must be at least 1.',
                        'discount.numeric' => 'Discount must be a valid number.',
                        'discount.min' => 'Discount cannot be negative.',
                        'discount.max' => 'Discount cannot exceed 100%.',
                    ]);
                } else { // 'shop' role
                    $request->validate([
                        'total' => 'required|array',
                        'total.*' => 'required|numeric|min:0',
                        'discount' => 'nullable|numeric|min:0|max:100',
                    ], [
                        'total.required' => 'Unit prices are required.',
                        'total.*.required' => 'Unit price is required for all parts.',
                        'total.*.numeric' => 'Unit price must be a valid number.',
                        'total.*.min' => 'Unit price cannot be negative.',
                        'discount.numeric' => 'Discount must be a valid number.',
                        'discount.min' => 'Discount cannot be negative.',
                        'discount.max' => 'Discount cannot exceed 100%.',
                    ]);
                }

                Log::info('Price quote submission: validation passed', [
                    'proforma_id' => $proforma->id,
                    'role' => auth()->user()->role ?? null,
                    'discount' => $request->discount ?? null,
                    'shop_parts_count' => is_array($request->total ?? null) ? count($request->total) : null,
                ]);

                // Step 3: Calculate the final amount.
                $finalAmount = 0;
                $discount = $request->discount ?? 0;

                if (auth()->user()->role === 'garage') {
                    $initialPrice = $request->amount;
                    $discountAmount = ($initialPrice * $discount) / 100;
                    $finalAmount = $initialPrice - $discountAmount;
                } else { // 'shop' role
                    $totalAmount = 0;
                    foreach ($proforma->parts as $index => $part) {
                        $unitPrice = floatval($request->total[$index] ?? 0);
                        if ($unitPrice > 0) {
                            $quantity = $part->quantity ?? 1;
                            $partTotal = $unitPrice * $quantity;
                            $totalAmount += $partTotal;
                        }
                    }
                    $discountAmount = ($totalAmount * $discount) / 100;
                    $finalAmount = $totalAmount - $discountAmount;
                }
                $finalAmount = max($finalAmount, 1);

                Log::info('Price quote submission: totals computed', [
                    'proforma_id' => $proforma->id,
                    'final_amount' => $finalAmount,
                    'discount' => $discount,
                    'role' => auth()->user()->role ?? null,
                ]);

                // Step 4: Create a new application record.
                $application = $proforma->applications()->create([
                    'application_by' => auth()->id(),
                    'from' => auth()->user()->role,
                    'amount' => $finalAmount,
                    'discount' => $discount,
                ]);

                Log::info('Price quote submission: application created', [
                    'proforma_id' => $proforma->id,
                    'application_id' => $application->id,
                    'from' => $application->from,
                    'amount' => $application->amount,
                ]);

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
                if (auth()->user()->role === 'shop') {
                    $partsProcessed = 0;
                    foreach ($proforma->parts as $index => $part) {
                        $unitPrice = floatval($request->total[$index] ?? 0);
                        if ($unitPrice > 0) {
                            $quantity = $part->quantity ?? 1;
                            $partTotal = $unitPrice * $quantity;
                            $resolvedCarPartId = \App\Models\CarPart::firstOrCreate([
                                'name' => $part->component ?: ($part->number ?: ('Part-' . $part->id))
                            ], [
                                'component' => $part->component ?: 'Mechanical Parts'
                            ])->id;

                            $application->prices()->create([
                                'car_part_id' => $resolvedCarPartId,
                                'quantity' => $quantity,
                                'unit_price' => $unitPrice,
                                'part_total' => $partTotal,
                            ]);

                            $partsProcessed++;
                        }
                    }

                    Log::info('Price quote submission: shop part prices saved', [
                        'application_id' => $application->id,
                        'parts_processed' => $partsProcessed,
                        'total_parts' => $proforma->parts->count()
                    ]);
                }

                // Step 8: Check if the proforma should be closed.
                // Re-count after insert (within the lock) to get accurate numbers.
                $garageApplicationsCount = $proforma->applications()->where('from', 'garage')->count();
                $shopApplicationsCount = $proforma->applications()->where('from', 'shop')->count();

                $garageRequirementMet = $requiredGarages === 0 || $garageApplicationsCount >= $requiredGarages;
                $shopRequirementMet = $requiredShops === 0 || $shopApplicationsCount >= $requiredShops;

                if (!$isEteraChereta && $garageRequirementMet && $shopRequirementMet) {
                    // Use ProformaClosingService to close properly (sends billing email)
                    $closingService = new \App\Services\ProformaClosingService();
                    $closingService->closeProforma($proforma, auth()->id());

                    Log::info('Price quote submission: proforma closed via service (requirements met)', [
                        'proforma_id' => $proforma->id,
                        'application_id' => $application->id,
                    ]);
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
