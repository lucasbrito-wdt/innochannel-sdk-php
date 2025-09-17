<?php

namespace Innochannel\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Innochannel\Models\Property[] getProperties(array $filters = [])
 * @method static \Innochannel\Models\Property getProperty(string $id)
 * @method static \Innochannel\Models\Property createProperty(array $data)
 * @method static \Innochannel\Models\Property updateProperty(string $id, array $data)
 * @method static bool deleteProperty(string $id)
 * @method static array syncWithPms(string $id, array $options = [])
 * @method static bool validatePropertyData(array $data)
 * @method static \Innochannel\Models\Room[] getRooms(string $propertyId)
 * @method static \Innochannel\Models\RatePlan[] getRatePlans(string $propertyId)
 * 
 * @see \Innochannel\Services\PropertyService
 */
class InnochannelProperty extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'innochannel.property';
    }
}