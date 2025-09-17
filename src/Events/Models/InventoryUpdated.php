<?php

declare(strict_types=1);

namespace Innochannel\Sdk\Events\Models;

use Innochannel\Sdk\Events\AbstractEvent;

/**
 * Class InventoryUpdated
 * 
 * Evento disparado quando o inventário é atualizado
 * 
 * @package Innochannel\Sdk\Events\Models
 * @author Innochannel SDK
 * @version 1.0.0
 */
class InventoryUpdated extends AbstractEvent
{
    /**
     * Dados do inventário
     * 
     * @var array<string, mixed>
     */
    protected array $inventory;

    /**
     * Mudanças realizadas
     * 
     * @var array<string, array{old: mixed, new: mixed}>
     */
    protected array $changes;

    /**
     * Constructor
     * 
     * @param array<string, mixed> $inventory
     * @param array<string, array{old: mixed, new: mixed}> $changes
     */
    public function __construct(array $inventory, array $changes = [])
    {
        $this->inventory = $inventory;
        $this->changes = $changes;
        
        parent::__construct([
            'inventory' => $inventory,
            'changes' => $changes,
        ]);
    }

    /**
     * Obter dados do inventário
     * 
     * @return array<string, mixed>
     */
    public function getInventory(): array
    {
        return $this->inventory;
    }

    /**
     * Obter mudanças realizadas
     * 
     * @return array<string, array{old: mixed, new: mixed}>
     */
    public function getChanges(): array
    {
        return $this->changes;
    }

    /**
     * Obter nome do evento
     * 
     * @return string
     */
    public function getName(): string
    {
        return 'inventory.updated';
    }

    /**
     * Verificar se houve mudança em um campo específico
     * 
     * @param string $field
     * @return bool
     */
    public function hasChanged(string $field): bool
    {
        return isset($this->changes[$field]);
    }

    /**
     * Obter valor antigo de um campo
     * 
     * @param string $field
     * @return mixed
     */
    public function getOldValue(string $field): mixed
    {
        return $this->changes[$field]['old'] ?? null;
    }

    /**
     * Obter valor novo de um campo
     * 
     * @param string $field
     * @return mixed
     */
    public function getNewValue(string $field): mixed
    {
        return $this->changes[$field]['new'] ?? null;
    }
}