<?php

namespace Innochannel\Sdk\Laravel\Listeners;

use Innochannel\Sdk\Laravel\Events\GeneralWebhookReceived;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class GeneralWebhookListener
{
    /**
     * Handle the event.
     */
    public function handle(GeneralWebhookReceived $event): void
    {
        $eventType = $event->getEventType();
        $eventData = $event->getEventData();

        Log::info("Processing general webhook", [
            'event_type' => $eventType,
            'is_system_notification' => $event->isSystemNotification(),
            'is_maintenance_event' => $event->isMaintenanceEvent(),
            'is_api_status_event' => $event->isApiStatusEvent(),
            'timestamp' => now(),
        ]);

        try {
            // Store webhook in database for processing
            $this->storeWebhookRecord($event);

            // Handle different event types
            if ($event->isSystemNotification()) {
                $this->handleSystemNotification($event);
            } elseif ($event->isMaintenanceEvent()) {
                $this->handleMaintenanceEvent($event);
            } elseif ($event->isApiStatusEvent()) {
                $this->handleApiStatusEvent($event);
            } elseif ($event->isWebhookTest()) {
                $this->handleWebhookTest($event);
            } else {
                $this->handleGenericEvent($eventType, $eventData);
            }

            Log::info("General webhook processed successfully", [
                'event_type' => $eventType,
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to process general webhook", [
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
    protected function storeWebhookRecord(GeneralWebhookReceived $event): void
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
     * Handle system notification events.
     */
    protected function handleSystemNotification(GeneralWebhookReceived $event): void
    {
        $message = $event->getNotificationMessage();
        $severity = $event->getSeverityLevel();

        Log::info("System notification received", [
            'message' => $message,
            'severity' => $severity,
        ]);

        // Handle different severity levels
        match ($severity) {
            'critical' => $this->handleCriticalNotification($message, $event->getEventData()),
            'warning' => $this->handleWarningNotification($message, $event->getEventData()),
            'info' => $this->handleInfoNotification($message, $event->getEventData()),
            default => $this->handleGenericNotification($message, $event->getEventData()),
        };
    }

    /**
     * Handle maintenance events.
     */
    protected function handleMaintenanceEvent(GeneralWebhookReceived $event): void
    {
        $eventData = $event->getEventData();
        $maintenanceType = $eventData['maintenance_type'] ?? 'unknown';
        $scheduledTime = $eventData['scheduled_time'] ?? null;
        $duration = $eventData['estimated_duration'] ?? null;

        Log::info("Maintenance event received", [
            'maintenance_type' => $maintenanceType,
            'scheduled_time' => $scheduledTime,
            'duration' => $duration,
        ]);

        // Add your custom logic here
        // For example:
        // - Schedule maintenance notifications
        // - Prepare system for downtime
        // - Update status pages
        // - Notify administrators
        // - Cache maintenance status

        Cache::put('innochannel_maintenance_status', [
            'type' => $maintenanceType,
            'scheduled_time' => $scheduledTime,
            'duration' => $duration,
            'status' => $eventData['status'] ?? 'scheduled',
        ], now()->addHours(24));
    }

    /**
     * Handle API status events.
     */
    protected function handleApiStatusEvent(GeneralWebhookReceived $event): void
    {
        $eventData = $event->getEventData();
        $apiStatus = $eventData['api_status'] ?? 'unknown';
        $services = $eventData['services'] ?? [];

        Log::info("API status event received", [
            'api_status' => $apiStatus,
            'services' => array_keys($services),
        ]);

        // Update API status cache
        Cache::put('innochannel_api_status', [
            'status' => $apiStatus,
            'services' => $services,
            'last_updated' => now(),
        ], now()->addMinutes(30));

        // Handle different API statuses
        match ($apiStatus) {
            'operational' => $this->handleApiOperational($services),
            'degraded' => $this->handleApiDegraded($services),
            'outage' => $this->handleApiOutage($services),
            default => Log::warning("Unknown API status: {$apiStatus}"),
        };
    }

    /**
     * Handle webhook test events.
     */
    protected function handleWebhookTest(GeneralWebhookReceived $event): void
    {
        Log::info("Webhook test received", [
            'test_id' => $event->getEventData()['test_id'] ?? null,
            'timestamp' => $event->getEventData()['timestamp'] ?? null,
        ]);

        // Respond to webhook test - this confirms webhook is working
        // You might want to update a status or send a confirmation
    }

    /**
     * Handle critical notifications.
     */
    protected function handleCriticalNotification(string $message, array $data): void
    {
        Log::critical("Critical system notification", [
            'message' => $message,
            'data' => $data,
        ]);

        // Add your critical notification logic here
        // For example:
        // - Send immediate alerts to administrators
        // - Trigger emergency procedures
        // - Update system status
        // - Send SMS/email notifications
    }

    /**
     * Handle warning notifications.
     */
    protected function handleWarningNotification(string $message, array $data): void
    {
        Log::warning("System warning notification", [
            'message' => $message,
            'data' => $data,
        ]);

        // Add your warning notification logic here
        // For example:
        // - Send email notifications
        // - Update monitoring dashboards
        // - Log for review
    }

    /**
     * Handle info notifications.
     */
    protected function handleInfoNotification(string $message, array $data): void
    {
        Log::info("System info notification", [
            'message' => $message,
            'data' => $data,
        ]);

        // Add your info notification logic here
        // For example:
        // - Update status displays
        // - Log for audit trail
        // - Send to monitoring systems
    }

    /**
     * Handle generic notifications.
     */
    protected function handleGenericNotification(string $message, array $data): void
    {
        Log::info("Generic system notification", [
            'message' => $message,
            'data' => $data,
        ]);
    }

    /**
     * Handle API operational status.
     */
    protected function handleApiOperational(array $services): void
    {
        Log::info("API is operational", ['services' => array_keys($services)]);

        // Clear any degradation alerts
        Cache::forget('innochannel_api_degraded');
    }

    /**
     * Handle API degraded status.
     */
    protected function handleApiDegraded(array $services): void
    {
        Log::warning("API is degraded", ['services' => array_keys($services)]);

        Cache::put('innochannel_api_degraded', true, now()->addHours(1));

        // Add your degraded API logic here
        // For example:
        // - Enable fallback mechanisms
        // - Reduce API call frequency
        // - Notify administrators
    }

    /**
     * Handle API outage status.
     */
    protected function handleApiOutage(array $services): void
    {
        Log::error("API outage detected", ['services' => array_keys($services)]);

        Cache::put('innochannel_api_outage', true, now()->addHours(2));

        // Add your outage handling logic here
        // For example:
        // - Switch to offline mode
        // - Queue operations for later
        // - Send critical alerts
        // - Update status pages
    }

    /**
     * Handle generic events.
     */
    protected function handleGenericEvent(string $eventType, array $eventData): void
    {
        Log::info("Generic event received", [
            'event_type' => $eventType,
            'data_keys' => array_keys($eventData),
        ]);
    }
}
