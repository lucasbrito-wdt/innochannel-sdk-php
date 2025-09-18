<?php

namespace Innochannel\Laravel;

use Illuminate\Support\ServiceProvider;
use Innochannel\Sdk\Client;
use Innochannel\Sdk\Services\PropertyService;
use Innochannel\Sdk\Services\InventoryService;
use Innochannel\Sdk\Services\WebhookService;
use Innochannel\Laravel\Console\InstallCommand;
use Innochannel\Laravel\Console\SyncCommand;
use Innochannel\Laravel\Console\TestConnectionCommand;
use Innochannel\Laravel\Middleware\InnochannelAuth;
use Innochannel\Sdk\Services\ReservationService;

class InnochannelServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge configuration
        $configPath = __DIR__ . '/config/innochannel.php';
        if (file_exists($configPath)) {
            $this->mergeConfigFrom($configPath, 'innochannel');
        }

        // Register the main client
        $this->app->singleton(Client::class, function ($app) {
            $config = $app['config']['innochannel'];

            return new Client([
                'api_key' => $config['api_key'],
                'api_secret' => $config['api_secret'],
                'base_url' => $config['base_url'],
                'timeout' => $config['timeout'] ?? 30
            ]);
        });

        // Register services
        $this->app->singleton(ReservationService::class, function ($app) {
            return new ReservationService($app->make(Client::class));
        });

        $this->app->singleton(PropertyService::class, function ($app) {
            return new PropertyService($app->make(Client::class));
        });

        $this->app->singleton(InventoryService::class, function ($app) {
            return new InventoryService($app->make(Client::class));
        });

        $this->app->singleton(WebhookService::class, function ($app) {
            return new WebhookService($app->make(Client::class));
        });

        // Register aliases
        $this->app->alias(Client::class, 'innochannel');
        $this->app->alias(ReservationService::class, 'innochannel.reservation');
        $this->app->alias(PropertyService::class, 'innochannel.property');
        $this->app->alias(InventoryService::class, 'innochannel.inventory');
        $this->app->alias(WebhookService::class, 'innochannel.webhook');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish configuration files
        if (file_exists(__DIR__ . '/config/innochannel.php')) {
            $this->publishes([
                __DIR__ . '/config/innochannel.php' => config_path('innochannel.php'),
            ], 'innochannel-config');
        }

        if (file_exists(__DIR__ . '/config/innochannel-telescope.php')) {
            $this->publishes([
                __DIR__ . '/config/innochannel-telescope.php' => config_path('innochannel-telescope.php'),
            ], 'innochannel-telescope-config');
        }

        // Publish migrations
        $this->publishes([
            __DIR__ . '/database/migrations/' => database_path('migrations'),
        ], 'innochannel-migrations');

        // Publish views
        $this->publishes([
            __DIR__ . '/resources/views/' => resource_path('views/vendor/innochannel'),
        ], 'innochannel-views');

        // Publish language files
        $this->publishes([
            __DIR__ . '/resources/lang/' => resource_path('lang/vendor/innochannel'),
        ], 'innochannel-lang');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');

        // Load views
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'innochannel');

        // Load translations
        $this->loadTranslationsFrom(__DIR__ . '/resources/lang', 'innochannel');

        // Load routes
        if (file_exists(__DIR__ . '/routes/web.php')) {
            $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
        }
        if (file_exists(__DIR__ . '/routes/api.php')) {
            $this->loadRoutesFrom(__DIR__ . '/routes/api.php');
        }

        // Register middleware
        $this->app['router']->aliasMiddleware('innochannel.auth', InnochannelAuth::class);
        $this->app['router']->aliasMiddleware('innochannel.telescope', \Innochannel\Laravel\Middleware\InnochannelTelescopeMiddleware::class);

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
                SyncCommand::class,
                TestConnectionCommand::class,
            ]);
        }

        // Register event listeners
        $this->registerEventListeners();

        // Register Telescope integration if available
        $this->registerTelescopeIntegration();

        // Register exception handler for Innochannel exceptions
        $this->registerExceptionHandler();
    }

    /**
     * Register Telescope integration if available.
     */
    protected function registerTelescopeIntegration(): void
    {
        if (class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
            $this->app->register(InnochannelTelescopeServiceProvider::class);
        }
    }

    /**
     * Register exception handler for Innochannel SDK exceptions.
     */
    protected function registerExceptionHandler(): void
    {
        $this->app->singleton(\Illuminate\Contracts\Debug\ExceptionHandler::class, function ($app) {
            return new \Innochannel\Laravel\Exceptions\Handler($app);
        });
    }

    /**
     * Register event listeners.
     */
    protected function registerEventListeners(): void
    {
        // Register event listeners for Innochannel events
        $events = $this->app['events'];

        // Reservation events
        $events->listen('innochannel.reservation.created', function ($reservation) {
            \Log::info('Innochannel reservation created', ['reservation_id' => $reservation->id]);
        });

        $events->listen('innochannel.reservation.updated', function ($reservation) {
            \Log::info('Innochannel reservation updated', ['reservation_id' => $reservation->id]);
        });

        $events->listen('innochannel.reservation.cancelled', function ($reservation) {
            \Log::info('Innochannel reservation cancelled', ['reservation_id' => $reservation->id]);
        });

        // Property events
        $events->listen('innochannel.property.updated', function ($property) {
            \Log::info('Innochannel property updated', ['property_id' => $property->id]);
        });

        // Inventory events
        $events->listen('innochannel.inventory.updated', function ($inventory) {
            \Log::info('Innochannel inventory updated', ['property_id' => $inventory->property_id]);
        });
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            Client::class,
            ReservationService::class,
            PropertyService::class,
            InventoryService::class,
            WebhookService::class,
            'innochannel',
            'innochannel.reservation',
            'innochannel.property',
            'innochannel.inventory',
            'innochannel.webhook',
        ];
    }
}
