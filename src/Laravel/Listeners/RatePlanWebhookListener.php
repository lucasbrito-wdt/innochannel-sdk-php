<?php

namespace Innochannel\Sdk\Laravel\Listeners;

use Innochannel\Sdk\Events\Models\RatePlanCreated;
use Innochannel\Sdk\Events\Models\RatePlanUpdated;
use Innochannel\Sdk\Events\Models\RatePlanDeleted;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RatePlanWebhookListener
{
    /**
     * Handle rate plan created event.
     */
    public function handleRatePlanCreated(RatePlanCreated $event): void
    {
        $ratePlan = $event->getRatePlan();
        $ratePlanData = $event->getRatePlanData();

        Log::info("Processing rate plan created event", [
            'rate_plan_id' => $ratePlan->getId(),
            'rate_plan_name' => $ratePlan->getName(),
            'property_id' => $ratePlan->getPropertyId(),
            'timestamp' => now(),
        ]);

        try {
            // Store webhook in database for processing
            $this->storeWebhookRecord('rate_plan.created', $event->toArray());

            // Handle rate plan creation logic
            $this->processRatePlanCreation($ratePlan, $ratePlanData);

            // Clear related cache
            $this->clearRatePlanCache($ratePlan->getPropertyId(), $ratePlan->getId());

            Log::info("Rate plan created event processed successfully", [
                'rate_plan_id' => $ratePlan->getId(),
                'rate_plan_name' => $ratePlan->getName(),
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to process rate plan created event", [
                'rate_plan_id' => $ratePlan->getId(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle rate plan updated event.
     */
    public function handleRatePlanUpdated(RatePlanUpdated $event): void
    {
        $ratePlan = $event->getRatePlan();
        $changes = $event->getChanges();

        Log::info("Processing rate plan updated event", [
            'rate_plan_id' => $ratePlan->getId(),
            'rate_plan_name' => $ratePlan->getName(),
            'changes' => array_keys($changes),
            'timestamp' => now(),
        ]);

        try {
            // Store webhook in database for processing
            $this->storeWebhookRecord('rate_plan.updated', $event->toArray());

            // Handle rate plan update logic
            $this->processRatePlanUpdate($ratePlan, $changes);

            // Clear related cache
            $this->clearRatePlanCache($ratePlan->getPropertyId(), $ratePlan->getId());

            Log::info("Rate plan updated event processed successfully", [
                'rate_plan_id' => $ratePlan->getId(),
                'changes' => array_keys($changes),
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to process rate plan updated event", [
                'rate_plan_id' => $ratePlan->getId(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle rate plan deleted event.
     */
    public function handleRatePlanDeleted(RatePlanDeleted $event): void
    {
        $ratePlan = $event->getRatePlan();

        Log::info("Processing rate plan deleted event", [
            'rate_plan_id' => $ratePlan->getId(),
            'rate_plan_name' => $ratePlan->getName(),
            'property_id' => $ratePlan->getPropertyId(),
            'timestamp' => now(),
        ]);

        try {
            // Store webhook in database for processing
            $this->storeWebhookRecord('rate_plan.deleted', $event->toArray());

            // Handle rate plan deletion logic
            $this->processRatePlanDeletion($ratePlan);

            // Clear related cache
            $this->clearRatePlanCache($ratePlan->getPropertyId(), $ratePlan->getId());

            Log::info("Rate plan deleted event processed successfully", [
                'rate_plan_id' => $ratePlan->getId(),
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to process rate plan deleted event", [
                'rate_plan_id' => $ratePlan->getId(),
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
            'webhook_id' => $payload['webhook_id'] ?? uniqid('rate_plan_webhook_'),
            'payload' => json_encode($payload),
            'status' => 'processing',
            'headers' => json_encode([]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Process rate plan creation.
     */
    protected function processRatePlanCreation($ratePlan, array $ratePlanData): void
    {
        Log::info("New rate plan created", [
            'rate_plan_id' => $ratePlan->getId(),
            'rate_plan_name' => $ratePlan->getName(),
            'rate_plan_type' => $ratePlanData['type'] ?? null,
            'base_rate' => $ratePlanData['base_rate'] ?? null,
        ]);

        // Add your custom logic here
        // For example:
        // - Create local rate plan record
        // - Set up initial pricing
        // - Configure rate rules
        // - Sync with PMS
        // - Update search indexes
        // - Notify relevant stakeholders
        // - Calculate derived rates
    }

    /**
     * Process rate plan update.
     */
    protected function processRatePlanUpdate($ratePlan, array $changes): void
    {
        Log::info("Rate plan updated", [
            'rate_plan_id' => $ratePlan->getId(),
            'rate_plan_name' => $ratePlan->getName(),
            'changes' => $changes,
        ]);

        // Add your custom logic here
        // For example:
        // - Update local rate plan record
        // - Sync changes with PMS
        // - Update search indexes
        // - Notify relevant stakeholders
        // - Update cached rate data
        // - Recalculate pricing if base rate changed
        // - Update derived rates
        // - Notify reservations engines of rate changes
    }

    /**
     * Process rate plan deletion.
     */
    protected function processRatePlanDeletion($ratePlan): void
    {
        Log::info("Rate plan deleted", [
            'rate_plan_id' => $ratePlan->getId(),
            'rate_plan_name' => $ratePlan->getName(),
        ]);

        // Add your custom logic here
        // For example:
        // - Archive rate plan data
        // - Handle existing bookings with this rate
        // - Remove from search indexes
        // - Notify property owner
        // - Clean up related pricing data
        // - Update availability calculations
        // - Notify reservations engines of rate removal
    }

    /**
     * Clear rate plan-related cache.
     */
    protected function clearRatePlanCache(string $propertyId, string $ratePlanId): void
    {
        $cacheKeys = [
            "rate_plan:{$ratePlanId}",
            "property:{$propertyId}:rate_plans",
            "property:{$propertyId}:rates",
            "rate_plan:{$ratePlanId}:pricing",
            "rate_plan:{$ratePlanId}:rules",
        ];

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }

        Log::debug("Cleared rate plan cache", [
            'property_id' => $propertyId,
            'rate_plan_id' => $ratePlanId,
            'cache_keys' => $cacheKeys,
        ]);
    }
}
