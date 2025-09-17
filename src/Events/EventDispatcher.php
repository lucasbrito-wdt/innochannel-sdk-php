<?php

declare(strict_types=1);

namespace Innochannel\Sdk\Events;

/**
 * Implementação concreta do dispatcher de eventos
 * 
 * @package Innochannel\Sdk\Events
 * @author Innochannel SDK Team
 * @version 1.0.0
 */
class EventDispatcher implements EventDispatcherInterface
{
    /**
     * @var array<string, array<array{listener: callable, priority: int}>>
     */
    private array $listeners = [];
    
    /**
     * @var array<string, array<callable>>
     */
    private array $sortedListeners = [];
    
    /**
     * {@inheritDoc}
     */
    public function dispatch(EventInterface $event): bool
    {
        $eventName = $event->getName();
        $listeners = $this->getListenersForEvent($eventName);
        
        if (empty($listeners)) {
            return true;
        }
        
        foreach ($listeners as $listener) {
            try {
                $result = $listener($event);
                
                // Se o listener retornar false, para a propagação
                if ($result === false) {
                    return false;
                }
            } catch (\Throwable $e) {
                // Log do erro mas continua executando outros listeners
                error_log("Erro ao executar listener para evento {$eventName}: " . $e->getMessage());
            }
        }
        
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function addListener(string $eventName, callable $listener, int $priority = 0): void
    {
        $this->listeners[$eventName][] = [
            'listener' => $listener,
            'priority' => $priority,
        ];
        
        // Limpa o cache de listeners ordenados
        unset($this->sortedListeners[$eventName]);
    }

    /**
     * {@inheritDoc}
     */
    public function removeListener(string $eventName, callable $listener): void
    {
        if (!isset($this->listeners[$eventName])) {
            return;
        }
        
        foreach ($this->listeners[$eventName] as $key => $listenerData) {
            if ($listenerData['listener'] === $listener) {
                unset($this->listeners[$eventName][$key]);
                unset($this->sortedListeners[$eventName]);
                break;
            }
        }
    }
    
    /**
     * Retorna os listeners para um evento específico, ordenados por prioridade
     * 
     * @param string $eventName
     * @return array<callable>
     */
    private function getListenersForEvent(string $eventName): array
    {
        if (!isset($this->listeners[$eventName])) {
            return [];
        }
        
        if (!isset($this->sortedListeners[$eventName])) {
            $this->sortListeners($eventName);
        }
        
        return $this->sortedListeners[$eventName];
    }
    
    /**
     * Ordena os listeners por prioridade (maior prioridade primeiro)
     * 
     * @param string $eventName
     */
    private function sortListeners(string $eventName): void
    {
        $listeners = $this->listeners[$eventName];
        
        // Ordena por prioridade (decrescente)
        usort($listeners, function ($a, $b) {
            return $b['priority'] <=> $a['priority'];
        });
        
        // Extrai apenas os callables
        $this->sortedListeners[$eventName] = array_map(
            fn($listenerData) => $listenerData['listener'],
            $listeners
        );
    }
}