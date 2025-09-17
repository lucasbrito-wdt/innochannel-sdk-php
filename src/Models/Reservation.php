<?php

declare(strict_types=1);

namespace Innochannel\Sdk\Models;

use DateTime;
use DateTimeInterface;
use JsonSerializable;
use Innochannel\Sdk\Traits\HasEvents;
use Innochannel\Sdk\Events\Models\ReservationCreated;
use Innochannel\Sdk\Events\Models\ReservationUpdated;
use Innochannel\Sdk\Events\Models\ReservationConfirmed;
use Innochannel\Sdk\Events\Models\ReservationCancelled;
use Innochannel\Sdk\Events\Models\ReservationDeleted;

/**
 * Represents a reservation in the Innochannel system.
 * 
 * This class handles reservation data and provides methods for
 * creating, updating, and managing reservations.
 * 
 * @package Innochannel\Sdk\Models
 * @author Innochannel SDK Team
 * @version 1.0.0
 */
class Reservation implements JsonSerializable
{
    use HasEvents;
    private ?string $id = null;
    private ?string $propertyId = null;
    private ?string $roomId = null;
    private ?string $ratePlanId = null;
    private ?string $bookingReference = null;
    private ?string $pmsBookingId = null;
    private ?string $otaBookingId = null;
    private ?string $channelCode = null;
    private ?DateTimeInterface $checkIn = null;
    private ?DateTimeInterface $checkOut = null;
    private int $adults = 1;
    private int $children = 0;
    private int $nights = 0;
    private ?array $guest = null;
    private ?array $additionalGuests = null;
    private string $status = 'pending';
    private ?float $totalAmount = null;
    private ?string $currency = null;
    private ?array $breakdown = null;
    private ?array $services = null;
    private ?array $specialRequests = null;
    private ?array $policies = null;
    private ?array $paymentInfo = null;
    private ?DateTimeInterface $createdAt = null;
    private ?DateTimeInterface $updatedAt = null;
    private ?DateTimeInterface $cancelledAt = null;
    private ?string $cancellationReason = null;
    private array $originalData = [];
    
    /**
     * Construtor da classe Reservation
     * 
     * @param array<string, mixed> $data Dados para inicializar a reserva
     */
    public function __construct(array $data = [])
    {
        $this->initializeEvents();
        
        if (!empty($data)) {
            $this->fillFromArray($data);
            $this->fireEvent(new ReservationCreated($this));
        }
    }
    
    /**
     * Criar instância a partir de array
     * 
     * @param array<string, mixed> $data Dados da reserva
     * @return static Nova instância de Reservation
     */
    public static function fromArray(array $data): self
    {
        return new self($data);
    }
    
