<?php

use Illuminate\Support\Facades\Route;

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

// SDK API routes can be added here as needed
// Example:
// Route::prefix('innochannel')->group(function () {
//     Route::get('/properties', [PropertyController::class, 'index']);
//     Route::get('/bookings', [ReservationController::class, 'index']);
// });