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
    private ?int $propertyId = null;
    private ?int $propertyOtaConnectionId = null;
    private ?string $otaName = null;
    private ?string $otaReservationId = null;
    private ?string $otaConfirmationCode = null;
    private string $status = 'new';
    private ?string $paymentStatus = null;
    private ?string $guestName = null;
    private ?string $guestEmail = null;
    private ?string $guestPhone = null;
    private ?string $guestAddress = null;
    private ?string $guestCountry = null;
    private ?array $specialRequests = null;
    private ?DateTimeInterface $checkInDate = null;
    private ?DateTimeInterface $checkOutDate = null;
    private ?string $checkInTime = null;
    private ?string $checkOutTime = null;
    private int $nights = 0;
    private int $adults = 1;
    private int $children = 0;
    private int $infants = 0;
    private ?string $otaRoomId = null;
    private ?string $otaRatePlanId = null;
    private ?string $roomName = null;
    private int $roomQuantity = 1;
    private ?float $totalAmount = null;
    private ?float $roomRate = null;
    private ?float $taxes = null;
    private ?float $fees = null;
    private ?float $commissionAmount = null;
    private ?float $commissionPercentage = null;
    private ?string $currency = null;
    private bool $acknowledgedToOta = false;
    private ?DateTimeInterface $acknowledgedAt = null;
    private bool $syncedToPms = false;
    private ?DateTimeInterface $syncedToPmsAt = null;
    private ?string $pmsReservationId = null;
    private ?array $pmsResponse = null;
    private ?array $rawData = null;
    private ?array $extras = null;
    private ?DateTimeInterface $bookingDate = null;
    private ?DateTimeInterface $modificationDate = null;
    private ?DateTimeInterface $cancellationDate = null;
    private ?string $cancellationReason = null;
    private ?DateTimeInterface $createdAt = null;
    private ?DateTimeInterface $updatedAt = null;
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
        $this->propertyId = $data['property_id'] ?? null;
        $this->propertyOtaConnectionId = $data['property_ota_connection_id'] ?? null;
        $this->otaName = $data['ota_name'] ?? null;
        $this->otaReservationId = $data['ota_reservation_id'] ?? null;
        $this->otaConfirmationCode = $data['ota_confirmation_code'] ?? null;
        $this->status = $data['status'] ?? 'new';
        $this->paymentStatus = $data['payment_status'] ?? null;
        $this->guestName = $data['guest_name'] ?? null;
        $this->guestEmail = $data['guest_email'] ?? null;
        $this->guestPhone = $data['guest_phone'] ?? null;
        $this->guestAddress = $data['guest_address'] ?? null;
        $this->guestCountry = $data['guest_country'] ?? null;
        
        // Handle special requests - convert string to array if needed
        $specialRequests = $data['special_requests'] ?? null;
        if (is_string($specialRequests)) {
            $this->specialRequests = [$specialRequests];
        } else {
            $this->specialRequests = $specialRequests;
        }

        $this->checkInTime = $data['check_in_time'] ?? null;
        $this->checkOutTime = $data['check_out_time'] ?? null;
        $this->nights = $data['nights'] ?? 0;
        $this->adults = $data['adults'] ?? 1;
        $this->children = $data['children'] ?? 0;
        $this->infants = $data['infants'] ?? 0;
        $this->otaRoomId = $data['ota_room_id'] ?? null;
        $this->otaRatePlanId = $data['ota_rate_plan_id'] ?? null;
        $this->roomName = $data['room_name'] ?? null;
        $this->roomQuantity = $data['room_quantity'] ?? 1;
        $this->totalAmount = $data['total_amount'] ?? null;
        $this->roomRate = $data['room_rate'] ?? null;
        $this->taxes = $data['taxes'] ?? null;
        $this->fees = $data['fees'] ?? null;
        $this->commissionAmount = $data['commission_amount'] ?? null;
        $this->commissionPercentage = $data['commission_percentage'] ?? null;
        $this->currency = $data['currency'] ?? null;
        $this->acknowledgedToOta = $data['acknowledged_to_ota'] ?? false;
        $this->syncedToPms = $data['synced_to_pms'] ?? false;
        $this->pmsReservationId = $data['pms_reservation_id'] ?? null;
        $this->pmsResponse = $data['pms_response'] ?? null;
        $this->rawData = $data['raw_data'] ?? null;
        $this->extras = $data['extras'] ?? null;
        $this->cancellationReason = $data['cancellation_reason'] ?? null;

        // Converter datas
        if (isset($data['check_in_date'])) {
            $this->checkInDate = new DateTime($data['check_in_date']);
        }

        if (isset($data['check_out_date'])) {
            $this->checkOutDate = new DateTime($data['check_out_date']);
        }

        if (isset($data['booking_date'])) {
            $this->bookingDate = new DateTime($data['booking_date']);
        }

        if (isset($data['modification_date'])) {
            $this->modificationDate = new DateTime($data['modification_date']);
        }

        if (isset($data['cancellation_date'])) {
            $this->cancellationDate = new DateTime($data['cancellation_date']);
        }

        if (isset($data['acknowledged_at'])) {
            $this->acknowledgedAt = new DateTime($data['acknowledged_at']);
        }

        if (isset($data['synced_to_pms_at'])) {
            $this->syncedToPmsAt = new DateTime($data['synced_to_pms_at']);
        }

        if (isset($data['created_at'])) {
            $this->createdAt = new DateTime($data['created_at']);
        }

        if (isset($data['updated_at'])) {
            $this->updatedAt = new DateTime($data['updated_at']);
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
            'property_ota_connection_id' => $this->propertyOtaConnectionId,
            'ota_name' => $this->otaName,
            'ota_reservation_id' => $this->otaReservationId,
            'pms_reservation_id' => $this->pmsReservationId,
            'status' => $this->status,
            'check_in_date' => $this->checkInDate?->format('Y-m-d'),
            'check_out_date' => $this->checkOutDate?->format('Y-m-d'),
            'nights' => $this->nights,
            'adults' => $this->adults,
            'children' => $this->children,
            'guest_name' => $this->guestName,
            'guest_email' => $this->guestEmail,
            'guest_phone' => $this->guestPhone,
            'guest_document' => $this->guestDocument,
            'guest_address' => $this->guestAddress,
            'special_requests' => $this->specialRequests,
            'total_amount' => $this->totalAmount,
            'currency' => $this->currency,
            'commission_amount' => $this->commissionAmount,
            'cancellation_reason' => $this->cancellationReason,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }

    // Getters
    public function getId(): ?string
    {
        return $this->id;
    }

    public function getPropertyId(): ?int
    {
        return $this->propertyId;
    }

    public function getPropertyOtaConnectionId(): ?int
    {
        return $this->propertyOtaConnectionId;
    }

    public function getOtaName(): ?string
    {
        return $this->otaName;
    }

    public function getOtaReservationId(): ?string
    {
        return $this->otaReservationId;
    }

    public function getOtaConfirmationCode(): ?string
    {
        return $this->otaConfirmationCode;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getPaymentStatus(): ?string
    {
        return $this->paymentStatus;
    }

    public function getGuestName(): ?string
    {
        return $this->guestName;
    }

    public function getGuestEmail(): ?string
    {
        return $this->guestEmail;
    }

    public function getGuestPhone(): ?string
    {
        return $this->guestPhone;
    }

    public function getGuestAddress(): ?string
    {
        return $this->guestAddress;
    }

    public function getGuestCountry(): ?string
    {
        return $this->guestCountry;
    }

    public function getSpecialRequests(): ?array
    {
        return $this->specialRequests;
    }

    public function getCheckInDate(): ?DateTimeInterface
    {
        return $this->checkInDate;
    }

    public function getCheckOutDate(): ?DateTimeInterface
    {
        return $this->checkOutDate;
    }

    public function getCheckInTime(): ?string
    {
        return $this->checkInTime;
    }

    public function getCheckOutTime(): ?string
    {
        return $this->checkOutTime;
    }

    public function getNights(): int
    {
        return $this->nights;
    }

    public function getAdults(): int
    {
        return $this->adults;
    }

    public function getChildren(): int
    {
        return $this->children;
    }

    public function getInfants(): int
    {
        return $this->infants;
    }

    public function getOtaRoomId(): ?string
    {
        return $this->otaRoomId;
    }

    public function getOtaRatePlanId(): ?string
    {
        return $this->otaRatePlanId;
    }

    public function getRoomName(): ?string
    {
        return $this->roomName;
    }

    public function getRoomQuantity(): int
    {
        return $this->roomQuantity;
    }

    public function getTotalAmount(): ?float
    {
        return $this->totalAmount;
    }

    public function getRoomRate(): ?float
    {
        return $this->roomRate;
    }

    public function getTaxes(): ?float
    {
        return $this->taxes;
    }

    public function getFees(): ?float
    {
        return $this->fees;
    }

    public function getCommissionAmount(): ?float
    {
        return $this->commissionAmount;
    }

    public function getCommissionPercentage(): ?float
    {
        return $this->commissionPercentage;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function isAcknowledgedToOta(): bool
    {
        return $this->acknowledgedToOta;
    }

    public function getAcknowledgedAt(): ?DateTimeInterface
    {
        return $this->acknowledgedAt;
    }

    public function isSyncedToPms(): bool
    {
        return $this->syncedToPms;
    }

    public function getSyncedToPmsAt(): ?DateTimeInterface
    {
        return $this->syncedToPmsAt;
    }

    public function getPmsReservationId(): ?string
    {
        return $this->pmsReservationId;
    }

    public function getPmsResponse(): ?array
    {
        return $this->pmsResponse;
    }

    public function getRawData(): ?array
    {
        return $this->rawData;
    }

    public function getExtras(): ?array
    {
        return $this->extras;
    }

    public function getBookingDate(): ?DateTimeInterface
    {
        return $this->bookingDate;
    }

    public function getModificationDate(): ?DateTimeInterface
    {
        return $this->modificationDate;
    }

    public function getCancellationDate(): ?DateTimeInterface
    {
        return $this->cancellationDate;
    }

    public function getCancellationReason(): ?string
    {
        return $this->cancellationReason;
    }
    public function getPolicies(): ?array
    {
        return $this->policies;
    }
    public function getPaymentInfo(): ?array
    {
        return $this->paymentInfo;
    }
    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }
    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }
    public function getCancelledAt(): ?DateTimeInterface
    {
        return $this->cancelledAt;
    }

    // Setters com eventos
    public function setId(?string $id): self
    {
        $old = $this->id;
        $this->id = $id;
        if ($old !== $id) {
            $this->fireEvent(new ReservationUpdated($this, ['id' => $old], ['id' => $id]));
        }
        return $this;
    }

    public function setPropertyId(?int $propertyId): self
    {
        $old = $this->propertyId;
        $this->propertyId = $propertyId;
        if ($old !== $propertyId) {
            $this->fireEvent(new ReservationUpdated($this, ['property_id' => $old], ['property_id' => $propertyId]));
        }
        return $this;
    }

    public function setPropertyOtaConnectionId(?int $propertyOtaConnectionId): self
    {
        $old = $this->propertyOtaConnectionId;
        $this->propertyOtaConnectionId = $propertyOtaConnectionId;
        if ($old !== $propertyOtaConnectionId) {
            $this->fireEvent(new ReservationUpdated($this, ['property_ota_connection_id' => $old], ['property_ota_connection_id' => $propertyOtaConnectionId]));
        }
        return $this;
    }

    public function setOtaName(?string $otaName): self
    {
        $old = $this->otaName;
        $this->otaName = $otaName;
        if ($old !== $otaName) {
            $this->fireEvent(new ReservationUpdated($this, ['ota_name' => $old], ['ota_name' => $otaName]));
        }
        return $this;
    }

    public function setOtaReservationId(?string $otaReservationId): self
    {
        $old = $this->otaReservationId;
        $this->otaReservationId = $otaReservationId;
        if ($old !== $otaReservationId) {
            $this->fireEvent(new ReservationUpdated($this, ['ota_reservation_id' => $old], ['ota_reservation_id' => $otaReservationId]));
        }
        return $this;
    }

    public function setPmsReservationId(?string $pmsReservationId): self
    {
        $old = $this->pmsReservationId;
        $this->pmsReservationId = $pmsReservationId;
        if ($old !== $pmsReservationId) {
            $this->fireEvent(new ReservationUpdated($this, ['pms_reservation_id' => $old], ['pms_reservation_id' => $pmsReservationId]));
        }
        return $this;
    }

    public function setStatus(?string $status): self
    {
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

    public function setCheckInDate(?DateTimeInterface $checkInDate): self
    {
        $old = $this->checkInDate;
        $this->checkInDate = $checkInDate;
        if ($old !== $checkInDate) {
            $this->fireEvent(new ReservationUpdated($this, ['check_in_date' => $old], ['check_in_date' => $checkInDate]));
        }
        return $this;
    }

    public function setCheckOutDate(?DateTimeInterface $checkOutDate): self
    {
        $old = $this->checkOutDate;
        $this->checkOutDate = $checkOutDate;
        if ($old !== $checkOutDate) {
            $this->fireEvent(new ReservationUpdated($this, ['check_out_date' => $old], ['check_out_date' => $checkOutDate]));
        }
        return $this;
    }

    public function setNights(int $nights): self
    {
        $old = $this->nights;
        $this->nights = $nights;
        if ($old !== $nights) {
            $this->fireEvent(new ReservationUpdated($this, ['nights' => $old], ['nights' => $nights]));
        }
        return $this;
    }

    public function setAdults(int $adults): self
    {
        $old = $this->adults;
        $this->adults = $adults;
        if ($old !== $adults) {
            $this->fireEvent(new ReservationUpdated($this, ['adults' => $old], ['adults' => $adults]));
        }
        return $this;
    }

    public function setChildren(int $children): self
    {
        $old = $this->children;
        $this->children = $children;
        if ($old !== $children) {
            $this->fireEvent(new ReservationUpdated($this, ['children' => $old], ['children' => $children]));
        }
        return $this;
    }

    public function setGuestName(?string $guestName): self
    {
        $old = $this->guestName;
        $this->guestName = $guestName;
        if ($old !== $guestName) {
            $this->fireEvent(new ReservationUpdated($this, ['guest_name' => $old], ['guest_name' => $guestName]));
        }
        return $this;
    }

    public function setGuestEmail(?string $guestEmail): self
    {
        $old = $this->guestEmail;
        $this->guestEmail = $guestEmail;
        if ($old !== $guestEmail) {
            $this->fireEvent(new ReservationUpdated($this, ['guest_email' => $old], ['guest_email' => $guestEmail]));
        }
        return $this;
    }

    public function setGuestPhone(?string $guestPhone): self
    {
        $old = $this->guestPhone;
        $this->guestPhone = $guestPhone;
        if ($old !== $guestPhone) {
            $this->fireEvent(new ReservationUpdated($this, ['guest_phone' => $old], ['guest_phone' => $guestPhone]));
        }
        return $this;
    }

    public function setGuestDocument(?string $guestDocument): self
    {
        $old = $this->guestDocument;
        $this->guestDocument = $guestDocument;
        if ($old !== $guestDocument) {
            $this->fireEvent(new ReservationUpdated($this, ['guest_document' => $old], ['guest_document' => $guestDocument]));
        }
        return $this;
    }

    public function setGuestAddress(?string $guestAddress): self
    {
        $old = $this->guestAddress;
        $this->guestAddress = $guestAddress;
        if ($old !== $guestAddress) {
            $this->fireEvent(new ReservationUpdated($this, ['guest_address' => $old], ['guest_address' => $guestAddress]));
        }
        return $this;
    }

    public function setSpecialRequests(?string $specialRequests): self
    {
        $old = $this->specialRequests;
        $this->specialRequests = $specialRequests;
        if ($old !== $specialRequests) {
            $this->fireEvent(new ReservationUpdated($this, ['special_requests' => $old], ['special_requests' => $specialRequests]));
        }
        return $this;
    }

    public function setTotalAmount(?float $totalAmount): self
    {
        $old = $this->totalAmount;
        $this->totalAmount = $totalAmount;
        if ($old !== $totalAmount) {
            $this->fireEvent(new ReservationUpdated($this, ['total_amount' => $old], ['total_amount' => $totalAmount]));
        }
        return $this;
    }

    public function setCurrency(?string $currency): self
    {
        $old = $this->currency;
        $this->currency = $currency;
        if ($old !== $currency) {
            $this->fireEvent(new ReservationUpdated($this, ['currency' => $old], ['currency' => $currency]));
        }
        return $this;
    }

    public function setCommissionAmount(?float $commissionAmount): self
    {
        $old = $this->commissionAmount;
        $this->commissionAmount = $commissionAmount;
        if ($old !== $commissionAmount) {
            $this->fireEvent(new ReservationUpdated($this, ['commission_amount' => $old], ['commission_amount' => $commissionAmount]));
        }
        return $this;
    }

    public function setCancellationReason(?string $cancellationReason): self
    {
        $old = $this->cancellationReason;
        $this->cancellationReason = $cancellationReason;
        if ($old !== $cancellationReason) {
            $this->fireEvent(new ReservationUpdated($this, ['cancellation_reason' => $old], ['cancellation_reason' => $cancellationReason]));
        }
        return $this;
    }

    public function setServices(?array $services): self
    {
        $old = $this->services;
        $this->services = $services;
        if ($old !== $services) {
            $this->fireEvent(new ReservationUpdated($this, ['services' => $old], ['services' => $services]));
        }
        return $this;
    }

    public function setPolicies(?array $policies): self
    {
        $old = $this->policies;
        $this->policies = $policies;
        if ($old !== $policies) {
            $this->fireEvent(new ReservationUpdated($this, ['policies' => $old], ['policies' => $policies]));
        }
        return $this;
    }

    public function setPaymentInfo(?array $paymentInfo): self
    {
        $old = $this->paymentInfo;
        $this->paymentInfo = $paymentInfo;
        if ($old !== $paymentInfo) {
            $this->fireEvent(new ReservationUpdated($this, ['payment_info' => $old], ['payment_info' => $paymentInfo]));
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
        return $this->guestName;
    }

    /**
     * Obter nome do hóspede principal (alias para getGuestFullName)
     * 
     * @return string|null
     */

    /**
     * Obter email do hóspede principal
     * 
     * @return string|null
     */

    /**
     * Obter telefone do hóspede principal
     * 
     * @return string|null
     */

    /**
     * Calcular duração da estadia em dias
     * 
     * @return int
     */
    public function getStayDuration(): int
    {
        if (!$this->checkInDate || !$this->checkOutDate) {
            return 0;
        }

        return $this->checkInDate->diff($this->checkOutDate)->days;
    }

    /**
     * Verificar se a reserva é para hoje
     * 
     * @return bool
     */
    public function isCheckInToday(): bool
    {
        if (!$this->checkInDate) {
            return false;
        }

        return $this->checkInDate->format('Y-m-d') === (new DateTime())->format('Y-m-d');
    }

    /**
     * Verificar se o checkout é hoje
     * 
     * @return bool
     */
    public function isCheckOutToday(): bool
    {
        if (!$this->checkOutDate) {
            return false;
        }

        return $this->checkOutDate->format('Y-m-d') === (new DateTime())->format('Y-m-d');
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

    /**
     * Implementação da interface JsonSerializable
     * 
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
