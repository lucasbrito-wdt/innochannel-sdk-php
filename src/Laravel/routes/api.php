<?php

use Illuminate\Support\Facades\Route;
use Innochannel\Laravel\Http\Controllers\PropertyController;

/*
|--------------------------------------------------------------------------
| Innochannel SDK API Routes
|--------------------------------------------------------------------------
|
| Here are the API routes for the Innochannel SDK. These routes are
| automatically loaded by the InnochannelServiceProvider when the
| package is installed in a Laravel application.
|
*/

// Health check route for the SDK
Route::get('/innochannel/health', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'Innochannel SDK is running',
        'version' => '1.0.0'
    ]);
})->name('innochannel.health');

// Property management routes with ValidationException handling
Route::prefix('innochannel')->group(function () {
    // Property routes
    Route::prefix('properties')->group(function () {
        Route::post('/', [PropertyController::class, 'store'])->name('innochannel.properties.store');
        Route::put('/{propertyId}', [PropertyController::class, 'update'])->name('innochannel.properties.update');
        
        // Room routes
        Route::post('/{propertyId}/rooms', [PropertyController::class, 'createRoom'])->name('innochannel.properties.rooms.store');
        Route::put('/{propertyId}/rooms/{roomId}', [PropertyController::class, 'updateRoom'])->name('innochannel.properties.rooms.update');
        
        // Rate plan routes
        Route::post('/{propertyId}/rooms/{roomId}/rate-plans', [PropertyController::class, 'createRatePlan'])->name('innochannel.properties.rooms.rate-plans.store');
    });
});