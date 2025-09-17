<?php

namespace Innochannel\Laravel\Listeners;

use Innochannel\Laravel\Events\InventoryWebhookReceived;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class InventoryWebhookListener
{
    /**
     * Handle the event.
     */
    public function handle(InventoryWebhookReceived $event): void
    {
        $propertyId = $event->getPropertyId();
        $eventType = $event->getEventType();
        $inventoryData = $event->getInventoryData();

        Log::info("Processing inventory webhook", [
            'property_id' => $propertyId,
            'event_type' => $eventType,
            'date_range' => $event->getDateRange(),
            'room_types' => $event->getRoomTypes(),
            'timestamp' => now(),
        ]);

        try {
            // Store webhook in database for processing
            $this->storeWebhookRecord($event);

            // Handle different event types
            match ($eventType) {
                'inventory.rates_updated' => $this->handleRatesUpdated($inventoryData),
                'inventory.availability_updated' => $this->handleAvailabilityUpdated($inventoryData),
                'inventory.restrictions_updated' => $this->handleRestrictionsUpdated($inventoryData),
                'inventory.bulk_updated' => $this->handleBulkUpdated($inventoryData),
                default => $this->handleUnknownEvent($eventType, $inventoryData),
            };

            // Clear related cache
            $this->clearRelatedCache($propertyId, $event->getDateRange(), $event->getRoomTypes());

            Log::info("Inventory webhook processed successfully", [
                'property_id' => $propertyId,
                'event_type' => $eventType,
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to process inventory webhook", [
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
    protected function storeWebhookRecord(InventoryWebhookReceived $event): void
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
     * Handle rates updated event.
     */
    protected function handleRatesUpdated(array $inventoryData): void
    {
        $rates = $inventoryData['rates'] ?? [];
        $propertyId = $inventoryData['property_id'] ?? null;

        Log::info("Rates updated", [
            'property_id' => $propertyId,
            'rates_count' => count($rates),
            'date_range' => $inventoryData['date_range'] ?? null,
        ]);

        // Add your custom logic here
        // For example:
        // - Update local rate database
        // - Sync with PMS rates
        // - Recalculate pricing strategies
        // - Update booking engine rates
        // - Notify revenue management system
    }

    /**
     * Handle availability updated event.
     */
    protected function handleAvailabilityUpdated(array $inventoryData): void
    {
        $availability = $inventoryData['availability'] ?? [];
        $propertyId = $inventoryData['property_id'] ?? null;

        Log::info("Availability updated", [
            'property_id' => $propertyId,
            'availability_count' => count($availability),
            'date_range' => $inventoryData['date_range'] ?? null,
        ]);

        // Add your custom logic here
        // For example:
        // - Update local availability database
        // - Sync with PMS inventory
        // - Update booking engine availability
        // - Trigger overbooking alerts if needed
        // - Update channel manager
    }

    /**
     * Handle restrictions updated event.
     */
    protected function handleRestrictionsUpdated(array $inventoryData): void
    {
        $restrictions = $inventoryData['restrictions'] ?? [];
        $propertyId = $inventoryData['property_id'] ?? null;

        Log::info("Restrictions updated", [
            'property_id' => $propertyId,
            'restrictions_count' => count($restrictions),
            'restriction_types' => array_keys($restrictions),
        ]);

        // Add your custom logic here
        // For example:
        // - Update local restrictions database
        // - Apply minimum stay rules
        // - Update closed-to-arrival/departure settings
        // - Sync with booking engine rules
        // - Update channel restrictions
    }

    /**
     * Handle bulk updated event.
     */
    protected function handleBulkUpdated(array $inventoryData): void
    {
        $updates = $inventoryData['updates'] ?? [];
        $propertyId = $inventoryData['property_id'] ?? null;

        Log::info("Bulk inventory updated", [
            'property_id' => $propertyId,
            'update_types' => array_keys($updates),
            'total_updates' => array_sum(array_map('count', $updates)),
        ]);

        // Process each type of update
        if (isset($updates['rates'])) {
            $this->handleRatesUpdated(['rates' => $updates['rates'], 'property_id' => $propertyId]);
        }

        if (isset($updates['availability'])) {
            $this->handleAvailabilityUpdated(['availability' => $updates['availability'], 'property_id' => $propertyId]);
        }

        if (isset($updates['restrictions'])) {
            $this->handleRestrictionsUpdated(['restrictions' => $updates['restrictions'], 'property_id' => $propertyId]);
        }
    }

    /**
     * Handle unknown event types.
     */
    protected function handleUnknownEvent(string $eventType, array $data): void
    {
        Log::warning("Unknown inventory event type received", [
            'event_type' => $eventType,
            'data_keys' => array_keys($data),
        ]);
    }

    /**
     * Clear related cache entries.
     */
    protected function clearRelatedCache(?string $propertyId, ?array $dateRange, ?array $roomTypes): void
    {
        if (!$propertyId) {
            return;
        }

        $cacheKeys = [
            "inventory:{$propertyId}",
            "rates:{$propertyId}",
            "availability:{$propertyId}",
            "restrictions:{$propertyId}",
        ];

        // Add date-specific cache keys if date range is provided
        if ($dateRange) {
            $startDate = $dateRange['start'] ?? null;
            $endDate = $dateRange['end'] ?? null;
            
            if ($startDate && $endDate) {
                $cacheKeys[] = "inventory:{$propertyId}:{$startDate}:{$endDate}";
                $cacheKeys[] = "rates:{$propertyId}:{$startDate}:{$endDate}";
                $cacheKeys[] = "availability:{$propertyId}:{$startDate}:{$endDate}";
            }
        }

        // Add room type specific cache keys
        if ($roomTypes) {
            foreach ($roomTypes as $roomType) {
                $cacheKeys[] = "inventory:{$propertyId}:room:{$roomType}";
                $cacheKeys[] = "rates:{$propertyId}:room:{$roomType}";
                $cacheKeys[] = "availability:{$propertyId}:room:{$roomType}";
            }
        }

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }

        // Clear inventory-related tags
        Cache::tags(['inventory', "property:{$propertyId}"])->flush();
    }
}