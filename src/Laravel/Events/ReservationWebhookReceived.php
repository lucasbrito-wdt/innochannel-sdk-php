<?php

namespace Innochannel\Sdk\Laravel\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReservationWebhookReceived
{
    use Dispatchable, SerializesModels;

    /**
     * The webhook payload.
     */
    public array $payload;

    /**
     * The request headers.
     */
    public array $headers;

    /**
     * Create a new event instance.
     */
    public function __construct(array $payload, array $headers = [])
    {
        $this->payload = $payload;
        $this->headers = $headers;
    }

    /**
     * Get the reservation ID from the payload.
     */
    public function getReservationId(): ?string
    {
        return $this->payload['reservation_id'] ?? null;
    }

    /**
     * Get the event type.
     */
    public function getEventType(): ?string
    {
        return $this->payload['event_type'] ?? null;
    }

    /**
     * Get the reservation data.
     */
    public function getReservationData(): array
    {
        return $this->payload ?? [];
    }

    /**
     * Check if this is a reservation creation event.
     */
    public function isReservationCreated(): bool
    {
        return $this->getEventType() === 'reservation.created';
    }

    /**
     * Check if this is a reservation update event.
     */
    public function isReservationUpdated(): bool
    {
        return $this->getEventType() === 'reservation.updated';
    }

    /**
     * Check if this is a reservation cancellation event.
     */
    public function isReservationCancelled(): bool
    {
        return $this->getEventType() === 'reservation.cancelled';
    }

    /**
     * Check if this is a reservation modification event.
     */
    public function isReservationModified(): bool
    {
        return $this->getEventType() === 'reservation.modified';
    }
}
