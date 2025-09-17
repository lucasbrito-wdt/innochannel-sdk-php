<?php

namespace Innochannel\Laravel;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Innochannel\Client;
use Innochannel\Services\BookingService;
use Innochannel\Services\PropertyService;
use Innochannel\Services\InventoryService;
use Innochannel\Services\WebhookService;
use Innochannel\Laravel\Console\Commands\InstallCommand;
use Innochannel\Laravel\Console\Commands\PublishCommand;
use Innochannel\Laravel\Console\Commands\StatusCommand;
use Innochannel\Laravel\Middleware\InnochannelAuth;

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

            return new Client(
                $config['api_key'],
                $config['base_url'],
                $config['timeout'] ?? 30
            );
        });

        // Register services
        $this->app->singleton(BookingService::class, function ($app) {
            return new BookingService($app->make(Client::class));
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
        $this->app->alias(BookingService::class, 'innochannel.booking');
        $this->app->alias(PropertyService::class, 'innochannel.property');
        $this->app->alias(InventoryService::class, 'innochannel.inventory');
        $this->app->alias(WebhookService::class, 'innochannel.webhook');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish configuration
        $this->publishes([
            __DIR__ . '/config/innochannel.php' => config_path('innochannel.php'),
        ], 'innochannel-config');

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

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
                PublishCommand::class,
                StatusCommand::class,
            ]);
        }

        // Register event listeners
        $this->registerEventListeners();
    }

    /**
     * Register event listeners.
     */
    protected function registerEventListeners(): void
    {
        // Register event listeners for Innochannel events
        $events = $this->app['events'];

        // Booking events
        $events->listen('innochannel.booking.created', function ($booking) {
            \Log::info('Innochannel booking created', ['booking_id' => $booking->id]);
        });

        $events->listen('innochannel.booking.updated', function ($booking) {
            \Log::info('Innochannel booking updated', ['booking_id' => $booking->id]);
        });

        $events->listen('innochannel.booking.cancelled', function ($booking) {
            \Log::info('Innochannel booking cancelled', ['booking_id' => $booking->id]);
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
            BookingService::class,
            PropertyService::class,
            InventoryService::class,
            WebhookService::class,
            'innochannel',
            'innochannel.booking',
            'innochannel.property',
            'innochannel.inventory',
            'innochannel.webhook',
        ];
    }
}
