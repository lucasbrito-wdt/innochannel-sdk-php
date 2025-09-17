<?php

namespace Innochannel\Sdk\Laravel\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InventoryWebhookReceived
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
     * Get the property ID from the payload.
     */
    public function getPropertyId(): ?string
    {
        return $this->payload['data']['property_id'] ?? null;
    }

    /**
     * Get the event type.
     */
    public function getEventType(): ?string
    {
        return $this->payload['event_type'] ?? null;
    }

    /**
     * Get the inventory data.
     */
    public function getInventoryData(): array
    {
        return $this->payload['data'] ?? [];
    }

    /**
     * Check if this is a rates update event.
     */
    public function isRatesUpdated(): bool
    {
        return $this->getEventType() === 'inventory.rates_updated';
    }

    /**
     * Check if this is an availability update event.
     */
    public function isAvailabilityUpdated(): bool
    {
        return $this->getEventType() === 'inventory.availability_updated';
    }

    /**
     * Check if this is a restrictions update event.
     */
    public function isRestrictionsUpdated(): bool
    {
        return $this->getEventType() === 'inventory.restrictions_updated';
    }

    /**
     * Check if this is a bulk inventory update event.
     */
    public function isBulkInventoryUpdated(): bool
    {
        return $this->getEventType() === 'inventory.bulk_updated';
    }

    /**
     * Get the affected date range.
     */
    public function getDateRange(): array
    {
        $data = $this->getInventoryData();

        return [
            'start_date' => $data['start_date'] ?? null,
            'end_date' => $data['end_date'] ?? null,
        ];
    }

    /**
     * Get the affected room types.
     */
    public function getRoomTypes(): array
    {
        return $this->payload['data']['room_types'] ?? [];
    }
}
