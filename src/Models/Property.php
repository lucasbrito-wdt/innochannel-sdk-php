<?php

declare(strict_types=1);

namespace Innochannel\Sdk\Models;

use DateTime;
use DateTimeInterface;
use Innochannel\Sdk\Traits\HasEvents;
use Innochannel\Sdk\Events\Models\PropertyCreatedEvent;
use Innochannel\Sdk\Events\Models\PropertyUpdatedEvent;
use Innochannel\Sdk\Events\Models\PropertyActivatedEvent;
use Innochannel\Sdk\Events\Models\PropertyDeactivatedEvent;
use Innochannel\Sdk\Events\Models\PropertyDeletedEvent;
use Innochannel\Sdk\Events\Models\PropertyPmsCredentialsUpdatedEvent;

/**
 * Modelo de Propriedade
 * 
 * Representa uma propriedade hoteleira no sistema Innochannel
 * 
 * @package Innochannel\Sdk\Models
 * @author Innochannel SDK
 * @version 1.0.0
 */
class Property
{
    use HasEvents;
    private int $id;
    private string $name;
    private ?string $description;
    private string $pmsType;
    private array $pmsCredentials;
    private ?string $address;
    private ?string $city;
    private ?string $state;
    private ?string $country;
    private ?string $postalCode;
    private ?string $phone;
    private ?string $email;
    private ?string $website;
    private array $amenities;
    private array $policies;
    private bool $isActive;
    private DateTimeInterface $createdAt;
    private DateTimeInterface $updatedAt;
    
    /**
     * Construtor da classe Property
     * 
     * @param array<string, mixed> $data Dados para inicializar a propriedade
     */
    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? 0;
        $this->name = $data['name'] ?? '';
        $this->description = $data['description'] ?? null;
        $this->pmsType = $data['pms_type'] ?? '';
        $this->pmsCredentials = $data['pms_credentials'] ?? [];
        $this->address = $data['address'] ?? null;
        $this->city = $data['city'] ?? null;
        $this->state = $data['state'] ?? null;
        $this->country = $data['country'] ?? null;
        $this->postalCode = $data['postal_code'] ?? null;
        $this->phone = $data['phone'] ?? null;
        $this->email = $data['email'] ?? null;
        $this->website = $data['website'] ?? null;
        $this->amenities = $data['amenities'] ?? [];
        $this->policies = $data['policies'] ?? [];
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
            'name' => $this->name,
            'description' => $this->description,
            'pms_type' => $this->pmsType,
            'pms_credentials' => $this->pmsCredentials,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'postal_code' => $this->postalCode,
            'phone' => $this->phone,
            'email' => $this->email,
            'website' => $this->website,
            'amenities' => $this->amenities,
            'policies' => $this->policies,
            'is_active' => $this->isActive,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
    
    // Getters
    public function getId(): int
    {
        return $this->id;
    }
    
    public function getName(): string
    {
        return $this->name;
    }
    
    public function getDescription(): ?string
    {
        return $this->description;
    }
    
    public function getPmsType(): string
    {
        return $this->pmsType;
    }
    
    public function getPmsCredentials(): array
    {
        return $this->pmsCredentials;
    }
    
    public function getAddress(): ?string
    {
        return $this->address;
    }
    
    public function getCity(): ?string
    {
        return $this->city;
    }
    
    public function getState(): ?string
    {
        return $this->state;
    }
    
    public function getCountry(): ?string
    {
        return $this->country;
    }
    
    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }
    
    public function getPhone(): ?string
    {
        return $this->phone;
    }
    
    public function getEmail(): ?string
    {
        return $this->email;
    }
    
    public function getWebsite(): ?string
    {
        return $this->website;
    }
    
    public function getAmenities(): array
    {
        return $this->amenities;
    }
    
    public function getPolicies(): array
    {
        return $this->policies;
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
    
    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }
    
    public function setPmsType(string $pmsType): self
    {
        $this->pmsType = $pmsType;
        return $this;
    }
    
    public function setPmsCredentials(array $pmsCredentials): self
    {
        $this->pmsCredentials = $pmsCredentials;
        return $this;
    }
    
    public function setAddress(?string $address): self
    {
        $this->address = $address;
        return $this;
    }
    
    public function setCity(?string $city): self
    {
        $this->city = $city;
        return $this;
    }
    
    public function setState(?string $state): self
    {
        $this->state = $state;
        return $this;
    }
    
    public function setCountry(?string $country): self
    {
        $this->country = $country;
        return $this;
    }
    
    public function setPostalCode(?string $postalCode): self
    {
        $this->postalCode = $postalCode;
        return $this;
    }
    
    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;
        return $this;
    }
    
    public function setEmail(?string $email): self
    {
        $this->email = $email;
        return $this;
    }
    
    public function setWebsite(?string $website): self
    {
        $this->website = $website;
        return $this;
    }
    
    public function setAmenities(array $amenities): self
    {
        $this->amenities = $amenities;
        return $this;
    }
    
    public function setPolicies(array $policies): self
    {
        $this->policies = $policies;
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
     * Obter política específica
     */
    public function getPolicy(string $key): mixed
    {
        return $this->policies[$key] ?? null;
    }
    
    /**
     * Definir política
     */
    public function setPolicy(string $key, mixed $value): self
    {
        $this->policies[$key] = $value;
        return $this;
    }
    
    /**
     * Obter endereço completo formatado
     */
    public function getFullAddress(): string
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->state,
            $this->postalCode,
            $this->country
        ]);
        
        return implode(', ', $parts);
    }
}