<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Innochannel API Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration options for the Innochannel SDK.
    | You can configure API credentials, endpoints, and various options here.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | API Credentials
    |--------------------------------------------------------------------------
    |
    | Your Innochannel API credentials. You can find these in your
    | Innochannel dashboard under API settings.
    |
    */
    'api_key' => env('INNOCHANNEL_API_KEY'),
    'api_secret' => env('INNOCHANNEL_API_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | API Endpoints
    |--------------------------------------------------------------------------
    |
    | The base URL for the Innochannel API. You can override this for
    | testing or if you're using a different environment.
    |
    */
    'base_url' => env('INNOCHANNEL_BASE_URL', 'https://api.innochannel.com'),
    'webhook_url' => env('INNOCHANNEL_WEBHOOK_URL'),

    /*
    |--------------------------------------------------------------------------
    | Request Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration options for HTTP requests to the Innochannel API.
    |
    */
    'timeout' => env('INNOCHANNEL_TIMEOUT', 30),
    'retry_attempts' => env('INNOCHANNEL_RETRY_ATTEMPTS', 3),
    'retry_delay' => env('INNOCHANNEL_RETRY_DELAY', 1000), // milliseconds

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Configure how the SDK logs API requests and responses.
    |
    */
    'logging' => [
        'enabled' => env('INNOCHANNEL_LOGGING_ENABLED', true),
        'channel' => env('INNOCHANNEL_LOG_CHANNEL', 'daily'),
        'level' => env('INNOCHANNEL_LOG_LEVEL', 'info'),
        'log_requests' => env('INNOCHANNEL_LOG_REQUESTS', true),
        'log_responses' => env('INNOCHANNEL_LOG_RESPONSES', false),
        'log_errors' => env('INNOCHANNEL_LOG_ERRORS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configure caching for API responses to improve performance.
    |
    */
    'cache' => [
        'enabled' => env('INNOCHANNEL_CACHE_ENABLED', true),
        'store' => env('INNOCHANNEL_CACHE_STORE', 'redis'),
        'ttl' => env('INNOCHANNEL_CACHE_TTL', 3600), // seconds
        'prefix' => env('INNOCHANNEL_CACHE_PREFIX', 'innochannel'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhook Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for handling webhooks from Innochannel.
    |
    */
    'webhooks' => [
        'enabled' => env('INNOCHANNEL_WEBHOOKS_ENABLED', true),
        'secret' => env('INNOCHANNEL_WEBHOOK_SECRET'),
        'verify_signature' => env('INNOCHANNEL_WEBHOOK_VERIFY_SIGNATURE', true),
        'routes' => [
            'prefix' => 'innochannel/webhooks',
            'middleware' => ['api', 'innochannel.auth'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Configure rate limiting for API requests.
    |
    */
    'rate_limiting' => [
        'enabled' => env('INNOCHANNEL_RATE_LIMITING_ENABLED', true),
        'requests_per_minute' => env('INNOCHANNEL_RATE_LIMIT_RPM', 60),
        'burst_limit' => env('INNOCHANNEL_RATE_LIMIT_BURST', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Service Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration options for individual services.
    |
    */
    'services' => [
        'reservation' => [
            'auto_sync' => env('INNOCHANNEL_RESERVATION_AUTO_SYNC', true),
            'sync_direction' => env('INNOCHANNEL_RESERVATION_SYNC_DIRECTION', 'both'), // push, pull, both
            'validation_strict' => env('INNOCHANNEL_RESERVATION_VALIDATION_STRICT', true),
        ],
        'property' => [
            'auto_sync' => env('INNOCHANNEL_PROPERTY_AUTO_SYNC', true),
            'cache_duration' => env('INNOCHANNEL_PROPERTY_CACHE_DURATION', 7200), // seconds
        ],
        'inventory' => [
            'auto_sync' => env('INNOCHANNEL_INVENTORY_AUTO_SYNC', true),
            'sync_interval' => env('INNOCHANNEL_INVENTORY_SYNC_INTERVAL', 300), // seconds
            'batch_size' => env('INNOCHANNEL_INVENTORY_BATCH_SIZE', 100),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Events Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the event system
    |
    */
    'events' => [
        'enabled' => env('INNOCHANNEL_EVENTS_ENABLED', true),
        'async' => env('INNOCHANNEL_EVENTS_ASYNC', true),
        'queue' => env('INNOCHANNEL_EVENTS_QUEUE', 'default'),

        // Model Events Configuration
        'model_events' => [
            'reservation' => [
                'created' => env('INNOCHANNEL_RESERVATION_CREATED_ENABLED', true),
                'updated' => env('INNOCHANNEL_RESERVATION_UPDATED_ENABLED', true),
                'cancelled' => env('INNOCHANNEL_RESERVATION_CANCELLED_ENABLED', true),
                'confirmed' => env('INNOCHANNEL_RESERVATION_CONFIRMED_ENABLED', true),
                'deleted' => env('INNOCHANNEL_RESERVATION_DELETED_ENABLED', true),
            ],
            'property' => [
                'created' => env('INNOCHANNEL_PROPERTY_CREATED_ENABLED', true),
                'updated' => env('INNOCHANNEL_PROPERTY_UPDATED_ENABLED', true),
                'activated' => env('INNOCHANNEL_PROPERTY_ACTIVATED_ENABLED', true),
                'deactivated' => env('INNOCHANNEL_PROPERTY_DEACTIVATED_ENABLED', true),
                'deleted' => env('INNOCHANNEL_PROPERTY_DELETED_ENABLED', true),
                'pms_credentials_updated' => env('INNOCHANNEL_PROPERTY_PMS_CREDENTIALS_UPDATED_ENABLED', true),
            ],
            'rate_plan' => [
                'created' => env('INNOCHANNEL_RATE_PLAN_CREATED_ENABLED', true),
                'updated' => env('INNOCHANNEL_RATE_PLAN_UPDATED_ENABLED', true),
                'activated' => env('INNOCHANNEL_RATE_PLAN_ACTIVATED_ENABLED', true),
                'deactivated' => env('INNOCHANNEL_RATE_PLAN_DEACTIVATED_ENABLED', true),
                'deleted' => env('INNOCHANNEL_RATE_PLAN_DELETED_ENABLED', true),
                'restrictions_updated' => env('INNOCHANNEL_RATE_PLAN_RESTRICTIONS_UPDATED_ENABLED', true),
                'cancellation_policy_updated' => env('INNOCHANNEL_RATE_PLAN_CANCELLATION_POLICY_UPDATED_ENABLED', true),
            ],
            'room' => [
                'created' => env('INNOCHANNEL_ROOM_CREATED_ENABLED', true),
                'updated' => env('INNOCHANNEL_ROOM_UPDATED_ENABLED', true),
                'activated' => env('INNOCHANNEL_ROOM_ACTIVATED_ENABLED', true),
                'deactivated' => env('INNOCHANNEL_ROOM_DEACTIVATED_ENABLED', true),
                'deleted' => env('INNOCHANNEL_ROOM_DELETED_ENABLED', true),
                'amenities_updated' => env('INNOCHANNEL_ROOM_AMENITIES_UPDATED_ENABLED', true),
                'bed_types_updated' => env('INNOCHANNEL_ROOM_BED_TYPES_UPDATED_ENABLED', true),
                'capacity_updated' => env('INNOCHANNEL_ROOM_CAPACITY_UPDATED_ENABLED', true),
            ],
        ],

        // Event Logging Configuration
        'logging' => [
            'enabled' => env('INNOCHANNEL_EVENTS_LOGGING_ENABLED', false),
            'channel' => env('INNOCHANNEL_EVENTS_LOG_CHANNEL', 'default'),
            'level' => env('INNOCHANNEL_EVENTS_LOG_LEVEL', 'info'),
        ],

        // Performance Configuration
        'performance' => [
            'max_listeners_per_event' => env('INNOCHANNEL_MAX_LISTENERS_PER_EVENT', 0),
            'listener_timeout' => env('INNOCHANNEL_LISTENER_TIMEOUT', 0),
            'stop_on_failure' => env('INNOCHANNEL_STOP_ON_FAILURE', false),
        ],

        // Debug Configuration
        'debug' => [
            'enabled' => env('INNOCHANNEL_EVENTS_DEBUG', false),
            'collect_stats' => env('INNOCHANNEL_EVENTS_COLLECT_STATS', false),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Configuration
    |--------------------------------------------------------------------------
    |
    | Configure database tables and connections for the SDK.
    |
    */
    'database' => [
        'connection' => env('INNOCHANNEL_DB_CONNECTION', 'default'),
        'tables' => [
            'logs' => 'innochannel_logs',
            'webhooks' => 'innochannel_webhooks',
            'cache' => 'innochannel_cache',
            'sync_status' => 'innochannel_sync_status',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Development Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration options for development and testing.
    |
    */
    'development' => [
        'debug' => env('INNOCHANNEL_DEBUG', false),
        'mock_responses' => env('INNOCHANNEL_MOCK_RESPONSES', false),
        'test_mode' => env('INNOCHANNEL_TEST_MODE', false),
    ],
];
