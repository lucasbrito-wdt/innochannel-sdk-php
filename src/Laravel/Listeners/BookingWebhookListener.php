<?php

namespace Innochannel\Laravel\Listeners;

use Innochannel\Laravel\Events\BookingWebhookReceived;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class BookingWebhookListener
{
    /**
     * Handle the event.
     */
    public function handle(BookingWebhookReceived $event): void
    {
        $bookingId = $event->getBookingId();
        $eventType = $event->getEventType();
        $bookingData = $event->getBookingData();

        Log::info("Processing booking webhook", [
            'booking_id' => $bookingId,
            'event_type' => $eventType,
            'timestamp' => now(),
        ]);

        try {
            // Store webhook in database for processing
            $this->storeWebhookRecord($event);

            // Handle different event types
            match ($eventType) {
                'booking.created' => $this->handleBookingCreated($bookingData),
                'booking.updated' => $this->handleBookingUpdated($bookingData),
                'booking.cancelled' => $this->handleBookingCancelled($bookingData),
                'booking.modified' => $this->handleBookingModified($bookingData),
                default => $this->handleUnknownEvent($eventType, $bookingData),
            };

            // Clear related cache
            $this->clearRelatedCache($bookingId);

            Log::info("Booking webhook processed successfully", [
                'booking_id' => $bookingId,
                'event_type' => $eventType,
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to process booking webhook", [
                'booking_id' => $bookingId,
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
    protected function storeWebhookRecord(BookingWebhookReceived $event): void
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
     * Handle booking created event.
     */
    protected function handleBookingCreated(array $bookingData): void
    {
        // Example: Send welcome email, update local database, etc.
        Log::info("New booking created", [
            'booking_id' => $bookingData['id'] ?? null,
            'guest_name' => $bookingData['guest']['name'] ?? null,
            'property_id' => $bookingData['property_id'] ?? null,
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
    protected function handleBookingUpdated(array $bookingData): void
    {
        Log::info("Booking updated", [
            'booking_id' => $bookingData['id'] ?? null,
            'changes' => $bookingData['changes'] ?? [],
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
    protected function handleBookingCancelled(array $bookingData): void
    {
        Log::info("Booking cancelled", [
            'booking_id' => $bookingData['id'] ?? null,
            'cancellation_reason' => $bookingData['cancellation_reason'] ?? null,
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
    protected function handleBookingModified(array $bookingData): void
    {
        Log::info("Booking modified", [
            'booking_id' => $bookingData['id'] ?? null,
            'modifications' => $bookingData['modifications'] ?? [],
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
        Log::warning("Unknown booking event type received", [
            'event_type' => $eventType,
            'data_keys' => array_keys($data),
        ]);
    }

    /**
     * Clear related cache entries.
     */
    protected function clearRelatedCache(?string $bookingId): void
    {
        if (!$bookingId) {
            return;
        }

        $cacheKeys = [
            "booking:{$bookingId}",
            "booking:{$bookingId}:details",
            "bookings:list",
            "bookings:recent",
        ];

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }
    }
}