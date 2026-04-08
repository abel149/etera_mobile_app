<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Controllers\CarPartController;
use App\Http\Controllers\ProformaController;
use App\Http\Controllers\ProformaApplicationDataController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\File\TemporaryFileController;
use App\Http\Controllers\LevelController;
use App\Http\Controllers\PartnerController;
use Illuminate\Support\Facades\Broadcast;
use App\Http\Controllers\AccountantController;
use App\Http\Controllers\TempController;
use App\Http\Controllers\ProformaApplicationController;
use App\Http\Controllers\UserBalanceController;
use App\Http\Controllers\WithdrawalController;
use App\Http\Middleware\GarageMiddleware;
use App\Http\Middleware\ShopMiddleware;
use App\Models\Inbox;
use App\Models\Proforma;
use App\Models\ProformaPart;
use App\Models\PartsImages;
use App\Models\ProformaSelection;
use App\Models\User;
use App\Models\BrandUser;
use App\Models\Brand;
use App\Models\Cost;
use App\Models\Commission;
use App\Models\PaidUser;
use App\Models\CarPart;
use App\Models\ProformaApplication;
use App\Models\ProformaInvoice;
use App\Services\AudioService;
use App\Services\ImageService;
use App\Services\TelegramService;
use App\Services\VideoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

use App\Jobs\AutoSelectProformaOffers;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GarageController;
use App\Http\Controllers\BusinessOwnerController;
use App\Http\Controllers\MarketerController;

use App\Http\Controllers\MarketerBusinessController;
use App\Http\Controllers\ProfileController;

use App\Http\Controllers\NotificationController;
use App\Notifications\ProformaApplicationReceived;
use App\Http\Controllers\UserReviewController;

use App\Events\ProformaPublished;
use App\Events\ProformaCreated;
use App\Http\Controllers\LogViewerController;

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
                dd($e->getMessage());
                 return response()->json([
                'success' => false,
                'message' => 'validation failed',
            ], 500);
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
                }
                catch (\Exception $e) {
                DB::rollBack();
                Log::error('❌ Proforma creation failed for business-owner', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                dd($e->getMessage());
                 return response()->json([
                'success' => false,
                'message' => 'prforma request failed. Please try again.',
            ], 500); }

    }
}
