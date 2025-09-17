<?php

declare(strict_types=1);

namespace Innochannel\Sdk\Events\Models;

use Innochannel\Sdk\Events\AbstractEvent;
use Innochannel\Sdk\Models\RatePlan;

/**
 * Evento base para eventos relacionados a planos de tarifas
 * 
 * @package Innochannel\Sdk\Events\Models
 * @author Innochannel SDK Team
 * @version 1.0.0
 */
abstract class RatePlanEvent extends AbstractEvent
{
    public function __construct(
        protected RatePlan $ratePlan,
        array $additionalData = []
    ) {
        $data = array_merge([
            'rate_plan' => $ratePlan,
            'rate_plan_id' => $ratePlan->getId(),
            'property_id' => $ratePlan->getPropertyId(),
            'rate_plan_name' => $ratePlan->getName(),
            'currency' => $ratePlan->getCurrency(),
            'rate_type' => $ratePlan->getRateType(),
            'is_active' => $ratePlan->isActive(),
            'is_refundable' => $ratePlan->isRefundable(),
        ], $additionalData);
        
        parent::__construct($data);
    }
    
    /**
     * Retorna a instância do plano de tarifas
     */
    public function getRatePlan(): RatePlan
    {
        return $this->ratePlan;
    }
}

/**
 * Evento disparado quando um plano de tarifas é criado
 */
class RatePlanCreated extends RatePlanEvent
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

/**
 * Evento disparado quando um plano de tarifas é atualizado
 */
class RatePlanUpdated extends RatePlanEvent
{
    public function __construct(
        RatePlan $ratePlan,
        protected array $originalData = [],
        protected array $changedFields = []
    ) {
        parent::__construct($ratePlan, [
            'original_data' => $originalData,
            'changed_fields' => $changedFields,
        ]);
    }
    
    public function getName(): string
    {
        return 'rate_plan.updated';
    }
    
    public function getEventName(): string
    {
        return 'rate_plan.updated';
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

/**
 * Evento disparado quando um plano de tarifas é ativado
 */
class RatePlanActivated extends RatePlanEvent
{
    public function getName(): string
    {
        return 'rate_plan.activated';
    }
    
    public function getEventName(): string
    {
        return 'rate_plan.activated';
    }
}

/**
 * Evento disparado quando um plano de tarifas é desativado
 */
class RatePlanDeactivated extends RatePlanEvent
{
    public function getName(): string
    {
        return 'rate_plan.deactivated';
    }
    
    public function getEventName(): string
    {
        return 'rate_plan.deactivated';
    }
}

/**
 * Evento disparado quando um plano de tarifas é deletado
 */
class RatePlanDeleted extends RatePlanEvent
{
    public function getName(): string
    {
        return 'rate_plan.deleted';
    }
    
    public function getEventName(): string
    {
        return 'rate_plan.deleted';
    }
}

/**
 * Evento disparado quando as restrições de um plano de tarifas são atualizadas
 */
class RatePlanRestrictionsUpdated extends RatePlanEvent
{
    public function __construct(
        RatePlan $ratePlan,
        protected array $oldRestrictions = []
    ) {
        parent::__construct($ratePlan, [
            'old_restrictions' => $oldRestrictions,
            'new_restrictions' => $ratePlan->getRestrictions(),
        ]);
    }
    
    public function getName(): string
    {
        return 'rate_plan.restrictions_updated';
    }
    
    public function getEventName(): string
    {
        return 'rate_plan.restrictions_updated';
    }
    
    /**
     * Retorna as restrições antigas
     * 
     * @return array<string, mixed>
     */
    public function getOldRestrictions(): array
    {
        return $this->oldRestrictions;
    }
}

/**
 * Evento disparado quando a política de cancelamento é atualizada
 */
class RatePlanCancellationPolicyUpdated extends RatePlanEvent
{
    public function __construct(
        RatePlan $ratePlan,
        protected array $oldPolicy = []
    ) {
        parent::__construct($ratePlan, [
            'old_cancellation_policy' => $oldPolicy,
            'new_cancellation_policy' => $ratePlan->getCancellationPolicy(),
        ]);
    }
    
    public function getName(): string
    {
        return 'rate_plan.cancellation_policy_updated';
    }
    
    public function getEventName(): string
    {
        return 'rate_plan.cancellation_policy_updated';
    }
    
    /**
     * Retorna a política de cancelamento antiga
     * 
     * @return array<string, mixed>
     */
    public function getOldCancellationPolicy(): array
    {
        return $this->oldPolicy;
    }
}