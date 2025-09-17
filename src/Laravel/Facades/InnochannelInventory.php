<?php

namespace Innochannel\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array getInventory(string $propertyId, array $filters = [])
 * @method static array updateInventory(string $propertyId, array $data)
 * @method static array updateRates(string $propertyId, array $data)
 * @method static array updateAvailability(string $propertyId, array $data)
 * @method static array syncWithPms(string $propertyId, array $options = [])
 * @method static bool validateInventoryData(array $data)
 * @method static array getBulkInventory(array $propertyIds, array $filters = [])
 * @method static array updateBulkInventory(array $data)
 * 
 * @see \Innochannel\Services\InventoryService
 */
class InnochannelInventory extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'innochannel.inventory';
    }
}