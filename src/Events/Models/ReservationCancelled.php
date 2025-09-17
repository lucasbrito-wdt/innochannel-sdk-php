<?php

declare(strict_types=1);

namespace Innochannel\Sdk\Events\Models;

use Innochannel\Sdk\Models\Reservation;

/**
 * Evento disparado quando uma reserva é cancelada
 * 
 * @package Innochannel\Sdk\Events\Models
 * @author Innochannel SDK Team
 * @version 1.0.0
 */
class ReservationCancelled extends ReservationEvent
{
    public function __construct(
        Reservation $reservation,
        protected ?string $reason = null
    ) {
        parent::__construct($reservation, [
            'cancellation_reason' => $reason,
        ]);
    }

    public function getName(): string
    {
        return 'reservation.cancelled';
    }

    /**
     * Retorna o motivo do cancelamento
     */
    public function getCancellationReason(): ?string
    {
        return $this->reason;
    }
}
