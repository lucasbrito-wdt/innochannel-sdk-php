<?php

declare(strict_types=1);

namespace Innochannel\Sdk\Events;

use Innochannel\Sdk\Events\EventDispatcherInterface;
use Innochannel\Sdk\Events\EventDispatcher;

/**
 * Gerenciador de Eventos
 * 
 * Classe singleton para gerenciar o sistema de eventos globalmente
 * 
 * @package Innochannel\Sdk\Events
 * @author Innochannel SDK
 * @version 1.0.0
 */
class EventManager
{
    private static ?EventManager $instance = null;
    private EventDispatcherInterface $dispatcher;
    private bool $enabled = true;
    
    /**
     * Construtor privado para singleton
     */
    private function __construct()
    {
        $this->dispatcher = new EventDispatcher();
    }
    
    /**
     * Obter instância singleton
     * 
     * @return EventManager
     */
    public static function getInstance(): EventManager
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    /**
     * Obter dispatcher de eventos
     * 
     * @return EventDispatcherInterface
     */
    public function getDispatcher(): EventDispatcherInterface
    {
        return $this->dispatcher;
    }
    
    /**
     * Definir dispatcher de eventos
     * 
     * @param EventDispatcherInterface $dispatcher
     * @return void
     */
    public function setDispatcher(EventDispatcherInterface $dispatcher): void
    {
        $this->dispatcher = $dispatcher;
    }
    
    /**
     * Verificar se eventos estão habilitados
     * 
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }
    
    /**
     * Habilitar eventos
     * 
     * @return void
     */
    public function enable(): void
    {
        $this->enabled = true;
    }
    
    /**
     * Desabilitar eventos
     * 
     * @return void
     */
    public function disable(): void
    {
        $this->enabled = false;
    }
    
    /**
     * Disparar evento se habilitado
     * 
     * @param EventInterface $event
     * @return void
     */
    public function fire(EventInterface $event): void
    {
        if ($this->enabled) {
            $this->dispatcher->dispatch($event);
        }
    }
    
    /**
     * Registrar listener
     * 
     * @param string $eventName
     * @param callable $listener
     * @param int $priority
     * @return void
     */
    public function addListener(string $eventName, callable $listener, int $priority = 0): void
    {
        $this->dispatcher->addListener($eventName, $listener, $priority);
    }
    
    /**
     * Remover listener
     * 
     * @param string $eventName
     * @param callable $listener
     * @return void
     */
    public function removeListener(string $eventName, callable $listener): void
    {
        $this->dispatcher->removeListener($eventName, $listener);
    }
    
    /**
     * Obter todos os listeners
     * 
     * @return array<string, array<callable>>
     */
    public function getListeners(): array
    {
        // Return empty array since we removed getListeners from interface
        return [];
    }
    
    /**
     * Limpar todos os listeners
     * 
     * @return void
     */
    public function clearListeners(): void
    {
        $this->dispatcher = new EventDispatcher();
    }
    
    /**
     * Executar operação sem eventos
     * 
     * @param callable $callback
     * @return mixed
     */
    public function withoutEvents(callable $callback): mixed
    {
        $wasEnabled = $this->enabled;
        $this->disable();
        
        try {
            return $callback();
        } finally {
            if ($wasEnabled) {
                $this->enable();
            }
        }
    }
}