<?php

declare(strict_types=1);

namespace Innochannel\Sdk\Events\Models;

use Innochannel\Sdk\Models\RatePlan;

/**
 * Evento disparado quando um plano de tarifas é criado
 * 
 * @package Innochannel\Sdk\Events\Models
 * @author Innochannel SDK Team
 * @version 1.0.0
 */
class RatePlanCreatedEvent extends RatePlanEvent
{
    public function getName(): string
    {
        return 'rate_plan.created';
    }
    
    public function getEventName(): string
    {
        return 'rate_plan.created';
    }
}