    /**
     * Preencher propriedades a partir de array
     * 
     * @param array<string, mixed> $data Dados para preenchimento
     * @return self
     */
    public function fillFromArray(array $data): self
    {
        $originalData = $this->toArray();
        
        $this->id = $data['id'] ?? null;
        $this->propertyId = isset($data['propertyId']) ? $data['propertyId'] : (isset($data['property_id']) ? $data['property_id'] : null);
        $this->roomId = isset($data['roomId']) ? $data['roomId'] : (isset($data['room_id']) ? $data['room_id'] : null);
        $this->ratePlanId = $data['ratePlanId'] ?? $data['rate_plan_id'] ?? null;
        $this->bookingReference = $data['booking_reference'] ?? null;
        $this->pmsBookingId = $data['pms_booking_id'] ?? null;
        $this->otaBookingId = $data['ota_booking_id'] ?? null;
        $this->channelCode = $data['channel_code'] ?? null;
        $this->adults = $data['adults'] ?? 1;
        $this->children = $data['children'] ?? 0;
        $this->nights = $data['nights'] ?? 0;
        
        // Handle guest data - support both formats
        if (isset($data['guestName'])) {
            $nameParts = explode(' ', trim($data['guestName']), 2);
            $this->guest = [
                'first_name' => $nameParts[0] ?? '',
                'last_name' => $nameParts[1] ?? '',
                'email' => $data['guestEmail'] ?? null,
                'phone' => $data['guestPhone'] ?? null
            ];
        } else {
            $this->guest = $data['guest'] ?? null;
        }
        
        $this->additionalGuests = $data['additional_guests'] ?? null;
        $this->status = $data['status'] ?? 'pending';
        $this->totalAmount = $data['totalAmount'] ?? $data['total_amount'] ?? null;
        $this->currency = $data['currency'] ?? null;
        $this->breakdown = $data['breakdown'] ?? null;
        $this->services = $data['services'] ?? null;
        
        // Handle special requests - convert string to array if needed
        $specialRequests = $data['specialRequests'] ?? $data['special_requests'] ?? null;
        if (is_string($specialRequests)) {
            $this->specialRequests = [$specialRequests];
        } else {
            $this->specialRequests = $specialRequests;
        }
        
        $this->policies = $data['policies'] ?? null;
        $this->paymentInfo = $data['payment_info'] ?? null;
        $this->cancellationReason = $data['cancellation_reason'] ?? null;
        
        // Converter datas
        if (isset($data['checkIn']) || isset($data['check_in'])) {
            $checkInDate = $data['checkIn'] ?? $data['check_in'];
            $this->checkIn = new DateTime($checkInDate);
        }
        
        if (isset($data['checkOut']) || isset($data['check_out'])) {
            $checkOutDate = $data['checkOut'] ?? $data['check_out'];
            $this->checkOut = new DateTime($checkOutDate);
        }
        
        if (isset($data['createdAt']) || isset($data['created_at'])) {
            $createdAtDate = $data['createdAt'] ?? $data['created_at'];
            $this->createdAt = new DateTime($createdAtDate);
        }
        
        if (isset($data['updatedAt']) || isset($data['updated_at'])) {
            $updatedAtDate = $data['updatedAt'] ?? $data['updated_at'];
            $this->updatedAt = new DateTime($updatedAtDate);
        }
        
        if (isset($data['cancelled_at'])) {
            $this->cancelledAt = new DateTime($data['cancelled_at']);
        }
         
         // Store original data after filling
         $this->originalData = $this->toArray();
         
         // Disparar evento de atualização se houve mudanças
         $newData = $this->toArray();
         if ($originalData !== $newData && !empty($originalData)) {
             $this->fireEvent(new ReservationUpdated($this, $originalData, $newData));
         }
         return $this;
     }
     
     /**
      * Disparar evento de atualização se necessário
      * 
      * @param array<string, mixed> $originalData Dados originais
      * @return void
      */
     private function fireUpdateEventIfNeeded(array $originalData): void
     {
         $newData = $this->toArray();
         if ($originalData !== $newData && !empty($originalData)) {
             $this->fireEvent(new ReservationUpdated($this, $originalData, $newData));
         }
    }
    
