<?php

namespace Innochannel\Sdk\Laravel\Listeners;

use Innochannel\Sdk\Laravel\Events\PropertyWebhookReceived;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PropertyWebhookListener
{
    /**
     * Handle the event.
     */
    public function handle(PropertyWebhookReceived $event): void
    {
        $propertyId = $event->getPropertyId();
        $eventType = $event->getEventType();
        $propertyData = $event->getPropertyData();

        Log::info("Processing property webhook", [
            'property_id' => $propertyId,
            'event_type' => $eventType,
            'timestamp' => now(),
        ]);

        try {
            // Store webhook in database for processing
            $this->storeWebhookRecord($event);

            // Handle different event types
            match ($eventType) {
                'property.created' => $this->handlePropertyCreated($propertyData),
                'property.updated' => $this->handlePropertyUpdated($propertyData),
                'property.deleted' => $this->handlePropertyDeleted($propertyData),
                'property.status_changed' => $this->handlePropertyStatusChanged($propertyData),
                default => $this->handleUnknownEvent($eventType, $propertyData),
            };

            // Clear related cache
            $this->clearRelatedCache($propertyId);

            Log::info("Property webhook processed successfully", [
                'property_id' => $propertyId,
                'event_type' => $eventType,
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to process property webhook", [
                'property_id' => $propertyId,
                'event_type' => $eventType,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Store webhook record in database.
     */
    protected function storeWebhookRecord(PropertyWebhookReceived $event): void
    {
        DB::table('innochannel_webhooks')->insert([
            'event_type' => $event->getEventType(),
            'webhook_id' => $event->payload['webhook_id'] ?? uniqid('webhook_'),
            'payload' => json_encode($event->payload),
            'status' => 'processing',
            'headers' => json_encode($event->headers),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Handle property created event.
     */
    protected function handlePropertyCreated(array $propertyData): void
    {
        Log::info("New property created", [
            'property_id' => $propertyData['id'] ?? null,
            'property_name' => $propertyData['name'] ?? null,
            'property_type' => $propertyData['type'] ?? null,
        ]);

        // Add your custom logic here
        // For example:
        // - Create local property record
        // - Set up initial inventory
        // - Configure default rates
        // - Send welcome notification to property owner
        // - Initialize PMS integration
    }

    /**
     * Handle property updated event.
     */
    protected function handlePropertyUpdated(array $propertyData): void
    {
        Log::info("Property updated", [
            'property_id' => $propertyData['id'] ?? null,
            'changes' => $propertyData['changes'] ?? [],
        ]);

        // Add your custom logic here
        // For example:
        // - Update local property record
        // - Sync changes with PMS
        // - Update search indexes
        // - Notify relevant stakeholders
        // - Update cached property data
    }

    /**
     * Handle property deleted event.
     */
    protected function handlePropertyDeleted(array $propertyData): void
    {
        Log::info("Property deleted", [
            'property_id' => $propertyData['id'] ?? null,
            'deletion_reason' => $propertyData['deletion_reason'] ?? null,
        ]);

        // Add your custom logic here
        // For example:
        // - Archive property data
        // - Cancel active bookings
        // - Remove from search indexes
        // - Notify property owner
        // - Clean up related data
    }

    /**
     * Handle property status changed event.
     */
    protected function handlePropertyStatusChanged(array $propertyData): void
    {
        $oldStatus = $propertyData['old_status'] ?? null;
        $newStatus = $propertyData['new_status'] ?? null;

        Log::info("Property status changed", [
            'property_id' => $propertyData['id'] ?? null,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
        ]);

        // Add your custom logic here
        // For example:
        // - Update property availability
        // - Adjust booking rules
        // - Send status change notifications
        // - Update PMS status
        // - Handle maintenance mode
    }

    /**
     * Handle unknown event types.
     */
    protected function handleUnknownEvent(string $eventType, array $data): void
    {
        Log::warning("Unknown property event type received", [
            'event_type' => $eventType,
            'data_keys' => array_keys($data),
        ]);
    }

    /**
     * Clear related cache entries.
     */
    protected function clearRelatedCache(?string $propertyId): void
    {
        if (!$propertyId) {
            return;
        }

        $cacheKeys = [
            "property:{$propertyId}",
            "property:{$propertyId}:details",
            "property:{$propertyId}:rooms",
            "property:{$propertyId}:rates",
            "properties:list",
            "properties:active",
        ];

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }

        // Clear property-related tags
        Cache::tags(['properties', "property:{$propertyId}"])->flush();
    }
}
