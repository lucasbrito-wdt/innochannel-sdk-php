<?php

namespace Innochannel\Laravel\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GeneralWebhookReceived
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
     * Get the event type.
     */
    public function getEventType(): ?string
    {
        return $this->payload['event_type'] ?? null;
    }

    /**
     * Get the event data.
     */
    public function getEventData(): array
    {
        return $this->payload['data'] ?? [];
    }

    /**
     * Check if this is a system notification event.
     */
    public function isSystemNotification(): bool
    {
        return $this->getEventType() === 'system.notification';
    }

    /**
     * Check if this is a maintenance event.
     */
    public function isMaintenanceEvent(): bool
    {
        return str_starts_with($this->getEventType() ?? '', 'maintenance.');
    }

    /**
     * Check if this is an API status event.
     */
    public function isApiStatusEvent(): bool
    {
        return str_starts_with($this->getEventType() ?? '', 'api.status.');
    }

    /**
     * Check if this is a webhook test event.
     */
    public function isWebhookTest(): bool
    {
        return $this->getEventType() === 'webhook.test';
    }

    /**
     * Get the notification message if this is a system notification.
     */
    public function getNotificationMessage(): ?string
    {
        if ($this->isSystemNotification()) {
            return $this->payload['data']['message'] ?? null;
        }

        return null;
    }

    /**
     * Get the severity level of the event.
     */
    public function getSeverity(): string
    {
        return $this->payload['severity'] ?? 'info';
    }
}