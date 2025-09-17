<?php

use Illuminate\Support\Facades\Route;
use Innochannel\Laravel\Http\Controllers\WebhookController;

/*
|--------------------------------------------------------------------------
| Innochannel Web Routes
|--------------------------------------------------------------------------
|
| Here are the web routes for Innochannel webhooks and callbacks.
| These routes are automatically loaded by the InnochannelServiceProvider.
|
*/

Route::prefix('innochannel')
    ->name('innochannel.')
    ->middleware(['web', 'innochannel.auth'])
    ->group(function () {

        // Webhook endpoints
        Route::post('webhooks/reservations', [WebhookController::class, 'handleReservationWebhook'])
            ->name('webhooks.reservations');

        Route::post('webhooks/property', [WebhookController::class, 'handlePropertyWebhook'])
            ->name('webhooks.property');

        Route::post('webhooks/inventory', [WebhookController::class, 'handleInventoryWebhook'])
            ->name('webhooks.inventory');

        Route::post('webhooks/general', [WebhookController::class, 'handleGeneralWebhook'])
            ->name('webhooks.general');

        // Callback endpoints for OAuth and other integrations
        Route::get('callback/oauth', [WebhookController::class, 'handleOAuthCallback'])
            ->name('callback.oauth');

        Route::get('callback/pms', [WebhookController::class, 'handlePmsCallback'])
            ->name('callback.pms');

        // Health check endpoint
        Route::get('health', [WebhookController::class, 'healthCheck'])
            ->name('health')
            ->withoutMiddleware('innochannel.auth');
    });
