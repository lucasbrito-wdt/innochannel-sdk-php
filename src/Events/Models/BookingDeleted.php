<?php

declare(strict_types=1);

namespace Innochannel\Sdk\Events\Models;

use Innochannel\Sdk\Models\Booking;

/**
 * Evento disparado quando uma reserva é deletada
 * 
 * @package Innochannel\Sdk\Events\Models
 * @author Innochannel SDK Team
 * @version 1.0.0
 */
class BookingDeleted extends BookingEvent
{
    public function getName(): string
    {
        return 'booking.deleted';
    }
}