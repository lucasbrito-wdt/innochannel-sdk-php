<?php

declare(strict_types=1);

namespace Innochannel\Sdk\Events\Models;

use Innochannel\Sdk\Events\AbstractEvent;
use Innochannel\Sdk\Models\Booking;

/**
 * Evento base para eventos relacionados a reservas
 * 
 * @package Innochannel\Sdk\Events\Models
 * @author Innochannel SDK Team
 * @version 1.0.0
 */
abstract class BookingEvent extends AbstractEvent
{
    public function __construct(
        protected Booking $booking,
        array $additionalData = []
    ) {
        $data = array_merge([
            'booking' => $booking,
            'booking_id' => $booking->getId(),
            'property_id' => $booking->getPropertyId(),
            'room_id' => $booking->getRoomId(),
            'status' => $booking->getStatus(),
        ], $additionalData);
        
        parent::__construct($data);
    }
    
    /**
     * Retorna a instância do booking
     */
    public function getBooking(): Booking
    {
        return $this->booking;
    }
}