<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Event System Configuration
    |--------------------------------------------------------------------------
    |
    | Configurações do sistema de eventos do Innochannel SDK
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Event Dispatcher
    |--------------------------------------------------------------------------
    |
    | Classe responsável por despachar eventos. Deve implementar
    | EventDispatcherInterface
    |
    */
    'dispatcher' => \Innochannel\Sdk\Events\EventDispatcher::class,

    /*
    |--------------------------------------------------------------------------
    | Auto Event Listeners
    |--------------------------------------------------------------------------
    |
    | Listeners que serão registrados automaticamente
    |
    */
    'auto_listeners' => [
        // Reservation Events
        \Innochannel\Sdk\Events\Models\ReservationCreated::class => [
            \Innochannel\Sdk\Laravel\Listeners\ReservationWebhookListener::class,
        ],
        \Innochannel\Sdk\Events\Models\ReservationUpdated::class => [
            \Innochannel\Sdk\Laravel\Listeners\ReservationWebhookListener::class,
        ],
        \Innochannel\Sdk\Events\Models\ReservationCancelled::class => [
            \Innochannel\Sdk\Laravel\Listeners\ReservationWebhookListener::class,
        ],
        \Innochannel\Sdk\Events\Models\ReservationConfirmed::class => [
            \Innochannel\Sdk\Laravel\Listeners\ReservationWebhookListener::class,
        ],
        \Innochannel\Sdk\Events\Models\ReservationDeleted::class => [
            \Innochannel\Sdk\Laravel\Listeners\ReservationWebhookListener::class,
        ],

        // Property Events
        \Innochannel\Sdk\Events\Models\PropertyCreated::class => [
            \Innochannel\Sdk\Laravel\Listeners\PropertyWebhookListener::class,
        ],
        \Innochannel\Sdk\Events\Models\PropertyUpdated::class => [
            \Innochannel\Sdk\Laravel\Listeners\PropertyWebhookListener::class,
        ],
        \Innochannel\Sdk\Events\Models\PropertyDeleted::class => [
            \Innochannel\Sdk\Laravel\Listeners\PropertyWebhookListener::class,
        ],

        // Room Events
        \Innochannel\Sdk\Events\Models\RoomCreated::class => [
            \Innochannel\Sdk\Laravel\Listeners\RoomWebhookListener::class,
        ],
        \Innochannel\Sdk\Events\Models\RoomUpdated::class => [
            \Innochannel\Sdk\Laravel\Listeners\RoomWebhookListener::class,
        ],
        \Innochannel\Sdk\Events\Models\RoomDeleted::class => [
            \Innochannel\Sdk\Laravel\Listeners\RoomWebhookListener::class,
        ],

        // Rate Plan Events
        \Innochannel\Sdk\Events\Models\RatePlanCreated::class => [
            \Innochannel\Sdk\Laravel\Listeners\RatePlanWebhookListener::class,
        ],
        \Innochannel\Sdk\Events\Models\RatePlanUpdated::class => [
            \Innochannel\Sdk\Laravel\Listeners\RatePlanWebhookListener::class,
        ],
        \Innochannel\Sdk\Events\Models\RatePlanDeleted::class => [
            \Innochannel\Sdk\Laravel\Listeners\RatePlanWebhookListener::class,
        ],

        // Inventory Events
        \Innochannel\Sdk\Events\Models\InventoryUpdated::class => [
            \Innochannel\Sdk\Laravel\Listeners\InventoryWebhookListener::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Event Logging
    |--------------------------------------------------------------------------
    |
    | Configurações de logging de eventos
    |
    */
    'logging' => [
        'enabled' => env('INNOCHANNEL_EVENTS_LOGGING', true),
        'channel' => env('INNOCHANNEL_EVENTS_LOG_CHANNEL', 'innochannel'),
        'level' => env('INNOCHANNEL_EVENTS_LOG_LEVEL', 'info'),
        'include_payload' => env('INNOCHANNEL_EVENTS_LOG_PAYLOAD', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Event Queue
    |--------------------------------------------------------------------------
    |
    | Configurações para processamento assíncrono de eventos
    |
    */
    'queue' => [
        'enabled' => env('INNOCHANNEL_EVENTS_QUEUE', false),
        'connection' => env('INNOCHANNEL_EVENTS_QUEUE_CONNECTION', 'default'),
        'queue_name' => env('INNOCHANNEL_EVENTS_QUEUE_NAME', 'innochannel-events'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Event Performance
    |--------------------------------------------------------------------------
    |
    | Configurações de performance para eventos
    |
    */
    'performance' => [
        'max_listeners_per_event' => 10,
        'timeout' => 30, // segundos
        'memory_limit' => '128M',
    ],

    /*
    |--------------------------------------------------------------------------
    | Event Debug
    |--------------------------------------------------------------------------
    |
    | Configurações de debug para desenvolvimento
    |
    */
    'debug' => [
        'enabled' => env('INNOCHANNEL_EVENTS_DEBUG', false),
        'trace_events' => env('INNOCHANNEL_EVENTS_TRACE', false),
        'log_performance' => env('INNOCHANNEL_EVENTS_LOG_PERFORMANCE', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Model Events Configuration
    |--------------------------------------------------------------------------
    |
    | Configurações específicas para eventos de modelos
    |
    */
    'models' => [
        'booking' => [
            'events_enabled' => true,
            'auto_sync' => true,
            'webhook_enabled' => true,
        ],
        'property' => [
            'events_enabled' => true,
            'auto_sync' => true,
            'webhook_enabled' => false,
        ],
        'room' => [
            'events_enabled' => true,
            'auto_sync' => true,
            'webhook_enabled' => false,
        ],
        'rate_plan' => [
            'events_enabled' => true,
            'auto_sync' => true,
            'webhook_enabled' => false,
        ],
    ],
];
