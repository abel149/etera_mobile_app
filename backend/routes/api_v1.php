<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BillingController;
use App\Http\Controllers\Api\BusinessOwnerController;
use App\Http\Controllers\Api\CreateProfoermaController;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\GarageController;
use App\Http\Controllers\Api\InsuranceController;
use App\Http\Controllers\Api\ShopController;
use App\Http\Controllers\Api\MarketerController;
use App\Http\Controllers\Api\UserReviewController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\AdminAnalyticsController;
use App\Http\Controllers\Api\UserBalanceController;

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

    // FCM device token registration
    Route::post('/device-token', [AuthController::class, 'registerDeviceToken']);

    // Push notifications
    Route::get('/notifications',       [AuthController::class, 'notifications']);
    Route::put('/notifications/read',  [AuthController::class, 'markNotificationsRead']);

    // Temp file upload (requires auth)
    Route::post('/upload/temp',   [\App\Http\Controllers\File\TemporaryFileController::class, 'store']);
    Route::delete('/upload/temp', [\App\Http\Controllers\File\TemporaryFileController::class, 'destroy']);

});

// -----------------------------------------------------------------------
// Protected: Others routes  (role: others)
// All prefixed /api/v1/others/...
// -----------------------------------------------------------------------
Route::middleware(['auth:sanctum', 'role:others'])->prefix('others')->group(function () {

    // Dashboard & Proformas
    Route::get('/dashboard',                          [CreateProfoermaController::class, 'dashboard']);
    Route::post('/create-file',                       [CreateProfoermaController::class, 'store']);
    Route::get('/proformas',                          [CreateProfoermaController::class, 'index']);
    Route::get('/proformas/{id}',                     [CreateProfoermaController::class, 'show']);
    Route::post('/proformas/{id}/request-close',      [CreateProfoermaController::class, 'requestClose']);

});

