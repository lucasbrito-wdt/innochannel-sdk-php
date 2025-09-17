<?php

namespace Innochannel\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Innochannel\Models\Reservation[] getReservations(array $filters = [])
 * @method static \Innochannel\Models\Reservation getReservation(string $id)
 * @method static \Innochannel\Models\Reservation createReservation(array $data)
 * @method static \Innochannel\Models\Reservation updateReservation(string $id, array $data)
 * @method static bool cancelReservation(string $id, array $options = [])
 * @method static array syncReservationWithPms(string $id, array $options = [])
 * @method static \Innochannel\Models\Property[] getProperties(array $filters = [])
 * @method static \Innochannel\Models\Property getProperty(string $id)
 * @method static \Innochannel\Models\Property updateProperty(string $id, array $data)
 * @method static array getInventory(string $propertyId, array $filters = [])
 * @method static array updateInventory(string $propertyId, array $data)
 * @method static array syncInventoryWithPms(string $propertyId, array $options = [])
 * @method static bool registerWebhook(string $url, array $events = [])
 * @method static bool unregisterWebhook(string $url)
 * @method static array getWebhooks()
 * @method static array testConnection()
 * 
 * @see \Innochannel\Client
 */
class Innochannel extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'innochannel';
    }
}
