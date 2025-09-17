<?php

namespace Innochannel\Laravel\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BookingWebhookReceived
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
     * Get the booking ID from the payload.
     */
    public function getBookingId(): ?string
    {
        return $this->payload['data']['id'] ?? null;
    }

    /**
     * Get the event type.
     */
    public function getEventType(): ?string
    {
        return $this->payload['event_type'] ?? null;
    }

    /**
     * Get the booking data.
     */
    public function getBookingData(): array
    {
        return $this->payload['data'] ?? [];
    }

    /**
     * Check if this is a booking creation event.
     */
    public function isBookingCreated(): bool
    {
        return $this->getEventType() === 'booking.created';
    }

    /**
     * Check if this is a booking update event.
     */
    public function isBookingUpdated(): bool
    {
        return $this->getEventType() === 'booking.updated';
    }

    /**
     * Check if this is a booking cancellation event.
     */
    public function isBookingCancelled(): bool
    {
        return $this->getEventType() === 'booking.cancelled';
    }

    /**
     * Check if this is a booking modification event.
     */
    public function isBookingModified(): bool
    {
        return $this->getEventType() === 'booking.modified';
    }
}