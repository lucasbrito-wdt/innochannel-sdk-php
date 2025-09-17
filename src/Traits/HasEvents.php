<?php

declare(strict_types=1);

namespace Innochannel\Sdk\Traits;

use Innochannel\Sdk\Events\EventInterface;
use Innochannel\Sdk\Events\EventManager;

/**
 * Trait HasEvents
 * 
 * Adiciona funcionalidades de eventos aos modelos
 * 
 * @package Innochannel\Sdk\Traits
 * @author Innochannel SDK
 * @version 1.0.0
 */
trait HasEvents
{
    /**
     * Dados originais para comparação
     * 
     * @var array<string, mixed>
     */
    protected array $originalAttributes = [];
    
    /**
     * Se eventos estão habilitados para esta instância
     * 
     * @var bool
     */
    protected bool $eventsEnabled = true;
    
    /**
     * Inicializar sistema de eventos
     * 
     * @return void
     */
    protected function initializeEvents(): void
    {
        $this->syncOriginalAttributes();
    }
    
    /**
     * Disparar evento
     * 
     * @param EventInterface $event
     * @return void
     */
    protected function fireEvent(EventInterface $event): void
    {
        if ($this->eventsEnabled) {
            EventManager::getInstance()->fire($event);
        }
    }
    
    /**
     * Habilitar eventos para esta instância
     * 
     * @return static
     */
    public function enableEvents(): static
    {
        $this->eventsEnabled = true;
        return $this;
    }
    
    /**
     * Desabilitar eventos para esta instância
     * 
     * @return static
     */
    public function disableEvents(): static
    {
        $this->eventsEnabled = false;
        return $this;
    }
    
    /**
     * Verificar se eventos estão habilitados
     * 
     * @return bool
     */
    public function eventsAreEnabled(): bool
    {
        return $this->eventsEnabled;
    }
    
    /**
     * Sincronizar atributos originais
     * 
     * @return void
     */
    protected function syncOriginalAttributes(): void
    {
        $this->originalAttributes = $this->toArray();
    }
    
    /**
     * Obter atributos originais
     * 
     * @return array<string, mixed>
     */
    public function getOriginalAttributes(): array
    {
        return $this->originalAttributes;
    }
    
    /**
     * Verificar se houve mudanças
     * 
     * @return bool
     */
    public function isDirty(): bool
    {
        return $this->originalAttributes !== $this->toArray();
    }
    
    /**
     * Obter mudanças realizadas
     * 
     * @return array<string, array{old: mixed, new: mixed}>
     */
    public function getChanges(): array
    {
        $current = $this->toArray();
        $changes = [];
        
        foreach ($current as $key => $value) {
            $oldValue = $this->originalAttributes[$key] ?? null;
            if ($oldValue !== $value) {
                $changes[$key] = [
                    'old' => $oldValue,
                    'new' => $value
                ];
            }
        }
        
        return $changes;
    }
    
    /**
     * Executar operação sem disparar eventos
     * 
     * @param callable $callback
     * @return mixed
     */
    public function withoutEvents(callable $callback): mixed
    {
        $wasEnabled = $this->eventsEnabled;
        $this->disableEvents();
        
        try {
            return $callback();
        } finally {
            if ($wasEnabled) {
                $this->enableEvents();
            }
        }
    }
    
    /**
     * Método abstrato que deve ser implementado pelos modelos
     * 
     * @return array<string, mixed>
     */
    abstract public function toArray(): array;
}