<?php

declare(strict_types=1);

namespace Innochannel\Sdk\Events\Models;

/**
 * Evento disparado quando uma reserva é deletada
 * 
 * @package Innochannel\Sdk\Events\Models
 * @author Innochannel SDK Team
 * @version 1.0.0
 */
class ReservationDeleted extends ReservationEvent
{
    public function getName(): string
    {
        return 'reservation.deleted';
    }
}