    /**
     * Converter para array
     * 
     * @return array<string, mixed> Array com dados da reserva
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'property_id' => $this->propertyId,
            'room_id' => $this->roomId,
            'rate_plan_id' => $this->ratePlanId,
            'booking_reference' => $this->bookingReference,
            'pms_booking_id' => $this->pmsBookingId,
            'ota_booking_id' => $this->otaBookingId,
            'channel_code' => $this->channelCode,
            'check_in' => $this->checkIn?->format('Y-m-d'),
            'check_out' => $this->checkOut?->format('Y-m-d'),
            'adults' => $this->adults,
            'children' => $this->children,
            'nights' => $this->nights,
            'guest' => $this->guest,
            'guestName' => $this->getGuestName(),
            'additional_guests' => $this->additionalGuests,
            'status' => $this->status,
            'total_amount' => $this->totalAmount,
            'totalAmount' => $this->totalAmount,
            'currency' => $this->currency,
            'breakdown' => $this->breakdown,
            'services' => $this->services,
            'special_requests' => $this->specialRequests,
            'policies' => $this->policies,
            'payment_info' => $this->paymentInfo,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
            'cancelled_at' => $this->cancelledAt?->format('Y-m-d H:i:s'),
            'cancellation_reason' => $this->cancellationReason,
        ];
    }
    
    // Getters
    public function getId(): ?string { return $this->id; }
    public function getPropertyId(): ?string { return $this->propertyId; }
    public function getRoomId(): ?string { return $this->roomId; }
    public function getRatePlanId(): ?string { return $this->ratePlanId; }
    public function getReservationReference(): ?string { return $this->bookingReference; }
    public function getPmsBookingId(): ?string { return $this->pmsBookingId; }
    public function getOtaBookingId(): ?string { return $this->otaBookingId; }
    public function getChannelCode(): ?string { return $this->channelCode; }
    public function getCheckIn(): ?DateTimeInterface { return $this->checkIn; }
    public function getCheckOut(): ?DateTimeInterface { return $this->checkOut; }
    public function getAdults(): int { return $this->adults; }
    public function getChildren(): int { return $this->children; }
    public function getNights(): int { return $this->nights; }
    public function getGuest(): ?array { return $this->guest; }
    public function getAdditionalGuests(): ?array { return $this->additionalGuests; }
    public function getStatus(): string { return $this->status; }
    public function getTotalAmount(): ?float { return $this->totalAmount; }
    public function getCurrency(): ?string { return $this->currency; }
    public function getBreakdown(): ?array { return $this->breakdown; }
    public function getServices(): ?array { return $this->services; }
    public function getSpecialRequests(): ?array { return $this->specialRequests; }
    public function getPolicies(): ?array { return $this->policies; }
    public function getPaymentInfo(): ?array { return $this->paymentInfo; }
    public function getCreatedAt(): ?DateTimeInterface { return $this->createdAt; }
    public function getUpdatedAt(): ?DateTimeInterface { return $this->updatedAt; }
    public function getCancelledAt(): ?DateTimeInterface { return $this->cancelledAt; }
    public function getCancellationReason(): ?string { return $this->cancellationReason; }
    
    // Setters com eventos
    public function setId(?string $id): self { 
        $old = $this->id;
        $this->id = $id; 
        if ($old !== $id) {
            $this->fireEvent(new ReservationUpdated($this, ['id' => $old], ['id' => $id]));
        }
        return $this; 
    }
    
    public function setPropertyId(?int $propertyId): self { 
        $old = $this->propertyId;
        $this->propertyId = $propertyId; 
        if ($old !== $propertyId) {
            $this->fireEvent(new ReservationUpdated($this, ['property_id' => $old], ['property_id' => $propertyId]));
        }
        return $this; 
    }
    
    public function setRoomId(?int $roomId): self { 
        $old = $this->roomId;
        $this->roomId = $roomId; 
        if ($old !== $roomId) {
            $this->fireEvent(new ReservationUpdated($this, ['room_id' => $old], ['room_id' => $roomId]));
        }
        return $this; 
    }
    
    public function setRatePlanId(?string $ratePlanId): self { 
        $old = $this->ratePlanId;
        $this->ratePlanId = $ratePlanId; 
        if ($old !== $ratePlanId) {
            $this->fireEvent(new ReservationUpdated($this, ['rate_plan_id' => $old], ['rate_plan_id' => $ratePlanId]));
        }
        return $this; 
    }
    
    public function setReservationReference(?string $reservationReference): self {
        $old = $this->bookingReference;
        $this->bookingReference = $reservationReference;
        if ($old !== $reservationReference) {
            $this->fireEvent(new ReservationUpdated($this, ['booking_reference' => $old], ['booking_reference' => $reservationReference]));
        }
        return $this;
    }
    
    public function setPmsBookingId(?string $pmsBookingId): self { 
        $old = $this->pmsBookingId;
        $this->pmsBookingId = $pmsBookingId; 
        if ($old !== $pmsBookingId) {
            $this->fireEvent(new ReservationUpdated($this, ['pms_booking_id' => $old], ['pms_booking_id' => $pmsBookingId]));
        }
        return $this; 
    }
    
    public function setOtaBookingId(?string $otaBookingId): self { 
        $old = $this->otaBookingId;
        $this->otaBookingId = $otaBookingId; 
        if ($old !== $otaBookingId) {
            $this->fireEvent(new ReservationUpdated($this, ['ota_booking_id' => $old], ['ota_booking_id' => $otaBookingId]));
        }
        return $this; 
    }
    
    public function setChannelCode(?string $channelCode): self { 
        $old = $this->channelCode;
        $this->channelCode = $channelCode; 
        if ($old !== $channelCode) {
            $this->fireEvent(new ReservationUpdated($this, ['channel_code' => $old], ['channel_code' => $channelCode]));
        }
        return $this; 
    }
    
    public function setCheckIn(?DateTimeInterface $checkIn): self { 
        $old = $this->checkIn;
        $this->checkIn = $checkIn; 
        if ($old !== $checkIn) {
            $this->fireEvent(new ReservationUpdated($this, ['check_in' => $old], ['check_in' => $checkIn]));
        }
        return $this; 
    }
    
    public function setCheckOut(?DateTimeInterface $checkOut): self { 
        $old = $this->checkOut;
        $this->checkOut = $checkOut; 
        if ($old !== $checkOut) {
            $this->fireEvent(new ReservationUpdated($this, ['check_out' => $old], ['check_out' => $checkOut]));
        }
        return $this; 
    }
    
    public function setAdults(int $adults): self { 
        $old = $this->adults;
        $this->adults = $adults; 
        if ($old !== $adults) {
            $this->fireEvent(new ReservationUpdated($this, ['adults' => $old], ['adults' => $adults]));
        }
        return $this; 
    }
    
    public function setChildren(int $children): self { 
        $old = $this->children;
        $this->children = $children; 
        if ($old !== $children) {
            $this->fireEvent(new ReservationUpdated($this, ['children' => $old], ['children' => $children]));
        }
        return $this; 
    }
    
    public function setNights(int $nights): self { 
        $old = $this->nights;
        $this->nights = $nights; 
        if ($old !== $nights) {
            $this->fireEvent(new ReservationUpdated($this, ['nights' => $old], ['nights' => $nights]));
        }
        return $this; 
    }
    
    public function setGuest(?array $guest): self { 
        $old = $this->guest;
        $this->guest = $guest; 
        if ($old !== $guest) {
            $this->fireEvent(new ReservationUpdated($this, ['guest' => $old], ['guest' => $guest]));
        }
        return $this; 
    }
    
    public function setAdditionalGuests(?array $additionalGuests): self { 
        $old = $this->additionalGuests;
        $this->additionalGuests = $additionalGuests; 
        if ($old !== $additionalGuests) {
            $this->fireEvent(new ReservationUpdated($this, ['additional_guests' => $old], ['additional_guests' => $additionalGuests]));
        }
        return $this; 
    }
    
    public function setStatus(string $status): self { 
        $old = $this->status;
        $this->status = $status; 
        
        if ($old !== $status) {
            $this->fireEvent(new ReservationUpdated($this, ['status' => $old], ['status' => $status]));
            
            // Disparar eventos específicos baseados no status
            if ($status === 'confirmed') {
                $this->fireEvent(new ReservationConfirmed($this));
            } elseif ($status === 'cancelled') {
                $this->fireEvent(new ReservationCancelled($this));
            }
        }
        
        return $this; 
    }
    
    public function setTotalAmount(?float $totalAmount): self { 
        $old = $this->totalAmount;
        $this->totalAmount = $totalAmount; 
        if ($old !== $totalAmount) {
            $this->fireEvent(new ReservationUpdated($this, ['total_amount' => $old], ['total_amount' => $totalAmount]));
        }
        return $this; 
    }
    
    public function setCurrency(?string $currency): self { 
        $old = $this->currency;
        $this->currency = $currency; 
        if ($old !== $currency) {
            $this->fireEvent(new ReservationUpdated($this, ['currency' => $old], ['currency' => $currency]));
        }
        return $this; 
    }
    
    public function setBreakdown(?array $breakdown): self { 
        $old = $this->breakdown;
        $this->breakdown = $breakdown; 
        if ($old !== $breakdown) {
            $this->fireEvent(new ReservationUpdated($this, ['breakdown' => $old], ['breakdown' => $breakdown]));
        }
        return $this; 
    }
    
    public function setServices(?array $services): self { 
        $old = $this->services;
        $this->services = $services; 
        if ($old !== $services) {
            $this->fireEvent(new ReservationUpdated($this, ['services' => $old], ['services' => $services]));
        }
        return $this; 
    }
    
    public function setSpecialRequests(?array $specialRequests): self { 
        $old = $this->specialRequests;
        $this->specialRequests = $specialRequests; 
        if ($old !== $specialRequests) {
            $this->fireEvent(new ReservationUpdated($this, ['special_requests' => $old], ['special_requests' => $specialRequests]));
        }
        return $this; 
    }
    
    public function setPolicies(?array $policies): self { 
        $old = $this->policies;
        $this->policies = $policies; 
        if ($old !== $policies) {
            $this->fireEvent(new ReservationUpdated($this, ['policies' => $old], ['policies' => $policies]));
        }
        return $this; 
    }
    
    public function setPaymentInfo(?array $paymentInfo): self { 
        $old = $this->paymentInfo;
        $this->paymentInfo = $paymentInfo; 
        if ($old !== $paymentInfo) {
            $this->fireEvent(new ReservationUpdated($this, ['payment_info' => $old], ['payment_info' => $paymentInfo]));
        }
        return $this; 
    }
    
    public function setCancellationReason(?string $cancellationReason): self { 
        $old = $this->cancellationReason;
        $this->cancellationReason = $cancellationReason; 
        if ($old !== $cancellationReason) {
            $this->fireEvent(new ReservationUpdated($this, ['cancellation_reason' => $old], ['cancellation_reason' => $cancellationReason]));
        }
        return $this; 
    }
    
    /**
      * Método para deletar a reserva (dispara evento)
      * 
      * @return void
      */
     public function delete(): void
     {
         $this->fireEvent(new ReservationDeleted($this));
     }
    
