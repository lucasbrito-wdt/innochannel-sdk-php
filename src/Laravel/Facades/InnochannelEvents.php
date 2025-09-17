<?php

declare(strict_types=1);

namespace Innochannel\Sdk\Laravel\Facades;

use Illuminate\Support\Facades\Facade;
use Innochannel\Sdk\Events\EventDispatcherInterface;
use Innochannel\Sdk\Events\EventInterface;

/**
 * Facade para o EventDispatcher do Innochannel
 * 
 * @package Innochannel\Sdk\Laravel\Facades
 * @author Innochannel SDK Team
 * @version 1.0.0
 * 
 * @method static void dispatch(EventInterface $event)
 * @method static void listen(string $eventName, callable $listener)
 * @method static void removeListener(string $eventName, callable $listener)
 * @method static array getListeners(string $eventName = null)
 * 
 * @see \Innochannel\Sdk\Events\EventDispatcherInterface
 */
class InnochannelEvents extends Facade
{
    /**
     * Retorna o nome do binding no container
     */
    protected static function getFacadeAccessor(): string
    {
        return EventDispatcherInterface::class;
    }

    /**
     * Registra um listener para múltiplos eventos
     * 
     * @param array<string> $events
     * @param callable $listener
     * @param int $priority
     */
    public static function listenToMany(array $events, callable $listener, int $priority = 0): void
    {
        foreach ($events as $event) {
            static::listen($event, $listener, $priority);
        }
    }

    /**
     * Registra um listener que será executado apenas uma vez
     * 
     * @param string $eventName
     * @param callable $listener
     * @param int $priority
     */
    public static function once(string $eventName, callable $listener, int $priority = 0): void
    {
        $onceListener = function ($event) use ($listener, $eventName) {
            $result = $listener($event);
            static::forget($eventName, $listener);
            return $result;
        };

        static::listen($eventName, $onceListener, $priority);
    }

    /**
     * Verifica se existe algum listener para um evento
     * 
     * @param string $eventName
     */
    public static function hasListeners(string $eventName): bool
    {
        $listeners = static::getListeners();
        return isset($listeners[$eventName]) && !empty($listeners[$eventName]);
    }

    /**
     * Conta o número de listeners para um evento
     * 
     * @param string $eventName
     */
    public static function countListeners(string $eventName): int
    {
        $listeners = static::getListeners();
        return isset($listeners[$eventName]) ? count($listeners[$eventName]) : 0;
    }

    /**
     * Executa uma operação sem disparar eventos
     * 
     * @param callable $callback
     * @return mixed
     */
    public static function withoutEvents(callable $callback): mixed
    {
        $originalDispatcher = static::getFacadeRoot();
        
        // Temporariamente substitui por um dispatcher vazio
        static::swap(new class implements EventDispatcherInterface {
            public function listen(string $eventName, callable $listener, int $priority = 0): void {}
            public function dispatch(EventInterface $event): bool { return true; }
            public function forget(string $eventName, callable $listener): void {}
            public function forgetAll(string $eventName): void {}
            public function getListeners(): array { return []; }
        });

        try {
            return $callback();
        } finally {
            static::swap($originalDispatcher);
        }
    }

    /**
     * Registra listeners condicionais
     * 
     * @param string $eventName
     * @param callable $condition
     * @param callable $listener
     * @param int $priority
     */
    public static function listenIf(string $eventName, callable $condition, callable $listener, int $priority = 0): void
    {
        $conditionalListener = function ($event) use ($condition, $listener) {
            if ($condition($event)) {
                return $listener($event);
            }
            return true;
        };

        static::listen($eventName, $conditionalListener, $priority);
    }

    /**
     * Registra um listener que filtra eventos baseado em critérios
     * 
     * @param string $eventName
     * @param array<string, mixed> $criteria
     * @param callable $listener
     * @param int $priority
     */
    public static function listenWhere(string $eventName, array $criteria, callable $listener, int $priority = 0): void
    {
        $filterListener = function ($event) use ($criteria, $listener) {
            if (!($event instanceof EventInterface)) {
                return true;
            }

            $eventData = $event->getEventData();
            
            foreach ($criteria as $key => $expectedValue) {
                if (!isset($eventData[$key]) || $eventData[$key] !== $expectedValue) {
                    return true; // Não atende aos critérios, pula
                }
            }

            return $listener($event);
        };

        static::listen($eventName, $filterListener, $priority);
    }
}