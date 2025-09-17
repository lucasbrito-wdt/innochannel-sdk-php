<?php

namespace Innochannel\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Innochannel\Models\Reservation[] getReservations(array $filters = [])
 * @method static \Innochannel\Models\Reservation getReservation(string $id)
 * @method static \Innochannel\Models\Reservation createReservation(array $data)
 * @method static \Innochannel\Models\Reservation updateReservation(string $id, array $data)
 * @method static \Innochannel\Models\Reservation modifyReservation(string $id, array $data)
 * @method static bool cancelReservation(string $id, array $options = [])
 * @method static array syncWithPms(string $id, array $options = [])
 * @method static bool validateReservationData(array $data)
 * @method static bool validateGuestData(array $data, bool $isUpdate = false)
 * @method static bool validateModificationData(array $data)
 * 
 * @see \Innochannel\Services\ReservationService
 */
class InnochannelReservation extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'innochannel.reservation';
    }
}
