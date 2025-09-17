<?php

namespace Innochannel\Laravel\Console;

use Illuminate\Console\Command;
use Innochannel\Laravel\Facades\Innochannel;
use Innochannel\Laravel\Facades\InnochannelReservation;
use Innochannel\Laravel\Facades\InnochannelProperty;
use Innochannel\Laravel\Facades\InnochannelInventory;
use Exception;

class SyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'innochannel:sync 
                            {type : Type of sync (reservations, properties, inventory, all)}
                            {--id= : Specific ID to sync}
                            {--property-id= : Property ID for inventory sync}
                            {--force : Force sync even if recently synced}
                            {--dry-run : Show what would be synced without actually syncing}';

    /**
     * The console command description.
     */
    protected $description = 'Sync data with PMS system';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $type = $this->argument('type');
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->info('🔍 DRY RUN MODE - No actual sync will be performed');
            $this->info('');
        }

        $this->info("Starting {$type} sync...");

        try {
            switch ($type) {
                case 'reservations':
                    return $this->syncReservations($isDryRun);

                case 'properties':
                    return $this->syncProperties($isDryRun);

                case 'inventory':
                    return $this->syncInventory($isDryRun);

                case 'all':
                    return $this->syncAll($isDryRun);

                default:
                    $this->error("Invalid sync type: {$type}");
                    $this->info('Available types: reservations, properties, inventory, all');
                    return self::FAILURE;
            }
        } catch (Exception $e) {
            $this->error("Sync failed: {$e->getMessage()}");
            return self::FAILURE;
        }
    }

    /**
     * Sync reservations with PMS.
     */
    protected function syncReservations(bool $isDryRun): int
    {
        $specificId = $this->option('id');

        if ($specificId) {
            $this->info("Syncing reservation: {$specificId}");

            if (!$isDryRun) {
                $result = InnochannelReservation::syncWithPms($specificId);
                $this->displaySyncResult('Reservation', $specificId, $result);
            } else {
                $this->info("Would sync reservation: {$specificId}");
            }
        } else {
            $this->info('Syncing all reservations...');

            // Get reservations that need sync
            $reservations = InnochannelReservation::getReservations([
                'needs_sync' => true,
                'limit' => 100
            ]);

            $this->info("Found " . count($reservations) . " reservations to sync");

            if (!$isDryRun) {
                $bar = $this->output->createProgressBar(count($reservations));
                $bar->start();

                foreach ($reservations as $reservation) {
                    try {
                        $result = InnochannelReservation::syncWithPms($reservation->id);
                        $bar->advance();
                    } catch (Exception $e) {
                        $this->error("Failed to sync reservation {$reservation->id}: {$e->getMessage()}");
                    }
                }

                $bar->finish();
                $this->info('');
            }
        }

        $this->info('✓ Reservation sync completed');
        return self::SUCCESS;
    }

    /**
     * Sync properties with PMS.
     */
    protected function syncProperties(bool $isDryRun): int
    {
        $specificId = $this->option('id');

        if ($specificId) {
            $this->info("Syncing property: {$specificId}");

            if (!$isDryRun) {
                $result = InnochannelProperty::syncWithPms($specificId);
                $this->displaySyncResult('Property', $specificId, $result);
            } else {
                $this->info("Would sync property: {$specificId}");
            }
        } else {
            $this->info('Syncing all properties...');

            $properties = InnochannelProperty::getProperties(['limit' => 50]);
            $this->info("Found " . count($properties) . " properties to sync");

            if (!$isDryRun) {
                $bar = $this->output->createProgressBar(count($properties));
                $bar->start();

                foreach ($properties as $property) {
                    try {
                        $result = InnochannelProperty::syncWithPms($property->id);
                        $bar->advance();
                    } catch (Exception $e) {
                        $this->error("Failed to sync property {$property->id}: {$e->getMessage()}");
                    }
                }

                $bar->finish();
                $this->info('');
            }
        }

        $this->info('✓ Property sync completed');
        return self::SUCCESS;
    }

    /**
     * Sync inventory with PMS.
     */
    protected function syncInventory(bool $isDryRun): int
    {
        $propertyId = $this->option('property-id');

        if (!$propertyId) {
            $this->error('Property ID is required for inventory sync. Use --property-id option.');
            return self::FAILURE;
        }

        $this->info("Syncing inventory for property: {$propertyId}");

        if (!$isDryRun) {
            $result = InnochannelInventory::syncWithPms($propertyId);
            $this->displaySyncResult('Inventory', $propertyId, $result);
        } else {
            $this->info("Would sync inventory for property: {$propertyId}");
        }

        $this->info('✓ Inventory sync completed');
        return self::SUCCESS;
    }

    /**
     * Sync all data types.
     */
    protected function syncAll(bool $isDryRun): int
    {
        $this->info('Starting full sync...');

        // Sync properties first
        $this->syncProperties($isDryRun);

        // Then sync reservations
        $this->syncReservations($isDryRun);

        // Finally sync inventory for each property
        if (!$isDryRun) {
            $properties = InnochannelProperty::getProperties(['limit' => 50]);

            foreach ($properties as $property) {
                try {
                    InnochannelInventory::syncWithPms($property->id);
                } catch (Exception $e) {
                    $this->error("Failed to sync inventory for property {$property->id}: {$e->getMessage()}");
                }
            }
        }

        $this->info('✓ Full sync completed');
        return self::SUCCESS;
    }

    /**
     * Display sync result.
     */
    protected function displaySyncResult(string $type, string $id, array $result): void
    {
        if ($result['success'] ?? false) {
            $this->info("✓ {$type} {$id} synced successfully");
        } else {
            $this->error("✗ {$type} {$id} sync failed: " . ($result['error'] ?? 'Unknown error'));
        }
    }
}
