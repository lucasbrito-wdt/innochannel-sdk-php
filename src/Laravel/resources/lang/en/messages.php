<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Innochannel SDK Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used by the Innochannel SDK for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
    */

    'booking' => [
        'created' => 'Booking created successfully',
        'updated' => 'Booking updated successfully',
        'cancelled' => 'Booking cancelled successfully',
        'not_found' => 'Booking not found',
        'invalid_data' => 'Invalid booking data provided',
    ],

    'property' => [
        'created' => 'Property created successfully',
        'updated' => 'Property updated successfully',
        'deleted' => 'Property deleted successfully',
        'not_found' => 'Property not found',
        'invalid_data' => 'Invalid property data provided',
    ],

    'inventory' => [
        'updated' => 'Inventory updated successfully',
        'not_found' => 'Inventory not found',
        'invalid_data' => 'Invalid inventory data provided',
    ],

    'webhook' => [
        'received' => 'Webhook received successfully',
        'processed' => 'Webhook processed successfully',
        'failed' => 'Webhook processing failed',
        'invalid_signature' => 'Invalid webhook signature',
    ],

    'api' => [
        'connection_failed' => 'Failed to connect to Innochannel API',
        'timeout' => 'Request timeout',
        'unauthorized' => 'Unauthorized access',
        'rate_limit_exceeded' => 'Rate limit exceeded',
        'server_error' => 'Server error occurred',
    ],

    'sync' => [
        'started' => 'Synchronization started',
        'completed' => 'Synchronization completed',
        'failed' => 'Synchronization failed',
        'in_progress' => 'Synchronization in progress',
    ],
];