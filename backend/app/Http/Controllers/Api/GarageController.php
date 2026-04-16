<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Proforma;
use App\Models\ProformaApplication;
use App\Models\WithdrawalRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GarageController extends Controller{

    public function index(){

         $user = auth()->user();
            if (!$user || $user->role !== 'garage') {
                return response()->json([
                    'success' => false,
                    'message' => 'unauthenticated'
                ], 400);
            }

            $applications = ProformaApplication::with(['proforma.brand', 'proforma.parts', 'prices'])
                ->where('application_by', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();

            $pendingCount = $applications->filter(fn($app) => in_array(optional($app->proforma)->status, ['pending', 'opened', 'published']))->count();
            $closedCount = $applications->filter(fn($app) => optional($app->proforma)->status === 'closed')->count();
            $completedCount = $applications->filter(fn($app) => optional($app->proforma)->status === 'completed')->count();
            $totalCount = $applications->count();

            return response()->json([
            'success' => true,
            'data' => [
                'applications' => $applications,
                'pendingCount' => $pendingCount,
                'closedCount'  => $closedCount,
                'completedCount' => $completedCount,
                'totalCount'    => $totalCount,

            ],
        ]);
    }

    public function createProforma(){
        Log::info('📝 POST request to garage/create-file received', [
                'user_id' => auth()->id(),
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

                Log::info('✅ Validation passed successfully');
            } catch (\Illuminate\Validation\ValidationException $e) {
                Log::error('❌ Validation failed', [
                    'errors' => $e->errors(),
                    'input_data' => $request->except(['parts.photo', 'voice_note'])
                ]);
                return redirect()->back()->withErrors($e->errors())->withInput();
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
                   
                    'chassis_number' => $request->chassis_number,
                    'year' => $request->year,
                    'model' => $request->model,
                    'required_number_of_shops' => $requiredShops,
                    'required_number_of_garages' => 0,
                    'timer_duration' => $timerMinutes,
                    'timer_expires_at' => $timerExpiresAt,
                ]);

                // 🔹 Step 3 — Handle spare parts
                // 🔹 Attach FilePond async-uploaded images (using PartsImage model)
    // 🔹 Step 3 — Handle spare parts
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

        Log::info('✅ Proforma part created', ['part_id' => $part->id, 'index' => $index]);

        // 🔹 Move temp images and attach them
        if (isset($partsData['photo'][$index]) && is_array($partsData['photo'][$index])) {
            foreach ($partsData['photo'][$index] as $photoPath) {
                if (!empty($photoPath) && str_contains($photoPath, 'uploads/temp/')) {
                $tempPath = $photoPath;
                $filename = basename($tempPath);
                $finalPath = 'uploads/part-images/' . $filename;

                // Move the file
                if (Storage::disk('public')->exists($tempPath)) {
                    Storage::disk('public')->move($tempPath, $finalPath);

                    \App\Models\PartsImage::create([
                        'proforma_part_id' => $part->id,
                        'image_path' => $finalPath,
                    ]);

                    Log::info('✅ Temp image moved and saved', [
                        'from' => $tempPath,
                        'to' => $finalPath,
                        'proforma_part_id' => $part->id,
                    ]);
                } else {
                    Log::warning('⚠️ Temp image not found', ['path' => $tempPath]);
                }
            }
                    }
                } else {
                    Log::warning('⚠️ No images found for part', ['index' => $index]);
                }
            }


                // 🔹 Step 4 — Voice note (optional)
                if ($request->filled('voice_note')) {
                    $base64 = $request->voice_note;
                    
                    // Extract the extension from the MIME type (handles codecs like audio/webm;codecs=opus)
                    if (preg_match('#^data:audio/([^;,]+)#i', $base64, $matches)) {
                        $extension = $matches[1]; // e.g. webm, mp3, wav
                    } else {
                        $extension = 'webm'; // default
                    }
                    
                    // Remove the entire data URI prefix including any codec specifications
                    // Pattern matches: data:audio/webm;codecs=opus;base64, OR data:audio/webm;base64,
                    $audioData = base64_decode(preg_replace('#^data:audio/[^;]+[^,]*,#i', '', $base64));
                    
                    if ($audioData === false) {
                        Log::error('❌ Voice note base64 decoding failed');
                    } else {
                        $filename = 'voice_note_' . time() . '_' . uniqid() . '.' . $extension;
                        Storage::disk('public')->put('voice_notes/' . $filename, $audioData);
                        $proforma->update(['voice_note_path' => 'voice_notes/' . $filename]);
                        Log::info('🎤 Voice note saved', ['filename' => $filename, 'size' => strlen($audioData)]);
                    }
                }

                DB::commit();

                // 🔔 Broadcast to admin dashboard in real-time
                event(new ProformaCreated($proforma));

                // 🔹 Step 5 — Schedule Etera-Chereta AutoSelect
                if ($isEteraChereta) {
                    AutoSelectProformaOffers::dispatch($proforma->id)->delay(now()->addMinutes($timerMinutes));
                }

                return redirect()->back()->with('success', 'Proforma created successfully!');
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('❌ Proforma creation failed', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return redirect()->back()->withErrors(['general' => 'An unexpected error occurred.'])->withInput();
            }
        
   
    }
     public function requestClose($id)
   
     {
                Log::info("🔵 Route hit: Start request-close", [
                'proforma_id' => $proformaId,
            ]);

            // Try to fetch the model normally
            $proforma = \App\Models\Proforma::find($proformaId);

            if (!$proforma) {
                Log::error("❌ Proforma not found!", [
                    'proforma_id' => $proformaId,
                ]);

                return back()->with('error', 'Proforma not found.');
            }

            Log::info("🟡 Proforma loaded", [
                'id' => $proforma->id,
                'current_close_request' => $proforma->close_request,
            ]);

            // Try updating
            $updated = $proforma->update([
                'close_request' => true,
            ]);

            Log::info("🟢 Update executed", [
                'result' => $updated,
                'new_close_request' => $proforma->fresh()->close_request,
            ]);

            return back()->with('success', 'Close request submitted.');

    }
    public function myFiles(){
         $proformas = auth()->user()
        ->proformas()
        ->orderBy('created_at', 'desc')
        ->get();

    return view('spare-part.files', compact('proformas'));
    }

    public function proformaDetails(){
         $proforma = \App\Models\Proforma::find($request->query('proforma'));
            if (! $proforma) {
                return redirect()->back();
            }

            // Reset inbox count when user opens proforma details
            if (auth()->check()) {
                // Remove the proforma from user's inbox to reset the ticker
                \App\Models\Inbox::where('user_id', auth()->id())
                    ->where('proforma_id', $proforma->id)
                    ->delete();
            }

            return view('spare-part.details', compact('proforma'));
    }
    public function applyProforma(Request $request){
        $request->validate([
                'amount' => 'required|numeric|min:1',
                'discount' => 'nullable|numeric|min:0|max:100',
            ]);

            // Calculate final amount
            $initialPrice = $request->amount;
            $discount = $request->discount ?? 0;
            $finalAmount = $request->input('final-amount', $initialPrice);
            
            // Ensure minimum amount
            $finalAmount = max($finalAmount, 1);

            $application = $proforma->applications()->create([
                'application_by' => auth()->id(),
                'from' => 'garage',
                'amount' => $finalAmount,
                'discount' => $discount,
            ]);

            // Send notification to proforma poster
            if ($proforma->poster && $proforma->poster->id !== auth()->id()) {
                $proforma->poster->notify(new ProformaApplicationReceived($proforma, $application, auth()->user()));
            }

            // Remove inbox record if exists
            $proforma->inboxes()->where('user_id', auth()->id())->delete();

            // Check if proforma should be closed (both garage and shop requirements met)
            $garageApplicationsCount = $proforma->applications()->where('from', 'garage')->count();
            $shopApplicationsCount = $proforma->applications()->where('from', 'shop')->count();
            $requiredGarages = (int) ($proforma->required_number_of_garages ?? 0);
            $requiredShops = (int) ($proforma->required_number_of_shops ?? 0);
            
            // Check if BOTH garage and shop requirements are met and not an Etera-Chereta (0,0) proforma
            $isEteraChereta = ($requiredGarages + $requiredShops) === 0;
            $garageRequirementMet = $requiredGarages === 0 || $garageApplicationsCount >= $requiredGarages;
            $shopRequirementMet = $requiredShops === 0 || $shopApplicationsCount >= $requiredShops;
            
            if (!$isEteraChereta && $garageRequirementMet && $shopRequirementMet && ($requiredGarages > 0 || $requiredShops > 0)) {
                $proforma->update(['status' => 'closed']);
                $proforma->inboxes()->delete();
            }

            return redirect('/role/proformas')
                ->with('success', 'Price quote submitted successfully!');
      
    }
    public function receivedProformas(){
         $user = auth()->user();

    // Mark all new proformas for this user as viewed
    $user->markReceivedProformasAsViewed();

    // Fetch only completed proformas for the current user, newest first, paginated
    $proformas = Proforma::where('poster_id', $user->id)
        ->where('status', 'completed')       // ✅ only completed
        ->orderBy('created_at', 'desc')      // ✅ newest first
        ->paginate(10);

    return view('spare-part.received', compact('proformas'));
    }
}