// -----------------------------------------------------------------------
// Protected: Business Owner routes  (role: business_owner)
// All prefixed /api/v1/business-owner/...
// -----------------------------------------------------------------------
Route::middleware(['auth:sanctum', 'role:business_owner,employee'])->prefix('business-owner')->group(function () {

    // Dashboard
    Route::get('/dashboard',                          [BusinessOwnerController::class, 'dashboard']);

    // Proformas
    Route::post('/create-file',                       [BusinessOwnerController::class, 'createProforma']);
    Route::get('/proformas',                          [BusinessOwnerController::class, 'index']);
    Route::get('/proformas/{id}',                     [BusinessOwnerController::class, 'show']);
    Route::post('/proformas/{id}/request-close',      [BusinessOwnerController::class, 'requestClose']);

    // Employee management
    Route::get('/employees',                          [BusinessOwnerController::class, 'listEmployees']);
    Route::post('/employees',                         [BusinessOwnerController::class, 'createEmployee']);
    Route::delete('/employees/{id}',                  [BusinessOwnerController::class, 'deleteEmployee']);

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
Route::middleware(['auth:sanctum', 'role:garage,employee'])->prefix('garage')->group(function () {

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
    Route::get('/balance', [UserBalanceController::class, 'index']);
    
    // Employee management
    Route::get('/employees',                          [GarageController::class, 'listEmployees']);
    Route::post('/employees',                         [GarageController::class, 'createEmployee']);
    Route::delete('/employees/{id}',                  [GarageController::class, 'deleteEmployee']);

    // Billing
    Route::get('/billing',                            [BillingController::class, 'overview']);
    Route::put('/billing/plan',                       [BillingController::class, 'updatePlan']);
    Route::get('/billing/statements',                 [BillingController::class, 'statements']);
    Route::get('/billing/statements/{sku}',           [BillingController::class, 'statementDetail']);

});

Route::middleware(['auth:sanctum', 'role:insurance'])->prefix('insurance')->group(function () {
    Route::get('/dashboard',                         [InsuranceController::class, 'dashboard']);
    Route::get('/proformas',                         [InsuranceController::class, 'index']);
    Route::post('/create-file',                      [InsuranceController::class, 'store']);
    Route::get('/proformas/{id}',                    [InsuranceController::class, 'show']);
    Route::post('/proformas/{id}/request-close',     [InsuranceController::class, 'requestClose']);
    Route::get('/received-proformas',                [InsuranceController::class, 'receivedProformas']);
    Route::get('/balance',                           [InsuranceController::class, 'balance']);
    Route::get('/partners',                          [InsuranceController::class, 'listPartners']);
    Route::post('/partners',                         [InsuranceController::class, 'addPartner']);
    Route::delete('/partners/{id}',                  [InsuranceController::class, 'removePartner']);
    
    // Billing
    Route::get('/billing',                            [BillingController::class, 'overview']);
    Route::put('/billing/plan',                       [BillingController::class, 'updatePlan']);
    Route::get('/billing/statements',                 [BillingController::class, 'statements']);
    Route::get('/billing/statements/{sku}',           [BillingController::class, 'statementDetail']);
    
    // Employee management
    Route::get('/employees',                          [InsuranceController::class, 'listEmployees']);
    Route::post('/employees',                         [InsuranceController::class, 'createEmployee']);
    Route::delete('/employees/{id}',                  [InsuranceController::class, 'deleteEmployee']);

});

// -----------------------------------------------------------------------
// Protected: Spare Part Shop routes  (role: shop + their employees)
// All prefixed /api/v1/shop/...
// -----------------------------------------------------------------------
Route::middleware(['auth:sanctum', 'role:shop,employee'])->prefix('shop')->group(function () {

    // Dashboard & Inbox
    Route::get('/dashboard',                          [ShopController::class, 'dashboard']);
    Route::get('/inbox',                              [ShopController::class, 'inbox']);

    // Proforma viewing & applying (shop bids on proformas)
    Route::get('/proformas/{id}',                     [ShopController::class, 'proformaDetail']);
    Route::post('/proformas/{id}/apply',              [ShopController::class, 'applyProforma']);

    // My submitted applications
    Route::get('/my-applications',                    [ShopController::class, 'myApplications']);

    // Balance & Withdrawals
    Route::get('/balance', [UserBalanceController::class, 'index']);

    // Employee management
    Route::get('/employees',                          [ShopController::class, 'listEmployees']);
    Route::post('/employees',                         [ShopController::class, 'createEmployee']);
    Route::delete('/employees/{id}',                  [ShopController::class, 'deleteEmployee']);

   
});

Route::middleware(['auth:sanctum', 'role:superadmin'])->prefix('admin')->group(function () {
   Route::get('/dashboard', [AdminController::class, 'dashboard']);
   
   //user managment
   Route::get('/user-approval', [AdminController::class, 'userApprovals']);
   Route::put('/user-approval/{id}', [AdminController::class, 'approveUser']);
   Route::delete('/user-approval/{id}', [AdminController::class, 'revokeUser']);
   Route::get('/user/{id}', [AdminController::class, 'viewUser']);
   Route::delete('/user/{id}', [AdminController::class, 'deleteUser']);

   //others proforma
   Route::get('/others-proforma', [AdminController::class, 'othersProforma']);
   Route::get('/proforma-details/{id}', [AdminController::class, 'proformaDetails']);
   Route::Post('/float/{id}', [AdminController::class, 'float']);
   Route::post('/close/{id}', [AdminController::class, 'closeProforma']);
   Route::get('/proforma-status', [AdminController::class, 'proformaStatus']);

   //notifications
   Route::get('/notifications', [AdminController::class, 'notifications']);
   Route::put('/mark-as-read', [AdminController::class, 'markAsRead']);

   //admin management
   Route::get('/admins', [AdminController::class, 'admins']);
   Route::post('/admins', [AdminController::class, 'createAdmin']);
   Route::put('/admins/{id}', [AdminController::class, 'updateAdmin']);
   Route::delete('/admins/{id}', [AdminController::class, 'deleteAdmin']);

   //Insurance managment
   Route::get('/insurances', [AdminController::class, 'insurances']);
   Route::post('/add-insurance', [InsuranceController::class,'store']);
   Route::put('/edit-insurance/{id}', [InsuranceController::class,'update']);
   Route::delete('/delete-insurance/{id}', [InsuranceController::class,'destroy']);

   //shop managment
   Route::get('/spare-parts', [AdminController::class, 'spareparts']);
   Route::post('/add-shop', [ShopController::class, 'createShop']);
   Route::put('/edit-shop/{id}', [ShopController::class, 'updateShop']);
   Route::get('/edit-shop/{id}', [ShopController::class, 'editShop']);
   Route::delete('/delete-shop/{id}', [ShopController::class, 'destroyShop']);
    
   //operator managment 
   Route::get('/operators', [AdminController::class, 'listOperators']);
   Route::post('/assign-manager/{opretor}', [AdminController::class , 'assignOperatorToManager']);
   Route::post('/set-quota/{opretor}', [AdminController::class , 'setOperatorQuota']);
   Route::post('/set-commission/{opretor}', [AdminController::class , 'setOperatorCommission']);
   Route::get('/commissions', [AdminController::class, 'viewAllCommissions']);

   //garage managment
    Route::get('/garages', [AdminController::class, 'garages']);
    Route::post('/add-shop', [GarageController::class, 'createGarage']);
    Route::put('/edit-shop/{id}', [GarageController::class, 'updateGarage']);
    Route::get('/edit-shop/{id}', [GarageController::class, 'editgarage']);
    Route::delete('/delete-shop/{id}', [GarageController::class, 'destroygarage']);
    
   //marketers managment
    Route::get('/marketers', [AdminController::class, 'marketers']);
    Route::post('/add-shop', [MarketerController::class, 'createMarketer']);
    Route::put('/edit-shop/{id}', [MarketerController::class, 'updateMarketer']);
    Route::get('/edit-shop/{id}', [MarketerController::class, 'editMarketer']);
    Route::delete('/delete-shop/{id}', [MarketerController::class, 'destroyMarketer']);
    
    // view Rating
    Route::get('/ratings', [UserReviewController::class, 'index']);
   

    //Brands view and create
    Route::get('/brands', function () {
        $brands = \App\Models\Brand::orderBy('name', 'asc')->get();  // Order by 'name' in ascending order

        return response()->json([
        'success' => true, 
            'data'=>[
                'brands' => $brands,
            ]
        ]);
    });
    Route::post('/brands', function (Request $request) {
        $request->validate([
                'name' => 'required|unique:brands,name',
            ]);

            $brands = \App\Models\Brand::create([
                'name' => $request->name,
            ]);

            $brands->save();ls\Brand::orderBy('name', 'asc')->get();  // Order by 'name' in ascending order

        return response()->json([
            'success' => true, 
            'message'=> 'brand successfuly created'
        ]);
    });

    //transaction
    Route::get('/transactions', [TransactionController::class, 'index'])->name('admin.transactions.index');
        

    //Analytics
    Route::get('/admin/analytics', [AdminAnalyticsController::class, 'index']);
    Route::post('/admin/analytics/mark-paid/{userId}', [AdminAnalyticsController::class, 'markPaid']);
    Route::post('/admin/analytics/receieve/{userId}', [AdminAnalyticsController::class, 'receivePayment']);
    Route::get('/admin/analytics/export/{type}', [AdminAnalyticsController::class, 'exportData']);
    

    //settings
     // Admin Settings (Cost + Commission)
    Route::get('/admin/settings', [AdminSettingsController::class, 'index']);
    // Cost
    Route::post('/admin/settings/costs', [AdminSettingsController::class, 'storeCost']);
    Route::delete('/admin/settings/costs/{cost}', [AdminSettingsController::class, 'destroyCost']);
    // Commission
    Route::post('/admin/settings/commissions', [AdminSettingsController::class, 'storeCommission']);
    // Email Toggle (AJAX)
    Route::post('/admin/settings/email-toggle', [AdminSettingsController::class, 'toggleEmail']);
});
