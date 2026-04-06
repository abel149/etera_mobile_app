<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RegisterController;

/*
|--------------------------------------------------------------------------
| ETERA Mobile API Routes
|--------------------------------------------------------------------------
|
| Base URL : http://127.0.0.1:8000/api
|
| PUBLIC ROUTES (no token required)
|   POST  /api/auth/login
|   GET   /api/brands
|   POST  /api/register
|   POST  /api/register/individual
|   POST  /api/register/business-owner
|   POST  /api/register/garage-shop
|
| PROTECTED ROUTES (require: Authorization: Bearer {token})
|   POST  /api/auth/logout
|   GET   /api/auth/me
|
*/

// -----------------------------------------------------------------------
// Public: Auth
// -----------------------------------------------------------------------
Route::post('/auth/login', [AuthController::class, 'login']);

// -----------------------------------------------------------------------
// Public: Helpers
// -----------------------------------------------------------------------
Route::get('/brands', [AuthController::class, 'brands']);

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
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/auth/logout', [AuthController::class, 'logout']);
    

});
