<?php

namespace Innochannel\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Innochannel\Models\Booking[] getBookings(array $filters = [])
 * @method static \Innochannel\Models\Booking getBooking(string $id)
 * @method static \Innochannel\Models\Booking createBooking(array $data)
 * @method static \Innochannel\Models\Booking updateBooking(string $id, array $data)
 * @method static \Innochannel\Models\Booking modifyBooking(string $id, array $data)
 * @method static bool cancelBooking(string $id, array $options = [])
 * @method static array syncWithPms(string $id, array $options = [])
 * @method static bool validateBookingData(array $data)
 * @method static bool validateGuestData(array $data, bool $isUpdate = false)
 * @method static bool validateModificationData(array $data)
 * 
 * @see \Innochannel\Services\BookingService
 */
class InnochannelBooking extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'innochannel.booking';
    }
}