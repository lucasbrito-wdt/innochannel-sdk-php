<?php

declare(strict_types=1);

namespace Innochannel\Sdk\Events;

/**
 * Interface EventInterface
 * 
 * @package Innochannel\Sdk\Events
 * @author Innochannel SDK
 * @version 1.0.0
 */
interface EventInterface
{
    /**
     * Obter nome do evento
     * 
     * @return string
     */
    public function getName(): string;
    
    /**
     * Obter dados do evento
     * 
     * @return array<string, mixed>
     */
    public function getData(): array;
}