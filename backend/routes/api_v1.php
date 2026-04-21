<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BillingController;
use App\Http\Controllers\Api\BusinessOwnerController;
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
    Route::post('/register/others', [RegisterController::class, 'storeOthers']);
    Route::post('/register/business-owner', [RegisterController::class, 'storeBusinessOwner']);
    Route::post('/register/garage-shop',    [RegisterController::class, 'storeGarageSparepart']);
});

// -----------------------------------------------------------------------
// Protected: Shared — all authenticated users (any role)
// -----------------------------------------------------------------------
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/auth/logout',  [AuthController::class, 'logout']);
    Route::get('/profile',       [AuthController::class, 'profile']);
    Route::put('/profile',       [AuthController::class, 'updateProfile']);

});

// -----------------------------------------------------------------------
// Protected: Others routes  (role: others)
// All prefixed /api/v1/others/...
// -----------------------------------------------------------------------
Route::middleware('auth:sanctum')->prefix('others')->group(function () {

    // Dashboard & Proformas
    Route::get('/dashboard',                          [CreateProfoermaController::class, 'dashboard']);
    Route::post('/create-file',                       [CreateProfoermaController::class, 'store']);
    Route::get('/proformas',                          [CreateProfoermaController::class, 'index']);
    Route::get('/proformas/{id}',                     [CreateProfoermaController::class, 'show']);
    Route::post('/proformas/{id}/request-close',      [CreateProfoermaController::class, 'requestClose']);

    // Billing
    Route::get('/billing',                            [BillingController::class, 'overview']);
    Route::put('/billing/plan',                       [BillingController::class, 'updatePlan']);
    Route::get('/billing/statements',                 [BillingController::class, 'statements']);
    Route::get('/billing/statements/{sku}',           [BillingController::class, 'statementDetail']);

});

// -----------------------------------------------------------------------
// Protected: Business Owner routes  (role: business_owner)
// All prefixed /api/v1/business-owner/...
// -----------------------------------------------------------------------
Route::middleware('auth:sanctum')->prefix('business-owner')->group(function () {

    // Dashboard
    Route::get('/dashboard',                          [BusinessOwnerController::class, 'dashboard']);

    // Proformas
    Route::post('/create-file',                       [BusinessOwnerController::class, 'createProforma']);
    Route::get('/proformas',                          [BusinessOwnerController::class, 'index']);
    Route::get('/proformas/{id}',                     [BusinessOwnerController::class, 'show']);
    Route::post('/proformas/{id}/request-close',      [BusinessOwnerController::class, 'requestClose']);

    // Balance & Withdrawals
    Route::get('/balance',                            [BusinessOwnerController::class, 'balance']);
    Route::post('/withdraw',                          [BusinessOwnerController::class, 'submitWithdrawal']);

    // Employee management
    Route::get('/employees',                          [BusinessOwnerController::class, 'listEmployees']);
    Route::post('/employees',                         [BusinessOwnerController::class, 'createEmployee']);

    // Billing
    Route::get('/billing',                            [BillingController::class, 'overview']);
    Route::put('/billing/plan',                       [BillingController::class, 'updatePlan']);
    Route::get('/billing/statements',                 [BillingController::class, 'statements']);
    Route::get('/billing/statements/{sku}',           [BillingController::class, 'statementDetail']);

});

// -----------------------------------------------------------------------
// Protected: Garage routes  (role: garage)
// All prefixed /api/v1/garage/...
// -----------------------------------------------------------------------
Route::middleware('auth:sanctum')->prefix('garage')->group(function () {

    // Dashboard & Inbox
    Route::get('/dashboard',                          [GarageController::class, 'dashboard']);
    Route::get('/inbox',                              [GarageController::class, 'inbox']);

    // Applications (proformas garage bids on)
    Route::get('/my-applications',                    [GarageController::class, 'myApplications']);
    Route::get('/proformas/{id}',                     [GarageController::class, 'proformaDetail']);
    Route::post('/proformas/{id}/apply',              [GarageController::class, 'applyProforma']);

    // Files (proformas garage created)
    Route::get('/my-files',                           [GarageController::class, 'myFiles']);
    Route::get('/my-files/{id}',                      [GarageController::class, 'showMyFile']);
    Route::post('/create-file',                       [GarageController::class, 'createProforma']);
    Route::post('/proformas/{id}/request-close',      [GarageController::class, 'requestClose']);
    Route::get('/received-proformas',                 [GarageController::class, 'receivedProformas']);

    // Balance & Withdrawals
    Route::get('/balance',                            [GarageController::class, 'balance']);
    Route::post('/withdraw',                          [GarageController::class, 'submitWithdrawal']);

    // Employee management
    Route::get('/employees',                          [GarageController::class, 'listEmployees']);
    Route::post('/employees',                         [GarageController::class, 'createEmployee']);

    // Billing
    Route::get('/billing',                            [BillingController::class, 'overview']);
    Route::put('/billing/plan',                       [BillingController::class, 'updatePlan']);
    Route::get('/billing/statements',                 [BillingController::class, 'statements']);
    Route::get('/billing/statements/{sku}',           [BillingController::class, 'statementDetail']);

});
