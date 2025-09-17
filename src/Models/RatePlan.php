<?php

declare(strict_types=1);

namespace Innochannel\Sdk\Models;

use DateTime;
use DateTimeInterface;
use Innochannel\Sdk\Traits\HasEvents;
use Innochannel\Sdk\Events\Models\RatePlanCreatedEvent;
use Innochannel\Sdk\Events\Models\RatePlanUpdated;
use Innochannel\Sdk\Events\Models\RatePlanActivated;
use Innochannel\Sdk\Events\Models\RatePlanDeactivated;
use Innochannel\Sdk\Events\Models\RatePlanDeleted;
use Innochannel\Sdk\Events\Models\RatePlanRestrictionsUpdated;
use Innochannel\Sdk\Events\Models\RatePlanCancellationPolicyUpdated;

/**
 * Modelo de Plano de Tarifas
 * 
 * Representa um plano de tarifas no sistema Innochannel
 * 
 * @package Innochannel\Sdk\Models
 * @author Innochannel SDK
 * @version 1.0.0
 */
class RatePlan
{
    use HasEvents;
    private int|string $id;
    private int|string $propertyId;
    private string $name;
    private ?string $description;
    private string $currency;
    private string $rateType;
    private array $restrictions;
    private array $cancellationPolicy;
    private bool $isRefundable;
    private bool $isActive;
    private DateTimeInterface $createdAt;
    private DateTimeInterface $updatedAt;

    /**
     * Construtor da classe RatePlan
     * 
     * @param array<string, mixed> $data Dados para inicializar o plano de tarifas
     */
    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? '';
        $this->propertyId = $data['property_id'] ?? '';
        $this->name = $data['name'] ?? '';
        $this->description = $data['description'] ?? null;
        $this->currency = $data['currency'] ?? 'BRL';
        $this->rateType = $data['rate_type'] ?? 'per_night';
        $this->restrictions = $data['restrictions'] ?? [];
        $this->cancellationPolicy = $data['cancellation_policy'] ?? [];
        $this->isRefundable = $data['is_refundable'] ?? true;
        $this->isActive = $data['is_active'] ?? true;
        $this->createdAt = isset($data['created_at']) ? new DateTime($data['created_at']) : new DateTime();
        $this->updatedAt = isset($data['updated_at']) ? new DateTime($data['updated_at']) : new DateTime();

        $this->initializeEvents();

        if (!empty($data)) {
            $this->fireEvent(new RatePlanCreatedEvent($this));
        }
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
            'description' => $this->description,
            'currency' => $this->currency,
            'rate_type' => $this->rateType,
            'restrictions' => $this->restrictions,
            'cancellation_policy' => $this->cancellationPolicy,
            'is_refundable' => $this->isRefundable,
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getRateType(): string
    {
        return $this->rateType;
    }

    public function getRestrictions(): array
    {
        return $this->restrictions;
    }

    public function getCancellationPolicy(): array
    {
        return $this->cancellationPolicy;
    }

    public function isRefundable(): bool
    {
        return $this->isRefundable;
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

    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;
        return $this;
    }

    public function setRateType(string $rateType): self
    {
        $this->rateType = $rateType;
        return $this;
    }

    public function setRestrictions(array $restrictions): self
    {
        $this->restrictions = $restrictions;
        return $this;
    }

    public function setCancellationPolicy(array $cancellationPolicy): self
    {
        $this->cancellationPolicy = $cancellationPolicy;
        return $this;
    }

    public function setIsRefundable(bool $isRefundable): self
    {
        $this->isRefundable = $isRefundable;
        return $this;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;
        return $this;
    }

    /**
     * Obter restrição específica
     */
    public function getRestriction(string $key): mixed
    {
        return $this->restrictions[$key] ?? null;
    }

    /**
     * Definir restrição
     */
    public function setRestriction(string $key, mixed $value): self
    {
        $this->restrictions[$key] = $value;
        return $this;
    }

    /**
     * Verificar se tem restrição específica
     */
    public function hasRestriction(string $key): bool
    {
        return isset($this->restrictions[$key]);
    }

    /**
     * Obter estadia mínima
     */
    public function getMinStay(): ?int
    {
        return $this->getRestriction('min_stay');
    }

    /**
     * Definir estadia mínima
     */
    public function setMinStay(int $minStay): self
    {
        return $this->setRestriction('min_stay', $minStay);
    }

    /**
     * Obter estadia máxima
     */
    public function getMaxStay(): ?int
    {
        return $this->getRestriction('max_stay');
    }

    /**
     * Definir estadia máxima
     */
    public function setMaxStay(int $maxStay): self
    {
        return $this->setRestriction('max_stay', $maxStay);
    }

    /**
     * Obter antecedência mínima para reserva
     */
    public function getMinAdvanceReservation(): ?int
    {
        return $this->getRestriction('min_advance_booking');
    }

    /**
     * Definir antecedência mínima para reserva
     */
    public function setMinAdvanceReservation(int $days): self
    {
        return $this->setRestriction('min_advance_booking', $days);
    }

    /**
     * Obter antecedência máxima para reserva
     */
    public function getMaxAdvanceReservation(): ?int
    {
        return $this->getRestriction('max_advance_booking');
    }

    /**
     * Definir antecedência máxima para reserva
     */
    public function setMaxAdvanceReservation(int $days): self
    {
        return $this->setRestriction('max_advance_booking', $days);
    }

    /**
     * Verificar se permite check-in em dia específico
     */
    public function allowsCheckinOnDay(string $dayOfWeek): bool
    {
        $allowedDays = $this->getRestriction('allowed_checkin_days');

        if (!is_array($allowedDays)) {
            return true; // Se não há restrição, permite todos os dias
        }

        return in_array(strtolower($dayOfWeek), array_map('strtolower', $allowedDays));
    }

    /**
     * Verificar se permite check-out em dia específico
     */
    public function allowsCheckoutOnDay(string $dayOfWeek): bool
    {
        $allowedDays = $this->getRestriction('allowed_checkout_days');

        if (!is_array($allowedDays)) {
            return true; // Se não há restrição, permite todos os dias
        }

        return in_array(strtolower($dayOfWeek), array_map('strtolower', $allowedDays));
    }

    /**
     * Obter política de cancelamento formatada
     */
    public function getFormattedCancellationPolicy(): string
    {
        if (empty($this->cancellationPolicy)) {
            return $this->isRefundable ? 'Reembolsável' : 'Não reembolsável';
        }

        $policy = [];

        if (isset($this->cancellationPolicy['free_cancellation_until'])) {
            $policy[] = "Cancelamento gratuito até {$this->cancellationPolicy['free_cancellation_until']} dias antes";
        }

        if (isset($this->cancellationPolicy['penalty_percentage'])) {
            $policy[] = "Multa de {$this->cancellationPolicy['penalty_percentage']}% após o prazo";
        }

        return implode('. ', $policy) ?: ($this->isRefundable ? 'Reembolsável' : 'Não reembolsável');
    }
}
