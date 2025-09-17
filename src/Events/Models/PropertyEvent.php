<?php

declare(strict_types=1);

namespace Innochannel\Sdk\Events\Models;

use Innochannel\Sdk\Events\AbstractEvent;
use Innochannel\Sdk\Models\Property;

/**
 * Evento base para eventos relacionados a propriedades
 * 
 * @package Innochannel\Sdk\Events\Models
 * @author Innochannel SDK Team
 * @version 1.0.0
 */
abstract class PropertyEvent extends AbstractEvent
{
    public function __construct(
        protected Property $property,
        array $additionalData = []
    ) {
        $data = array_merge([
            'property' => $property,
            'property_id' => $property->getId(),
            'property_name' => $property->getName(),
            'pms_type' => $property->getPmsType(),
            'is_active' => $property->isActive(),
        ], $additionalData);
        
        parent::__construct($data);
    }
    
    /**
     * Retorna a instância da propriedade
     */
    public function getProperty(): Property
    {
        return $this->property;
    }
}

/**
 * Evento disparado quando uma propriedade é criada
 */
class PropertyCreated extends PropertyEvent
{
    public function getEventName(): string
    {
        return 'property.created';
    }
}

/**
 * Evento disparado quando uma propriedade é atualizada
 */
class PropertyUpdated extends PropertyEvent
{
    public function __construct(
        Property $property,
        protected array $originalData = [],
        protected array $changedFields = []
    ) {
        parent::__construct($property, [
            'original_data' => $originalData,
            'changed_fields' => $changedFields,
        ]);
    }
    
    public function getEventName(): string
    {
        return 'property.updated';
    }
    
    /**
     * Retorna os dados originais antes da atualização
     * 
     * @return array<string, mixed>
     */
    public function getOriginalData(): array
    {
        return $this->originalData;
    }
    
    /**
     * Retorna os campos que foram alterados
     * 
     * @return array<string>
     */
    public function getChangedFields(): array
    {
        return $this->changedFields;
    }
}

/**
 * Evento disparado quando uma propriedade é ativada
 */
class PropertyActivated extends PropertyEvent
{
    public function getEventName(): string
    {
        return 'property.activated';
    }
}

/**
 * Evento disparado quando uma propriedade é desativada
 */
class PropertyDeactivated extends PropertyEvent
{
    public function getEventName(): string
    {
        return 'property.deactivated';
    }
}

/**
 * Evento disparado quando uma propriedade é deletada
 */
class PropertyDeleted extends PropertyEvent
{
    public function getEventName(): string
    {
        return 'property.deleted';
    }
}

/**
 * Evento disparado quando as credenciais PMS de uma propriedade são atualizadas
 */
class PropertyPmsCredentialsUpdated extends PropertyEvent
{
    public function __construct(
        Property $property,
        protected array $oldCredentials = []
    ) {
        parent::__construct($property, [
            'old_credentials' => $oldCredentials,
            'new_credentials' => $property->getPmsCredentials(),
        ]);
    }
    
    public function getEventName(): string
    {
        return 'property.pms_credentials_updated';
    }
    
    /**
     * Retorna as credenciais antigas
     * 
     * @return array<string, mixed>
     */
    public function getOldCredentials(): array
    {
        return $this->oldCredentials;
    }
}