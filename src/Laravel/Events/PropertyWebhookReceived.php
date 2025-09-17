<?php

namespace Innochannel\Laravel\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PropertyWebhookReceived
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
     * Get the property data.
     */
    public function getPropertyData(): array
    {
        return $this->payload['data'] ?? [];
    }

    /**
     * Check if this is a property creation event.
     */
    public function isPropertyCreated(): bool
    {
        return $this->getEventType() === 'property.created';
    }

    /**
     * Check if this is a property update event.
     */
    public function isPropertyUpdated(): bool
    {
        return $this->getEventType() === 'property.updated';
    }

    /**
     * Check if this is a property deletion event.
     */
    public function isPropertyDeleted(): bool
    {
        return $this->getEventType() === 'property.deleted';
    }

    /**
     * Check if this is a property status change event.
     */
    public function isPropertyStatusChanged(): bool
    {
        return $this->getEventType() === 'property.status_changed';
    }
}