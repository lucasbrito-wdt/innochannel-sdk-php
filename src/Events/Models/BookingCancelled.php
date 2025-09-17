<?php

declare(strict_types=1);

namespace Innochannel\Sdk\Events\Models;

use Innochannel\Sdk\Models\Booking;

/**
 * Evento disparado quando uma reserva é cancelada
 * 
 * @package Innochannel\Sdk\Events\Models
 * @author Innochannel SDK Team
 * @version 1.0.0
 */
class BookingCancelled extends BookingEvent
{
    public function __construct(
        Booking $booking,
        protected ?string $reason = null
    ) {
        parent::__construct($booking, [
            'cancellation_reason' => $reason,
        ]);
    }
    
    public function getName(): string
    {
        return 'booking.cancelled';
    }
    
    /**
     * Retorna o motivo do cancelamento
     */
    public function getCancellationReason(): ?string
    {
        return $this->reason;
    }
}