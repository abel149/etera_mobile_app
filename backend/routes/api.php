<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CreateProfoermaController;
use App\Http\Controllers\Api\RegisterController;

/*
|--------------------------------------------------------------------------
| ETERA Mobile API Routes
|--------------------------------------------------------------------------
|
| Base URL : http://127.0.0.1:8000/api
|
| PUBLIC ROUTES (no token required)
|   POST   /api/auth/login
|   GET    /api/brands
|   GET    /api/parts
|   POST   /api/register
|   POST   /api/register/individual
|   POST   /api/register/business-owner
|   POST   /api/register/garage-shop
|
| PROTECTED ROUTES (require: Authorization: Bearer {token})
|   POST   /api/auth/logout
|   GET    /api/dashboard              — Dashboard summary + proforma list
|   POST   /api/create-file            — Create new proforma
|   GET    /api/proformas              — Received proformas (paginated)
|   GET    /api/proformas/{id}         — Proforma details + applications/prices
|   POST   /api/proformas/{id}/request-close
|   GET    /api/profile                — View profile
|   PUT    /api/profile                — Update profile
|   GET    /api/balance                — Balance + withdrawal history
|   POST   /api/withdraw               — Submit withdrawal request
|   POST   /api/upload/temp            — Upload temp file (images)
|   DELETE /api/upload/temp            — Delete temp file
|
*/

// -----------------------------------------------------------------------
// Public: Auth
// -----------------------------------------------------------------------
Route::post('/auth/login', [AuthController::class, 'login']);

//profile and logout routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
});
// -----------------------------------------------------------------------
// Public: Helpers
// -----------------------------------------------------------------------
Route::get('/brands', function () {
    $brands = \App\Models\Brand::orderBy('name')->get(['id', 'name']);
    return response()->json([
        'success' => true,
        'data' => $brands,
    ]);
});
Route::get('/parts', function () {
    $parts = \App\Models\CarPart::orderBy('name')->get(['id', 'name']);
    return response()->json([
        'success' => true,
        'data' => $parts,
    ]);
});

// -----------------------------------------------------------------------
// Public: Registration
// -----------------------------------------------------------------------
Route::post('/register',                [RegisterController::class, 'store']);
Route::post('/register/individual',     [RegisterController::class, 'storeIndividual']);
Route::post('/register/business-owner', [RegisterController::class, 'storeBusinessOwner']);
Route::post('/register/garage-shop',    [RegisterController::class, 'storeGarageSparepart']);

// -----------------------------------------------------------------------
// Protected: requires valid Sanctum token 
// -----------------------------------------------------------------------
//buissness owner page 
Route::middleware('auth:sanctum')->group(function () {

    // Dashboard
    Route::get('/dashboard', [CreateProfoermaController::class, 'dashboard']);

    // Proformas
    Route::post('/create-file', [CreateProfoermaController::class, 'store']);
    Route::get('/proformas', [CreateProfoermaController::class, 'index']);
    Route::get('/proformas/{id}', [CreateProfoermaController::class, 'show']);
    Route::post('/proformas/{id}/request-close', [CreateProfoermaController::class, 'requestClose']);

   
    // Balance & Withdrawal
    Route::get('/balance', [CreateProfoermaController::class, 'balance']);
    Route::post('/withdraw', [CreateProfoermaController::class, 'submitWithdrawal']);

    // Temp file upload for mobile
    Route::post('/upload/temp', [\App\Http\Controllers\File\TemporaryFileController::class, 'store']);
    Route::delete('/upload/temp', [\App\Http\Controllers\File\TemporaryFileController::class, 'destroy']);

});
