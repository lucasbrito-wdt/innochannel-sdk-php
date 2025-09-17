<?php

declare(strict_types=1);

namespace Innochannel\Sdk\Models;

use DateTime;
use DateTimeInterface;
use Innochannel\Sdk\Traits\HasEvents;
use Innochannel\Sdk\Events\Models\RoomCreatedEvent;
use Innochannel\Sdk\Events\Models\RoomUpdatedEvent;
use Innochannel\Sdk\Events\Models\RoomActivatedEvent;
use Innochannel\Sdk\Events\Models\RoomDeactivatedEvent;
use Innochannel\Sdk\Events\Models\RoomDeletedEvent;
use Innochannel\Sdk\Events\Models\RoomAmenitiesUpdatedEvent;
use Innochannel\Sdk\Events\Models\RoomBedTypesUpdatedEvent;
use Innochannel\Sdk\Events\Models\RoomCapacityChangedEvent;

/**
 * Modelo de Quarto
 * 
 * Representa um quarto no sistema Innochannel
 * 
 * @package Innochannel\Sdk\Models
 * @author Innochannel SDK
 * @version 1.0.0
 */
class Room
{
    use HasEvents;
    private int|string $id;
    private int|string $propertyId;
    private string $name;
    private string $roomType;
    private ?string $description;
    private int $maxOccupancy;
    private int $maxAdults;
    private int $maxChildren;
    private float $size;
    private ?string $sizeUnit;
    private array $amenities;
    private array $bedTypes;
    private ?string $viewType;
    private bool $isActive;
    private DateTimeInterface $createdAt;
    private DateTimeInterface $updatedAt;
    
    /**
     * Construtor da classe Room
     * 
     * @param array<string, mixed> $data Dados para inicializar o quarto
     */
    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? '';
        $this->propertyId = $data['property_id'] ?? '';
        $this->name = $data['name'] ?? '';
        $this->roomType = $data['room_type'] ?? '';
        $this->description = $data['description'] ?? null;
        $this->maxOccupancy = $data['max_occupancy'] ?? 1;
        $this->maxAdults = $data['max_adults'] ?? 1;
        $this->maxChildren = $data['max_children'] ?? 0;
        $this->size = $data['size'] ?? 0.0;
        $this->sizeUnit = $data['size_unit'] ?? null;
        $this->amenities = $data['amenities'] ?? [];
        $this->bedTypes = $data['bed_types'] ?? [];
        $this->viewType = $data['view_type'] ?? null;
        $this->isActive = $data['is_active'] ?? true;
        $this->createdAt = isset($data['created_at']) ? new DateTime($data['created_at']) : new DateTime();
        $this->updatedAt = isset($data['updated_at']) ? new DateTime($data['updated_at']) : new DateTime();
        
