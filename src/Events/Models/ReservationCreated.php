<?php

declare(strict_types=1);

namespace Innochannel\Sdk\Events\Models;

/**
 * Evento disparado quando uma reserva é criada
 * 
 * @package Innochannel\Sdk\Events\Models
 * @author Innochannel SDK Team
 * @version 1.0.0
 */
class ReservationCreated extends ReservationEvent
{
    public function getName(): string
    {
        return 'reservation.created';
    }
}
