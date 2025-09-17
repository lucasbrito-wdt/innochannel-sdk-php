<?php

declare(strict_types=1);

namespace Innochannel\Sdk\Events;

use DateTime;
use DateTimeInterface;

/**
 * Classe abstrata base para todos os eventos
 * 
 * @package Innochannel\Sdk\Events
 * @author Innochannel SDK Team
 * @version 1.0.0
 */
abstract class AbstractEvent implements EventInterface
{
    protected DateTimeInterface $timestamp;
    
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(
        protected array $data = []
    ) {
        $this->timestamp = new DateTime();
    }
    
    /**
     * {@inheritDoc}
     */
    public function getData(): array
    {
        return $this->data;
    }
    
    /**
     * Converte o evento para array
     * 
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'event_name' => $this->getName(),
            'timestamp' => $this->timestamp->format('c'),
            'data' => $this->data,
        ];
    }
}