    // Métodos utilitários
    
    /**
     * Verificar se a reserva está confirmada
     * 
     * @return bool
     */
    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }
    
    /**
     * Verificar se a reserva está cancelada
     * 
     * @return bool
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }
    
    /**
     * Verificar se a reserva está pendente
     * 
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
    
    /**
     * Obter número total de hóspedes
     * 
     * @return int
     */
    public function getTotalGuests(): int
    {
        return $this->adults + $this->children;
    }
    
    /**
     * Obter nome completo do hóspede principal
     * 
     * @return string|null
     */
    public function getGuestFullName(): ?string
    {
        if (!$this->guest) {
            return null;
        }
        
        $firstName = $this->guest['first_name'] ?? '';
        $lastName = $this->guest['last_name'] ?? '';
        
        return trim($firstName . ' ' . $lastName) ?: null;
    }
    
    /**
     * Obter nome do hóspede principal (alias para getGuestFullName)
     * 
     * @return string|null
     */
    public function getGuestName(): ?string
    {
        return $this->getGuestFullName();
    }
    
    /**
     * Definir nome do hóspede principal
     * 
     * @param string|null $name Nome completo do hóspede
     * @return self
     */
    public function setGuestName(?string $name): self
    {
        $originalData = $this->toArray();
        
        if ($name) {
            $nameParts = explode(' ', trim($name), 2);
            $this->guest = array_merge($this->guest ?? [], [
                'first_name' => $nameParts[0] ?? '',
                'last_name' => $nameParts[1] ?? ''
            ]);
        } else {
            $this->guest = null;
        }
        
        $this->fireUpdateEventIfNeeded($originalData);
         return $this;
    }
    
    /**
     * Obter email do hóspede principal
     * 
     * @return string|null
     */
    public function getGuestEmail(): ?string
    {
        return $this->guest['email'] ?? null;
    }
    
    /**
     * Obter telefone do hóspede principal
     * 
     * @return string|null
     */
    public function getGuestPhone(): ?string
    {
        return $this->guest['phone'] ?? null;
    }
    
    /**
     * Calcular duração da estadia em dias
     * 
     * @return int
     */
    public function getStayDuration(): int
    {
        if (!$this->checkIn || !$this->checkOut) {
            return 0;
        }
        
        return $this->checkIn->diff($this->checkOut)->days;
    }
    
    /**
     * Verificar se a reserva é para hoje
     * 
     * @return bool
     */
    public function isCheckInToday(): bool
    {
        if (!$this->checkIn) {
            return false;
        }
        
        return $this->checkIn->format('Y-m-d') === (new DateTime())->format('Y-m-d');
    }
    
    /**
     * Verificar se o checkout é hoje
     * 
     * @return bool
     */
    public function isCheckOutToday(): bool
    {
        if (!$this->checkOut) {
            return false;
        }
        
        return $this->checkOut->format('Y-m-d') === (new DateTime())->format('Y-m-d');
    }
    
    /**
     * Obter valor formatado
     * 
     * @return string|null
     */
    public function getFormattedAmount(): ?string
    {
        if ($this->totalAmount === null) {
            return null;
        }
        
        $currency = $this->currency ?? 'USD';
        return $currency . ' ' . number_format($this->totalAmount, 2);
    }
    
    /**
     * Verificar se o modelo foi modificado
     * 
     * @return bool
     */
    public function isDirty(): bool
    {
        return $this->originalData !== $this->toArray();
    }
    
    /**
     * Obter mudanças realizadas
     * 
     * @return array<string, mixed>
     */
    public function getChanges(): array
    {
        if (!$this->isDirty()) {
            return [];
        }
        
        $current = $this->toArray();
        $changes = [];
        
        foreach ($current as $key => $value) {
            $oldValue = $this->originalData[$key] ?? null;
            if ($oldValue !== $value) {
                $changes[$key] = [
                    'old' => $oldValue,
                    'new' => $value
                ];
            }
        }
        
        return $changes;
    }
}