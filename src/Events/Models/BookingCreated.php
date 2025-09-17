<?php

declare(strict_types=1);

namespace Innochannel\Sdk\Events\Models;

use Innochannel\Sdk\Models\Booking;

/**
 * Evento disparado quando uma reserva é criada
 * 
 * @package Innochannel\Sdk\Events\Models
 * @author Innochannel SDK Team
 * @version 1.0.0
 */
class BookingCreated extends BookingEvent
{
    public function getName(): string
    {
        return 'booking.created';
    }
}