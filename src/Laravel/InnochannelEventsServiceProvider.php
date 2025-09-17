<?php

declare(strict_types=1);

namespace Innochannel\Sdk\Laravel;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Innochannel\Sdk\Events\EventDispatcher;
use Innochannel\Sdk\Events\EventDispatcherInterface;
use Innochannel\Sdk\Models\Booking;
use Innochannel\Sdk\Models\Property;
use Innochannel\Sdk\Models\RatePlan;
use Innochannel\Sdk\Models\Room;
use Innochannel\Sdk\Traits\HasEvents;

/**
 * Service Provider para eventos do Innochannel
 * 
 * @package Innochannel\Sdk\Laravel
 * @author Innochannel SDK Team
 * @version 1.0.0
 */
class InnochannelEventsServiceProvider extends ServiceProvider
{
    /**
     * Registra os serviços no container
     */
    public function register(): void
    {
        // Publica a configuração
        $this->mergeConfigFrom(
            __DIR__ . '/../../../../../../config/innochannel-events.php',
            'innochannel-events'
        );

        // Registra o EventDispatcher
        $this->app->singleton(EventDispatcherInterface::class, function (Application $app) {
            $dispatcherClass = config('innochannel-events.dispatcher', EventDispatcher::class);
            return new $dispatcherClass();
        });

        // Alias para facilitar o uso
        $this->app->alias(EventDispatcherInterface::class, 'innochannel.events');
    }

    /**
     * Inicializa os serviços
     */
    public function boot(): void
    {
        // Publica a configuração
        $this->publishes([
            __DIR__ . '/../../../../../../config/innochannel-events.php' => config_path('innochannel-events.php'),
        ], 'innochannel-events-config');

        // Configura o sistema de eventos se estiver habilitado
        if (config('innochannel-events.enabled', true)) {
            $this->setupEventSystem();
        }

        // Registra listeners automáticos
        $this->registerAutoListeners();

        // Configura logging se habilitado
        if (config('innochannel-events.logging.enabled', false)) {
            $this->setupEventLogging();
        }
    }

    /**
     * Configura o sistema de eventos nos modelos
     */
    private function setupEventSystem(): void
    {
        $dispatcher = $this->app->make(EventDispatcherInterface::class);

        // Configura o dispatcher nos modelos que usam HasEvents
        if (method_exists(Booking::class, 'setEventDispatcher')) {
            Booking::setEventDispatcher($dispatcher);
        }

        if (method_exists(Property::class, 'setEventDispatcher')) {
            Property::setEventDispatcher($dispatcher);
        }

        if (method_exists(RatePlan::class, 'setEventDispatcher')) {
            RatePlan::setEventDispatcher($dispatcher);
        }

        if (method_exists(Room::class, 'setEventDispatcher')) {
            Room::setEventDispatcher($dispatcher);
        }
    }

    /**
     * Registra listeners automáticos definidos na configuração
     */
    private function registerAutoListeners(): void
    {
        $dispatcher = $this->app->make(EventDispatcherInterface::class);
        $autoListeners = config('innochannel-events.auto_listeners', []);

        foreach ($autoListeners as $eventName => $listeners) {
            if (!is_array($listeners)) {
                $listeners = [$listeners];
            }

            foreach ($listeners as $listenerClass) {
                if (is_string($listenerClass) && class_exists($listenerClass)) {
                    $listener = $this->app->make($listenerClass);
                    
                    if (is_callable($listener)) {
                        $dispatcher->listen($eventName, $listener);
                    } elseif (method_exists($listener, 'handle')) {
                        $dispatcher->listen($eventName, [$listener, 'handle']);
                    }
                }
            }
        }
    }

    /**
     * Configura o logging de eventos
     */
    private function setupEventLogging(): void
    {
        $dispatcher = $this->app->make(EventDispatcherInterface::class);
        $logChannel = config('innochannel-events.logging.channel', 'default');
        $logLevel = config('innochannel-events.logging.level', 'info');

        // Adiciona um listener global para logging
        $dispatcher->listen('*', function ($event) use ($logChannel, $logLevel) {
            if ($event instanceof \Innochannel\Sdk\Events\EventInterface) {
                $logger = \Illuminate\Support\Facades\Log::channel($logChannel);
                
                $logger->log($logLevel, 'Innochannel Event Dispatched', [
                    'event_name' => $event->getName(),
                    'timestamp' => $event->getTimestamp()->format('c'),
                    'data' => $event->getData(),
                ]);
            }
        });
    }

    /**
     * Retorna os serviços fornecidos por este provider
     * 
     * @return array<string>
     */
    public function provides(): array
    {
        return [
            EventDispatcherInterface::class,
            'innochannel.events',
        ];
    }
}