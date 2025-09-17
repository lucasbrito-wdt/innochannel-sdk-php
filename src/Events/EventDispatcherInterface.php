<?php

declare(strict_types=1);

namespace Innochannel\Sdk\Events;

/**
 * Interface EventDispatcherInterface
 * 
 * @package Innochannel\Sdk\Events
 * @author Innochannel SDK
 * @version 1.0.0
 */
interface EventDispatcherInterface
{
    /**
     * Disparar evento
     * 
     * @param EventInterface $event
     * @return bool
     */
    public function dispatch(EventInterface $event): bool;
    
    /**
     * Adicionar listener para evento
     * 
     * @param string $eventName
     * @param callable $listener
     * @param int $priority
     * @return void
     */
    public function addListener(string $eventName, callable $listener, int $priority = 0): void;
    
    /**
     * Remover listener de evento
     * 
     * @param string $eventName
     * @param callable $listener
     * @return void
     */
    public function removeListener(string $eventName, callable $listener): void;
}