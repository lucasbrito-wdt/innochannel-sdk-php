<?php

declare(strict_types=1);

namespace Innochannel\Sdk\Events\Models;

use Innochannel\Sdk\Models\Reservation;

/**
 * Evento disparado quando uma reserva é atualizada
 * 
 * @package Innochannel\Sdk\Events\Models
 * @author Innochannel SDK Team
 * @version 1.0.0
 */
class ReservationUpdated extends ReservationEvent
{
    public function __construct(
        Reservation $reservation,
        protected array $originalData = [],
        protected array $changedFields = []
    ) {
        parent::__construct($reservation, [
            'original_data' => $originalData,
            'changed_fields' => $changedFields,
        ]);
    }


    public function getName(): string
    {
        return 'reservation.updated';
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
