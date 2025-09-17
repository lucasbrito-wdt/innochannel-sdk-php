<?php

declare(strict_types=1);

namespace Innochannel\Sdk\Events\Models;

use Innochannel\Sdk\Events\AbstractEvent;
use Innochannel\Sdk\Models\Reservation;

/**
 * Evento base para eventos relacionados a reservas
 * 
 * @package Innochannel\Sdk\Events\Models
 * @author Innochannel SDK Team
 * @version 1.0.0
 */
abstract class ReservationEvent extends AbstractEvent
{
    public function __construct(
        protected Reservation $reservation,
        array $additionalData = []
    ) {
        $data = array_merge([
            'booking' => $reservation,
            'booking_id' => $reservation->getId(),
            'property_id' => $reservation->getPropertyId(),
            'room_id' => $reservation->getRoomId(),
            'status' => $reservation->getStatus(),
        ], $additionalData);

        parent::__construct($data);
    }

    /**
     * Retorna a instância do reservation
     */
    public function getReservation(): Reservation
    {
        return $this->reservation;
    }
}
