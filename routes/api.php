<?php

use App\Http\Controllers\Api\FuneralApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// All API routes are protected by auth:sanctum and tenant middleware
Route::middleware(['auth:sanctum', 'tenant'])->prefix('api')->group(function () {

    // Timeline Wizard (Funeral Creation)
    Route::post('/funerals', [FuneralApiController::class, 'store']);
    Route::post('/funerals/drafts', [FuneralApiController::class, 'saveDraft']);
    Route::get('/funerals/drafts/{draft}', [FuneralApiController::class, 'getDraft']);

    // Memorial Table (Products)
    Route::get('/products', [\App\Http\Controllers\Api\MemorialTableApiController::class, 'listProducts']);

    // Family Cloud
    Route::post('/family/login', [\App\Http\Controllers\Api\FamilyCloudApiController::class, 'login']);
    Route::get('/family/dashboard', [\App\Http\Controllers\Api\FamilyCloudApiController::class, 'dashboard']);

    // Cemetery Registry
    Route::get('/cemeteries', [\App\Http\Controllers\Api\CemeteryRegistryApiController::class, 'search']);
    Route::get('/cemeteries/{id}/map', [\App\Http\Controllers\Api\CemeteryRegistryApiController::class, 'map']);
});
