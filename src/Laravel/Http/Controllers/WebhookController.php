<?php

namespace Innochannel\Laravel\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Event;
use Innochannel\Laravel\Events\BookingWebhookReceived;
use Innochannel\Laravel\Events\PropertyWebhookReceived;
use Innochannel\Laravel\Events\InventoryWebhookReceived;
use Innochannel\Laravel\Events\GeneralWebhookReceived;
use Exception;

class WebhookController extends Controller
{
    /**
     * Handle booking webhook.
     */
    public function handleBookingWebhook(Request $request): JsonResponse
    {
        try {
            $payload = $request->all();
            
            Log::info('Booking webhook received', [
                'event_type' => $payload['event_type'] ?? 'unknown',
                'booking_id' => $payload['data']['id'] ?? null,
                'timestamp' => now(),
            ]);

            // Fire event for application to handle
            Event::dispatch(new BookingWebhookReceived($payload, $request->headers->all()));

            return response()->json([
                'status' => 'success',
                'message' => 'Booking webhook processed successfully'
            ]);

        } catch (Exception $e) {
            Log::error('Failed to process booking webhook', [
                'error' => $e->getMessage(),
                'payload' => $request->all(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to process webhook'
            ], 500);
        }
    }

    /**
     * Handle property webhook.
     */
    public function handlePropertyWebhook(Request $request): JsonResponse
    {
        try {
            $payload = $request->all();
            
            Log::info('Property webhook received', [
                'event_type' => $payload['event_type'] ?? 'unknown',
                'property_id' => $payload['data']['id'] ?? null,
                'timestamp' => now(),
            ]);

            // Fire event for application to handle
            Event::dispatch(new PropertyWebhookReceived($payload, $request->headers->all()));

            return response()->json([
                'status' => 'success',
                'message' => 'Property webhook processed successfully'
            ]);

        } catch (Exception $e) {
            Log::error('Failed to process property webhook', [
                'error' => $e->getMessage(),
                'payload' => $request->all(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to process webhook'
            ], 500);
        }
    }

    /**
     * Handle inventory webhook.
     */
    public function handleInventoryWebhook(Request $request): JsonResponse
    {
        try {
            $payload = $request->all();
            
            Log::info('Inventory webhook received', [
                'event_type' => $payload['event_type'] ?? 'unknown',
                'property_id' => $payload['data']['property_id'] ?? null,
                'timestamp' => now(),
            ]);

            // Fire event for application to handle
            Event::dispatch(new InventoryWebhookReceived($payload, $request->headers->all()));

            return response()->json([
                'status' => 'success',
                'message' => 'Inventory webhook processed successfully'
            ]);

        } catch (Exception $e) {
            Log::error('Failed to process inventory webhook', [
                'error' => $e->getMessage(),
                'payload' => $request->all(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to process webhook'
            ], 500);
        }
    }

    /**
     * Handle general webhook.
     */
    public function handleGeneralWebhook(Request $request): JsonResponse
    {
        try {
            $payload = $request->all();
            
            Log::info('General webhook received', [
                'event_type' => $payload['event_type'] ?? 'unknown',
                'timestamp' => now(),
            ]);

            // Fire event for application to handle
            Event::dispatch(new GeneralWebhookReceived($payload, $request->headers->all()));

            return response()->json([
                'status' => 'success',
                'message' => 'General webhook processed successfully'
            ]);

        } catch (Exception $e) {
            Log::error('Failed to process general webhook', [
                'error' => $e->getMessage(),
                'payload' => $request->all(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to process webhook'
            ], 500);
        }
    }

    /**
     * Handle OAuth callback.
     */
    public function handleOAuthCallback(Request $request): JsonResponse
    {
        try {
            $code = $request->get('code');
            $state = $request->get('state');
            $error = $request->get('error');

            if ($error) {
                Log::error('OAuth callback error', [
                    'error' => $error,
                    'error_description' => $request->get('error_description'),
                ]);

                return response()->json([
                    'status' => 'error',
                    'message' => 'OAuth authorization failed',
                    'error' => $error
                ], 400);
            }

            Log::info('OAuth callback received', [
                'code' => $code ? 'present' : 'missing',
                'state' => $state,
            ]);

            // Here you would typically exchange the code for an access token
            // and store it for future API calls

            return response()->json([
                'status' => 'success',
                'message' => 'OAuth callback processed successfully'
            ]);

        } catch (Exception $e) {
            Log::error('Failed to process OAuth callback', [
                'error' => $e->getMessage(),
                'query' => $request->query(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to process OAuth callback'
            ], 500);
        }
    }

    /**
     * Handle PMS callback.
     */
    public function handlePmsCallback(Request $request): JsonResponse
    {
        try {
            $payload = $request->all();
            
            Log::info('PMS callback received', [
                'pms_system' => $payload['pms_system'] ?? 'unknown',
                'callback_type' => $payload['callback_type'] ?? 'unknown',
                'timestamp' => now(),
            ]);

            // Process PMS-specific callback logic here

            return response()->json([
                'status' => 'success',
                'message' => 'PMS callback processed successfully'
            ]);

        } catch (Exception $e) {
            Log::error('Failed to process PMS callback', [
                'error' => $e->getMessage(),
                'payload' => $request->all(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to process PMS callback'
            ], 500);
        }
    }

    /**
     * Health check endpoint.
     */
    public function healthCheck(): JsonResponse
    {
        return response()->json([
            'status' => 'healthy',
            'service' => 'Innochannel Laravel Package',
            'version' => '1.0.0',
            'timestamp' => now()->toISOString(),
        ]);
    }
}