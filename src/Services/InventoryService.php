<?php

declare(strict_types=1);

namespace Innochannel\Sdk\Services;

use Innochannel\Sdk\Client;
use Innochannel\Sdk\Exceptions\ApiException;
use Innochannel\Sdk\Exceptions\ValidationException;
use Innochannel\Sdk\Models\Inventory;
use DateTime;
use DateTimeInterface;

/**
 * Serviço para gerenciamento de inventário
 * 
 * @package Innochannel\Sdk\Services
 * @author Innochannel SDK Team
 * @version 1.0.0
 */
class InventoryService
{
    private Client $client;
    
    public function __construct(Client $client)
    {
        $this->client = $client;
    }
    
    /**
     * Atualizar disponibilidade
     * 
     * @param int $propertyId
     * @param array $availabilityData
     * @return array
     * @throws ApiException
     * @throws ValidationException
     */
    public function updateAvailability(int $propertyId, array $availabilityData): array
    {
        $this->validateAvailabilityData($availabilityData);
        
        return $this->client->post("/api/pms/properties/{$propertyId}/availability", $availabilityData);
    }
    
    /**
     * Consultar disponibilidade
     * 
     * @param int $propertyId
     * @param array $filters
     * @return array
     * @throws ApiException
     */
    public function getAvailability(int $propertyId, array $filters = []): array
    {
        return $this->client->get("/api/pms/properties/{$propertyId}/availability", $filters);
    }
    
    /**
     * Atualizar tarifas
     * 
     * @param int $propertyId
     * @param array $rateData
     * @return array
     * @throws ApiException
     * @throws ValidationException
     */
    public function updateRates(int $propertyId, array $rateData): array
    {
        $this->validateRateData($rateData);
        
        return $this->client->post("/api/pms/properties/{$propertyId}/rates", $rateData);
    }
    
    /**
     * Consultar tarifas
     * 
     * @param int $propertyId
     * @param array $filters
     * @return array
     * @throws ApiException
     */
    public function getRates(int $propertyId, array $filters = []): array
    {
        return $this->client->get("/api/pms/properties/{$propertyId}/rates", $filters);
    }
    
    /**
     * Atualizar disponibilidade e tarifas em lote
     * 
     * @param int $propertyId
     * @param array $batchData
     * @return array
     * @throws ApiException
     * @throws ValidationException
     */
    public function updateBatch(int $propertyId, array $batchData): array
    {
        $this->validateBatchData($batchData);
        
        return $this->client->post("/api/pms/properties/{$propertyId}/inventory/batch", $batchData);
    }
    
    /**
     * Obter calendário de disponibilidade
     * 
     * @param int $propertyId
     * @param string $dateFrom
     * @param string $dateTo
     * @param array $filters
     * @return array
     * @throws ApiException
     */
    public function getCalendar(int $propertyId, string $dateFrom, string $dateTo, array $filters = []): array
    {
        $params = array_merge($filters, [
            'date_from' => $dateFrom,
            'date_to' => $dateTo
        ]);
        
        return $this->client->get("/api/pms/properties/{$propertyId}/calendar", $params);
    }
    
    /**
     * Definir restrições de estadia
     * 
     * @param int $propertyId
     * @param array $restrictionData
     * @return array
     * @throws ApiException
     * @throws ValidationException
     */
    public function setRestrictions(int $propertyId, array $restrictionData): array
    {
        $this->validateRestrictionData($restrictionData);
        
        return $this->client->post("/api/pms/properties/{$propertyId}/restrictions", $restrictionData);
    }
    
    /**
     * Consultar restrições
     * 
     * @param int $propertyId
     * @param array $filters
     * @return array
     * @throws ApiException
     */
    public function getRestrictions(int $propertyId, array $filters = []): array
    {
        return $this->client->get("/api/pms/properties/{$propertyId}/restrictions", $filters);
    }
    
    /**
     * Sincronizar inventário com PMS
     * 
     * @param int $propertyId
     * @param array $syncOptions
     * @return array
     * @throws ApiException
     */
    public function syncWithPms(int $propertyId, array $syncOptions = []): array
    {
        return $this->client->post("/api/pms/properties/{$propertyId}/sync", $syncOptions);
    }
    
