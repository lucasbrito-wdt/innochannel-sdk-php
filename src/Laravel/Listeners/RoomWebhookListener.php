<?php

namespace Innochannel\Sdk\Laravel\Listeners;

use Innochannel\Sdk\Events\Models\RoomCreated;
use Innochannel\Sdk\Events\Models\RoomUpdated;
use Innochannel\Sdk\Events\Models\RoomDeleted;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RoomWebhookListener
{
    /**
     * Handle room created event.
     */
    public function handleRoomCreated(RoomCreated $event): void
    {
        $room = $event->getRoom();
        $roomData = $event->getRoomData();

        Log::info("Processing room created event", [
            'room_id' => $room->getId(),
            'room_name' => $room->getName(),
            'property_id' => $room->getPropertyId(),
            'timestamp' => now(),
        ]);

        try {
            // Store webhook in database for processing
            $this->storeWebhookRecord('room.created', $event->toArray());

            // Handle room creation logic
            $this->processRoomCreation($room, $roomData);

            // Clear related cache
            $this->clearRoomCache($room->getPropertyId(), $room->getId());

            Log::info("Room created event processed successfully", [
                'room_id' => $room->getId(),
                'room_name' => $room->getName(),
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to process room created event", [
                'room_id' => $room->getId(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle room updated event.
     */
    public function handleRoomUpdated(RoomUpdated $event): void
    {
        $room = $event->getRoom();
        $changes = $event->getChanges();

        Log::info("Processing room updated event", [
            'room_id' => $room->getId(),
            'room_name' => $room->getName(),
            'changes' => array_keys($changes),
            'timestamp' => now(),
        ]);

        try {
            // Store webhook in database for processing
            $this->storeWebhookRecord('room.updated', $event->toArray());

            // Handle room update logic
            $this->processRoomUpdate($room, $changes);

            // Clear related cache
            $this->clearRoomCache($room->getPropertyId(), $room->getId());

            Log::info("Room updated event processed successfully", [
                'room_id' => $room->getId(),
                'changes' => array_keys($changes),
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to process room updated event", [
                'room_id' => $room->getId(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle room deleted event.
     */
    public function handleRoomDeleted(RoomDeleted $event): void
    {
        $room = $event->getRoom();

        Log::info("Processing room deleted event", [
            'room_id' => $room->getId(),
            'room_name' => $room->getName(),
            'property_id' => $room->getPropertyId(),
            'timestamp' => now(),
        ]);

        try {
            // Store webhook in database for processing
            $this->storeWebhookRecord('room.deleted', $event->toArray());

            // Handle room deletion logic
            $this->processRoomDeletion($room);

            // Clear related cache
            $this->clearRoomCache($room->getPropertyId(), $room->getId());

            Log::info("Room deleted event processed successfully", [
                'room_id' => $room->getId(),
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to process room deleted event", [
                'room_id' => $room->getId(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Store webhook record in database.
     */
    protected function storeWebhookRecord(string $eventType, array $payload): void
    {
        DB::table('innochannel_webhooks')->insert([
            'event_type' => $eventType,
            'webhook_id' => $payload['webhook_id'] ?? uniqid('room_webhook_'),
            'payload' => json_encode($payload),
            'status' => 'processing',
            'headers' => json_encode([]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Process room creation.
     */
    protected function processRoomCreation($room, array $roomData): void
    {
        Log::info("New room created", [
            'room_id' => $room->getId(),
            'room_name' => $room->getName(),
            'room_type' => $roomData['type'] ?? null,
            'capacity' => $roomData['capacity'] ?? null,
        ]);

        // Add your custom logic here
        // For example:
        // - Create local room record
        // - Set up initial inventory
        // - Configure default rates
        // - Sync with PMS
        // - Update search indexes
        // - Notify relevant stakeholders
    }

    /**
     * Process room update.
     */
    protected function processRoomUpdate($room, array $changes): void
    {
        Log::info("Room updated", [
            'room_id' => $room->getId(),
            'room_name' => $room->getName(),
            'changes' => $changes,
        ]);

        // Add your custom logic here
        // For example:
        // - Update local room record
        // - Sync changes with PMS
        // - Update search indexes
        // - Notify relevant stakeholders
        // - Update cached room data
        // - Recalculate availability if capacity changed
    }

    /**
     * Process room deletion.
     */
    protected function processRoomDeletion($room): void
    {
        Log::info("Room deleted", [
            'room_id' => $room->getId(),
            'room_name' => $room->getName(),
        ]);

        // Add your custom logic here
        // For example:
        // - Archive room data
        // - Cancel future bookings
        // - Remove from search indexes
        // - Notify property owner
        // - Clean up related inventory data
        // - Update availability calculations
    }

    /**
     * Clear room-related cache.
     */
    protected function clearRoomCache(string $propertyId, string $roomId): void
    {
        $cacheKeys = [
            "room:{$roomId}",
            "property:{$propertyId}:rooms",
            "property:{$propertyId}:availability",
            "room:{$roomId}:rates",
            "room:{$roomId}:inventory",
        ];

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }

        Log::debug("Cleared room cache", [
            'property_id' => $propertyId,
            'room_id' => $roomId,
            'cache_keys' => $cacheKeys,
        ]);
    }
}