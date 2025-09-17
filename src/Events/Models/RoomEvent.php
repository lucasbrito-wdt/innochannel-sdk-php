<?php

declare(strict_types=1);

namespace Innochannel\Sdk\Events\Models;

use Innochannel\Sdk\Events\AbstractEvent;
use Innochannel\Sdk\Models\Room;

/**
 * Evento base para eventos relacionados a quartos
 * 
 * @package Innochannel\Sdk\Events\Models
 * @author Innochannel SDK Team
 * @version 1.0.0
 */
abstract class RoomEvent extends AbstractEvent
{
    public function __construct(
        protected Room $room,
        array $additionalData = []
    ) {
        $data = array_merge([
            'room' => $room,
            'room_id' => $room->getId(),
            'property_id' => $room->getPropertyId(),
            'room_name' => $room->getName(),
            'room_type' => $room->getRoomType(),
            'max_occupancy' => $room->getMaxOccupancy(),
            'max_adults' => $room->getMaxAdults(),
            'max_children' => $room->getMaxChildren(),
            'is_active' => $room->isActive(),
        ], $additionalData);
        
        parent::__construct($data);
    }
    
    /**
     * Retorna a instância do quarto
     */
    public function getRoom(): Room
    {
        return $this->room;
    }
}

/**
 * Evento disparado quando um quarto é criado
 */
class RoomCreated extends RoomEvent
{
    public function getEventName(): string
    {
        return 'room.created';
    }
}

/**
 * Evento disparado quando um quarto é atualizado
 */
class RoomUpdated extends RoomEvent
{
    public function __construct(
        Room $room,
        protected array $originalData = [],
        protected array $changedFields = []
    ) {
        parent::__construct($room, [
            'original_data' => $originalData,
            'changed_fields' => $changedFields,
        ]);
    }
    
    public function getEventName(): string
    {
        return 'room.updated';
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
 * Evento disparado quando um quarto é ativado
 */
class RoomActivated extends RoomEvent
{
    public function getEventName(): string
    {
        return 'room.activated';
    }
}

/**
 * Evento disparado quando um quarto é desativado
 */
class RoomDeactivated extends RoomEvent
{
    public function getEventName(): string
    {
        return 'room.deactivated';
    }
}

/**
 * Evento disparado quando um quarto é deletado
 */
class RoomDeleted extends RoomEvent
{
    public function getEventName(): string
    {
        return 'room.deleted';
    }
}

/**
 * Evento disparado quando as comodidades de um quarto são atualizadas
 */
class RoomAmenitiesUpdated extends RoomEvent
{
    public function __construct(
        Room $room,
        protected array $oldAmenities = []
    ) {
        parent::__construct($room, [
            'old_amenities' => $oldAmenities,
            'new_amenities' => $room->getAmenities(),
        ]);
    }
    
    public function getEventName(): string
    {
        return 'room.amenities_updated';
    }
    
    /**
     * Retorna as comodidades antigas
     * 
     * @return array<string>
     */
    public function getOldAmenities(): array
    {
        return $this->oldAmenities;
    }
}

/**
 * Evento disparado quando os tipos de cama são atualizados
 */
class RoomBedTypesUpdated extends RoomEvent
{
    public function __construct(
        Room $room,
        protected array $oldBedTypes = []
    ) {
        parent::__construct($room, [
            'old_bed_types' => $oldBedTypes,
            'new_bed_types' => $room->getBedTypes(),
        ]);
    }
    
    public function getEventName(): string
    {
        return 'room.bed_types_updated';
    }
    
    /**
     * Retorna os tipos de cama antigos
     * 
     * @return array<string>
     */
    public function getOldBedTypes(): array
    {
        return $this->oldBedTypes;
    }
}

/**
 * Evento disparado quando a capacidade do quarto é alterada
 */
class RoomCapacityUpdated extends RoomEvent
{
    public function __construct(
        Room $room,
        protected int $oldMaxOccupancy,
        protected int $oldMaxAdults,
        protected int $oldMaxChildren
    ) {
        parent::__construct($room, [
            'old_max_occupancy' => $oldMaxOccupancy,
            'old_max_adults' => $oldMaxAdults,
            'old_max_children' => $oldMaxChildren,
            'new_max_occupancy' => $room->getMaxOccupancy(),
            'new_max_adults' => $room->getMaxAdults(),
            'new_max_children' => $room->getMaxChildren(),
        ]);
    }
    
    public function getEventName(): string
    {
        return 'room.capacity_updated';
    }
    
    /**
     * Retorna a ocupação máxima anterior
     */
    public function getOldMaxOccupancy(): int
    {
        return $this->oldMaxOccupancy;
    }
    
    /**
     * Retorna o número máximo de adultos anterior
     */
    public function getOldMaxAdults(): int
    {
        return $this->oldMaxAdults;
    }
    
    /**
     * Retorna o número máximo de crianças anterior
     */
    public function getOldMaxChildren(): int
    {
        return $this->oldMaxChildren;
    }
}