    /**
     * Validar dados de disponibilidade
     * 
     * @param array $data
     * @throws ValidationException
     */
    private function validateAvailabilityData(array $data): void
    {
        $errors = [];
        
        if (empty($data['room_id'])) {
            $errors['room_id'] = ['Room ID is required'];
        }
        
        if (empty($data['date_from'])) {
            $errors['date_from'] = ['Start date is required'];
        }
        
        if (empty($data['date_to'])) {
            $errors['date_to'] = ['End date is required'];
        }
        
        if (isset($data['availability']) && (!is_int($data['availability']) || $data['availability'] < 0)) {
            $errors['availability'] = ['Availability must be a non-negative integer'];
        }
        
        if (isset($data['date_from']) && isset($data['date_to'])) {
            $dateFrom = new DateTime($data['date_from']);
            $dateTo = new DateTime($data['date_to']);
            
            if ($dateFrom >= $dateTo) {
                $errors['date_to'] = ['End date must be after start date'];
            }
        }
        
        if (!empty($errors)) {
            throw new ValidationException('Availability validation failed', $errors);
        }
    }
    
    /**
     * Validar dados de tarifas
     * 
     * @param array $data
     * @throws ValidationException
     */
    private function validateRateData(array $data): void
    {
        $errors = [];
        
        if (empty($data['room_id'])) {
            $errors['room_id'] = ['Room ID is required'];
        }
        
        if (empty($data['rate_plan_id'])) {
            $errors['rate_plan_id'] = ['Rate plan ID is required'];
        }
        
        if (empty($data['date_from'])) {
            $errors['date_from'] = ['Start date is required'];
        }
        
        if (empty($data['date_to'])) {
            $errors['date_to'] = ['End date is required'];
        }
        
        if (isset($data['rate']) && (!is_numeric($data['rate']) || $data['rate'] < 0)) {
            $errors['rate'] = ['Rate must be a non-negative number'];
        }
        
        if (isset($data['date_from']) && isset($data['date_to'])) {
            $dateFrom = new DateTime($data['date_from']);
            $dateTo = new DateTime($data['date_to']);
            
            if ($dateFrom >= $dateTo) {
                $errors['date_to'] = ['End date must be after start date'];
            }
        }
        
        if (!empty($errors)) {
            throw new ValidationException('Rate validation failed', $errors);
        }
    }
    
    /**
     * Validar dados de lote
     * 
     * @param array $data
     * @throws ValidationException
     */
    private function validateBatchData(array $data): void
    {
        $errors = [];
        
        if (empty($data['updates']) || !is_array($data['updates'])) {
            $errors['updates'] = ['Updates array is required'];
            throw new ValidationException('Batch validation failed', $errors);
        }
        
        foreach ($data['updates'] as $index => $update) {
            if (empty($update['type']) || !in_array($update['type'], ['availability', 'rate'])) {
                $errors["updates.{$index}.type"] = ['Update type must be "availability" or "rate"'];
            }
            
            if ($update['type'] === 'availability') {
                try {
                    $this->validateAvailabilityData($update);
                } catch (ValidationException $e) {
                    foreach ($e->getErrors() as $field => $fieldErrors) {
                        $errors["updates.{$index}.{$field}"] = $fieldErrors;
                    }
                }
            } elseif ($update['type'] === 'rate') {
                try {
                    $this->validateRateData($update);
                } catch (ValidationException $e) {
                    foreach ($e->getErrors() as $field => $fieldErrors) {
                        $errors["updates.{$index}.{$field}"] = $fieldErrors;
                    }
                }
            }
        }
        
        if (!empty($errors)) {
            throw new ValidationException('Batch validation failed', $errors);
        }
    }
    
    /**
     * Validar dados de restrições
     * 
     * @param array $data
     * @throws ValidationException
     */
    private function validateRestrictionData(array $data): void
    {
        $errors = [];
        
        if (empty($data['room_id'])) {
            $errors['room_id'] = ['Room ID is required'];
        }
        
        if (empty($data['date_from'])) {
            $errors['date_from'] = ['Start date is required'];
        }
        
        if (empty($data['date_to'])) {
            $errors['date_to'] = ['End date is required'];
        }
        
        if (isset($data['min_stay']) && (!is_int($data['min_stay']) || $data['min_stay'] < 1)) {
            $errors['min_stay'] = ['Minimum stay must be a positive integer'];
        }
        
        if (isset($data['max_stay']) && (!is_int($data['max_stay']) || $data['max_stay'] < 1)) {
            $errors['max_stay'] = ['Maximum stay must be a positive integer'];
        }
        
        if (isset($data['min_stay']) && isset($data['max_stay']) && $data['min_stay'] > $data['max_stay']) {
            $errors['max_stay'] = ['Maximum stay must be greater than or equal to minimum stay'];
        }
        
        if (!empty($errors)) {
            throw new ValidationException('Restriction validation failed', $errors);
        }
    }
}