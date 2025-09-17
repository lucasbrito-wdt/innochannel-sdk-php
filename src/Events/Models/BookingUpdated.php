<?php

declare(strict_types=1);

namespace Innochannel\Sdk\Events\Models;

use Innochannel\Sdk\Models\Booking;

/**
 * Evento disparado quando uma reserva é atualizada
 * 
 * @package Innochannel\Sdk\Events\Models
 * @author Innochannel SDK Team
 * @version 1.0.0
 */
class BookingUpdated extends BookingEvent
{
    public function __construct(
        Booking $booking,
        protected array $originalData = [],
        protected array $changedFields = []
    ) {
        parent::__construct($booking, [
            'original_data' => $originalData,
            'changed_fields' => $changedFields,
        ]);
    }
    
    public function getName(): string
    {
        return 'booking.updated';
    }
    
    /**
     * Retorna os dados originais antes da atualização
     * 
     * @return array<string, mixed>
     */
    public function getOriginalData(): array
    {
        return $this->originalData;
    }
    
    /**
     * Retorna os campos que foram alterados
     * 
     * @return array<string>
     */
    public function getChangedFields(): array
    {
        return $this->changedFields;
    }
}