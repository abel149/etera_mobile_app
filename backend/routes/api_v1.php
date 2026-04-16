<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CreateProfoermaController;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\GarageController;

/*
|--------------------------------------------------------------------------
| ETERA Mobile API v1 Routes
|--------------------------------------------------------------------------
|
| Base URL : {APP_URL}/api/v1
|
| PUBLIC (no token)
|   POST   /api/v1/auth/login
|   GET    /api/v1/brands
|   GET    /api/v1/parts
|   POST   /api/v1/register
|   POST   /api/v1/register/individual
|   POST   /api/v1/register/business-owner
|   POST   /api/v1/register/garage-shop
|
| PROTECTED (Authorization: Bearer {token})
|   POST   /api/v1/auth/logout
|   GET    /api/v1/auth/me
|   GET    /api/v1/dashboard
|   POST   /api/v1/create-file
|   GET    /api/v1/proformas
|   GET    /api/v1/proformas/{id}
|   POST   /api/v1/proformas/{id}/request-close
|   GET    /api/v1/profile
|   PUT    /api/v1/profile
|   GET    /api/v1/balance
|   POST   /api/v1/withdraw
|   POST   /api/v1/upload/temp
|   DELETE /api/v1/upload/temp
|
*/

// -----------------------------------------------------------------------
// Public: Auth
// -----------------------------------------------------------------------
Route::post('/auth/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1'); // 5 attempts per minute

// -----------------------------------------------------------------------
// Public: Helpers (cacheable dropdowns)
// -----------------------------------------------------------------------
// Temp file upload (images)

Route::post('/upload/temp',   [\App\Http\Controllers\File\TemporaryFileController::class, 'store']);
Route::delete('/upload/temp', [\App\Http\Controllers\File\TemporaryFileController::class, 'destroy']);

Route::get('/brands', function () {
    return response()->json([
        'success' => true,
        'data'    => \App\Models\Brand::orderBy('name')->get(['id', 'name']),
    ]);
});

Route::get('/parts', function () {
    return response()->json([
        'success' => true,
        'data'    => \App\Models\CarPart::orderBy('name')->get(['id', 'name']),
    ]);
});

// -----------------------------------------------------------------------
// Public: Registration (rate-limited)
// -----------------------------------------------------------------------
Route::middleware('throttle:10,1')->group(function () {
    Route::post('/register/business-owner', [RegisterController::class, 'storeBusinessOwner']);
    Route::post('/register/garage-shop',    [RegisterController::class, 'storeGarageSparepart']);
});

// -----------------------------------------------------------------------
// Protected: requires valid Sanctum token 
// buissness owner routes
// -----------------------------------------------------------------------
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
   
    // Dashboard
    Route::get('/dashboard', [CreateProfoermaController::class, 'dashboard']);

    // Proformas
    Route::post('/create-file',                   [CreateProfoermaController::class, 'store']);
    Route::get('/proformas',                      [CreateProfoermaController::class, 'index']);
    Route::get('/proformas/{id}',                 [CreateProfoermaController::class, 'show']);
    Route::post('/proformas/{id}/request-close',  [CreateProfoermaController::class, 'requestClose']);

    // Profile
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);


});


// -----------------------------------------------------------------------
// Protected: requires valid Sanctum token 
// garage routes
// -----------------------------------------------------------------------
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/garage', [GarageController::class, 'index']);
    Route::get('/garage', [GarageController::class, 'show']);
    Route::post('/proformas/{id}/request-close',  [GarageController::class, 'requestClose']);
    Route::get('/my-files', [GarageController::class, 'myFiles']);
    Route::post('/create-file',[GarageController::class, 'store']);
    Route::get('proforma-details',[GarageController::class, 'proformaDetails']);
    Route::post('apply/{proforma}', [GarageController::class, 'applyProforma']);
    Route::get('/received-proformas',[GarageController::class, 'receivedProformas']);
    


});