        $this->initializeEvents();
    }
    
    /**
     * Criar instância a partir de array
     */
    public static function fromArray(array $data): self
    {
        return new self($data);
    }
    
    /**
     * Converter para array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'property_id' => $this->propertyId,
            'name' => $this->name,
            'room_type' => $this->roomType,
            'description' => $this->description,
            'max_occupancy' => $this->maxOccupancy,
            'max_adults' => $this->maxAdults,
            'max_children' => $this->maxChildren,
            'size' => $this->size,
            'size_unit' => $this->sizeUnit,
            'amenities' => $this->amenities,
            'bed_types' => $this->bedTypes,
            'view_type' => $this->viewType,
            'is_active' => $this->isActive,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
    
    // Getters
    public function getId(): int|string
    {
        return $this->id;
    }
    
    public function getPropertyId(): int|string
    {
        return $this->propertyId;
    }
    
    public function getName(): string
    {
        return $this->name;
    }
    
    public function getRoomType(): string
    {
        return $this->roomType;
    }
    
    public function getDescription(): ?string
    {
        return $this->description;
    }
    
    public function getMaxOccupancy(): int
    {
        return $this->maxOccupancy;
    }
    
    public function getMaxAdults(): int
    {
        return $this->maxAdults;
    }
    
    public function getMaxChildren(): int
    {
        return $this->maxChildren;
    }
    
    public function getSize(): float
    {
        return $this->size;
    }
    
    public function getSizeUnit(): ?string
    {
        return $this->sizeUnit;
    }
    
    public function getAmenities(): array
    {
        return $this->amenities;
    }
    
    public function getBedTypes(): array
    {
        return $this->bedTypes;
    }
    
    public function getViewType(): ?string
    {
        return $this->viewType;
    }
    
    public function isActive(): bool
    {
        return $this->isActive;
    }
    
    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }
    
    public function getUpdatedAt(): DateTimeInterface
    {
        return $this->updatedAt;
    }
    
    // Setters
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }
    
    public function setRoomType(string $roomType): self
    {
        $this->roomType = $roomType;
        return $this;
    }
    
    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }
    
    public function setMaxOccupancy(int $maxOccupancy): self
    {
        $this->maxOccupancy = $maxOccupancy;
        return $this;
    }
    
    public function setMaxAdults(int $maxAdults): self
    {
        $this->maxAdults = $maxAdults;
        return $this;
    }
    
    public function setMaxChildren(int $maxChildren): self
    {
        $this->maxChildren = $maxChildren;
        return $this;
    }
    
    public function setSize(float $size): self
    {
        $this->size = $size;
        return $this;
    }
    
    public function setSizeUnit(?string $sizeUnit): self
    {
        $this->sizeUnit = $sizeUnit;
        return $this;
    }
    
    public function setAmenities(array $amenities): self
    {
        $this->amenities = $amenities;
        return $this;
    }
    
    public function setBedTypes(array $bedTypes): self
    {
        $this->bedTypes = $bedTypes;
        return $this;
    }
    
    public function setViewType(?string $viewType): self
    {
        $this->viewType = $viewType;
        return $this;
    }
    
    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;
        return $this;
    }
    
    /**
     * Verificar se tem amenidade específica
     */
    public function hasAmenity(string $amenity): bool
    {
        return in_array($amenity, $this->amenities);
    }
    
    /**
     * Adicionar amenidade
     */
    public function addAmenity(string $amenity): self
    {
        if (!$this->hasAmenity($amenity)) {
            $this->amenities[] = $amenity;
        }
        
        return $this;
    }
    
    /**
     * Remover amenidade
     */
    public function removeAmenity(string $amenity): self
    {
        $this->amenities = array_filter($this->amenities, fn($a) => $a !== $amenity);
        return $this;
    }
    
    /**
     * Verificar se tem tipo de cama específico
     */
    public function hasBedType(string $bedType): bool
    {
        return in_array($bedType, $this->bedTypes);
    }
    
    /**
     * Adicionar tipo de cama
     */
    public function addBedType(string $bedType): self
    {
        if (!$this->hasBedType($bedType)) {
            $this->bedTypes[] = $bedType;
        }
        
        return $this;
    }
    
    /**
     * Remover tipo de cama
     */
    public function removeBedType(string $bedType): self
    {
        $this->bedTypes = array_filter($this->bedTypes, fn($b) => $b !== $bedType);
        return $this;
    }
    
    /**
     * Obter tamanho formatado com unidade
     */
    public function getFormattedSize(): string
    {
        if ($this->size <= 0) {
            return 'N/A';
        }
        
        $unit = $this->sizeUnit ?? 'm²';
        return number_format($this->size, 1) . ' ' . $unit;
    }
    
    /**
     * Verificar se pode acomodar número específico de hóspedes
     */
    public function canAccommodate(int $adults, int $children = 0): bool
    {
        return $adults <= $this->maxAdults && 
               $children <= $this->maxChildren && 
               ($adults + $children) <= $this->maxOccupancy;
    }
}