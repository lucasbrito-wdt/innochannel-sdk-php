<?php

namespace Innochannel\Sdk\Laravel\Listeners;

use Innochannel\Sdk\Laravel\Events\ReservationWebhookReceived;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ReservationWebhookListener
{
    /**
     * Handle the event.
     */
    public function handle(ReservationWebhookReceived $event): void
    {
        $reservationId = $event->getReservationId();
        $eventType = $event->getEventType();
        $reservationData = $event->getReservationData();

        Log::info("Processing reservation webhook", [
            'reservation_id' => $reservationId,
            'event_type' => $eventType,
            'timestamp' => now(),
        ]);

        try {
            // Store webhook in database for processing
            $this->storeWebhookRecord($event);

            // Handle different event types
            match ($eventType) {
                'reservation.created' => $this->handleReservationCreated($reservationData),
                'reservation.updated' => $this->handleReservationUpdated($reservationData),
                'reservation.cancelled' => $this->handleReservationCancelled($reservationData),
                'reservation.modified' => $this->handleReservationModified($reservationData),
                default => $this->handleUnknownEvent($eventType, $reservationData),
            };

            // Clear related cache
            $this->clearRelatedCache($reservationId);

            Log::info("Reservation webhook processed successfully", [
                'reservation_id' => $reservationId,
                'event_type' => $eventType,
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to process booking webhook", [
                'reservation_id' => $reservationId,
                'event_type' => $eventType,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // You might want to queue this for retry
            throw $e;
        }
    }

    /**
     * Store webhook record in database.
     */
    protected function storeWebhookRecord(ReservationWebhookReceived $event): void
    {
        DB::table('innochannel_reservation_webhooks')->insert([
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
     * Handle reservation created event.
     */
    protected function handleReservationCreated(array $reservationData): void
    {
        // Example: Send welcome email, update local database, etc.
        Log::info("New reservation created", [
            'reservation_id' => $reservationData['id'] ?? null,
            'guest_name' => $reservationData['guest']['name'] ?? null,
            'property_id' => $reservationData['property_id'] ?? null,
        ]);

        // Add your custom logic here
        // For example:
        // - Send confirmation email to guest
        // - Update local booking database
        // - Trigger PMS sync
        // - Send notification to property manager
    }

    /**
     * Handle booking updated event.
     */
    protected function handleReservationUpdated(array $reservationData): void
    {
        Log::info("Reservation updated", [
            'reservation_id' => $reservationData['id'] ?? null,
            'changes' => $reservationData['changes'] ?? [],
        ]);

        // Add your custom logic here
        // For example:
        // - Update local booking record
        // - Send update notification to guest
        // - Sync changes with PMS
    }

    /**
     * Handle booking cancelled event.
     */
    protected function handleReservationCancelled(array $reservationData): void
    {
        Log::info("Reservation cancelled", [
            'reservation_id' => $reservationData['id'] ?? null,
            'cancellation_reason' => $reservationData['cancellation_reason'] ?? null,
        ]);

        // Add your custom logic here
        // For example:
        // - Send cancellation confirmation
        // - Process refund if applicable
        // - Update inventory availability
        // - Notify property manager
    }

    /**
     * Handle booking modified event.
     */
    protected function handleReservationModified(array $reservationData): void
    {
        Log::info("Reservation modified", [
            'reservation_id' => $reservationData['id'] ?? null,
            'modifications' => $reservationData['modifications'] ?? [],
        ]);

        // Add your custom logic here
        // For example:
        // - Calculate price difference
        // - Send modification confirmation
        // - Update PMS with changes
    }

    /**
     * Handle unknown event types.
     */
    protected function handleUnknownEvent(string $eventType, array $data): void
    {
        Log::warning("Unknown reservation event type received", [
            'event_type' => $eventType,
            'data_keys' => array_keys($data),
        ]);
    }

    /**
     * Clear related cache entries.
     */
    protected function clearRelatedCache(?string $reservationId): void
    {
        if (!$reservationId) {
            return;
        }

        $cacheKeys = [
            "reservation:{$reservationId}",
            "reservation:{$reservationId}:details",
            "reservations:list",
            "reservations:recent",
        ];

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }
    }
}
