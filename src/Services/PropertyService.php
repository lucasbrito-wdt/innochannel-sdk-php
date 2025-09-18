<?php

declare(strict_types=1);

namespace Innochannel\Sdk\Services;

use Innochannel\Sdk\Client;
use Innochannel\Sdk\Models\Property;
use Innochannel\Sdk\Models\Room;
use Innochannel\Sdk\Models\RatePlan;
use Innochannel\Sdk\Exceptions\ApiException;
use Innochannel\Sdk\Exceptions\ValidationException;

/**
 * Serviço para gerenciamento de propriedades
 * 
 * @package Innochannel\Sdk\Services
 * @author Innochannel SDK Team
 * @version 1.0.0
 */
class PropertyService
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Criar uma nova propriedade
     * 
     * @param array $propertyData Dados da propriedade
     * @return Property
     * @throws ApiException
     * @throws ValidationException
     */
    public function create(array $propertyData): Property
    {
        $this->validatePropertyData($propertyData);

        $response = $this->client->post('/api/pms/properties', $propertyData);

        return Property::fromArray($response['data']);
    }

    /**
     * Obter propriedade por ID
     * 
     * @param int|string $propertyId
     * @return Property
     * @throws ApiException
     */
    public function get($propertyId): Property
    {
        $response = $this->client->get("/api/pms/properties/{$propertyId}");

        return Property::fromArray($response['data']);
    }

    /**
     * Listar todas as propriedades
     * 
     * @param array $filters Filtros opcionais
     * @return array
     * @throws ApiException
     */
    public function list(array $filters = []): array
    {
        $response = $this->client->get('/api/pms/properties', $filters);

        return array_map(
            fn($propertyData) => Property::fromArray($propertyData),
            $response['data']
        );
    }

    /**
     * Atualizar propriedade
     * 
     * @param int|string $propertyId
     * @param array $propertyData
     * @return Property
     * @throws ApiException
     * @throws ValidationException
     */
    public function update($propertyId, array $propertyData): Property
    {
        $this->validatePropertyData($propertyData, false);

        $response = $this->client->put("/api/pms/properties/{$propertyId}", $propertyData);

        return Property::fromArray($response['data']);
    }

    /**
     * Excluir propriedade
     * 
     * @param int|string $propertyId
     * @return bool
     * @throws ApiException
     */
    public function delete($propertyId): bool
    {
        $this->client->delete("/api/pms/properties/{$propertyId}");

        return true;
    }

    /**
     * Criar quarto para uma propriedade
     * 
     * @param int|string $propertyId
     * @param array $roomData
     * @return Room
     * @throws ApiException
     * @throws ValidationException
     */
    public function createRoom($propertyId, array $roomData): Room
    {
        $this->validateRoomData($roomData);

        $response = $this->client->post("/api/pms/properties/{$propertyId}/rooms", $roomData);

        return Room::fromArray($response['data']);
    }

    /**
     * Listar quartos de uma propriedade
     * 
     * @param int|string $propertyId
     * @param array $filters
     * @return array
     * @throws ApiException
     */
    public function listRooms($propertyId, array $filters = []): array
    {
        $response = $this->client->get("/api/pms/properties/{$propertyId}/rooms", $filters);

        return array_map(
            fn($roomData) => Room::fromArray($roomData),
            $response['data']
        );
    }

    /**
     * Obter quarto por ID
     * 
     * @param int|string $propertyId
     * @param int|string $roomId
     * @return Room
     * @throws ApiException
     */
    public function getRoom($propertyId, $roomId): Room
    {
        $response = $this->client->get("/api/pms/properties/{$propertyId}/rooms/{$roomId}");

        return Room::fromArray($response['data']);
    }

    /**
     * Listar quartos de uma propriedade (método legado)
     * 
     * @param int|string $propertyId
     * @return array
     * @throws ApiException
     */
    public function getRooms($propertyId): array
    {
        $response = $this->client->get("/api/pms/properties/{$propertyId}/rooms");

        return array_map(
            fn($roomData) => Room::fromArray($roomData),
            $response['data']
        );
    }

    /**
     * Atualizar quarto
     * 
     * @param int|string $propertyId
     * @param int|string $roomId
     * @param array $roomData
     * @return Room
     * @throws ApiException
     * @throws ValidationException
     */
    public function updateRoom($propertyId, $roomId, array $roomData): Room
    {
        $this->validateRoomData($roomData, false);

        $response = $this->client->put("/api/pms/properties/{$propertyId}/rooms/{$roomId}", $roomData);

        return Room::fromArray($response['data']);
    }

    /**
     * Deletar quarto
     * 
     * @param int|string $propertyId
     * @param int|string $roomId
     * @return bool
     * @throws ApiException
     */
    public function deleteRoom($propertyId, $roomId): bool
    {
        $this->client->delete("/api/pms/properties/{$propertyId}/rooms/{$roomId}");

        return true;
    }

    /**
     * Criar plano de tarifas para uma propriedade
     * 
     * @param int|string $propertyId
     * @param array $ratePlanData
     * @return RatePlan
     * @throws ApiException
     * @throws ValidationException
     */
    public function createRatePlan($propertyId, array $ratePlanData): RatePlan
    {
        $this->validateRatePlanData($ratePlanData);

        $response = $this->client->post("/api/pms/properties/{$propertyId}/rate-plans", $ratePlanData);

        return RatePlan::fromArray($response['data']);
    }

    /**
     * Listar planos de tarifas de uma propriedade
     * 
     * @param int|string $propertyId
     * @param array $filters
     * @return array
     * @throws ApiException
     */
    public function listRatePlans($propertyId, array $filters = []): array
    {
        $response = $this->client->get("/api/pms/properties/{$propertyId}/rate-plans", $filters);

        return array_map(
            fn($ratePlanData) => RatePlan::fromArray($ratePlanData),
            $response['data']
        );
    }

    /**
     * Obter plano de tarifas por ID
     * 
     * @param int|string $propertyId
     * @param int|string $ratePlanId
     * @return RatePlan
     * @throws ApiException
     */
    public function getRatePlan($propertyId, $ratePlanId): RatePlan
    {
        $response = $this->client->get("/api/pms/properties/{$propertyId}/rate-plans/{$ratePlanId}");

        return RatePlan::fromArray($response['data']);
    }

    /**
     * Listar planos de tarifas de uma propriedade (método legado)
     * 
     * @param int|string $propertyId
     * @return array
     * @throws ApiException
     */
    public function getRatePlans($propertyId): array
    {
        $response = $this->client->get("/api/pms/properties/{$propertyId}/rate-plans");

        return array_map(
            fn($ratePlanData) => RatePlan::fromArray($ratePlanData),
            $response['data']
        );
    }

    /**
     * Atualizar plano de tarifas
     * 
     * @param int|string $propertyId
     * @param int|string $ratePlanId
     * @param array $ratePlanData
     * @return RatePlan
     * @throws ApiException
     * @throws ValidationException
     */
    public function updateRatePlan($propertyId, $ratePlanId, array $ratePlanData): RatePlan
    {
        $this->validateRatePlanData($ratePlanData);

        $response = $this->client->put("/api/pms/properties/{$propertyId}/rate-plans/{$ratePlanId}", $ratePlanData);

        return RatePlan::fromArray($response['data']);
    }

    /**
     * Deletar plano de tarifas
     * 
     * @param int|string $propertyId
     * @param int|string $ratePlanId
     * @return bool
     * @throws ApiException
     */
    public function deleteRatePlan($propertyId, $ratePlanId): bool
    {
        $this->client->delete("/api/pms/properties/{$propertyId}/rate-plans/{$ratePlanId}");

        return true;
    }

    /**
     * Testar conexão PMS
     * 
     * @param array $connectionData
     * @return array
     * @throws ApiException
     */
    public function testPmsConnection(array $connectionData): array
    {
        return $this->client->post('/api/pms/test-connection', $connectionData);
    }

    /**
     * Sincronizar com PMS
     * 
     * @param int|string $propertyId
     * @param array $syncOptions
     * @return array
     * @throws ApiException
     */
    public function syncWithPms($propertyId, array $syncOptions = []): array
    {
        $syncOptions['direction'] = $syncOptions['direction'] ?? 'pull';
        $syncOptions['entities'] = $syncOptions['entities'] ?? ['rooms', 'rate-plans', 'availability'];

        return $this->client->post("/api/pms/properties/{$propertyId}/sync", $syncOptions);
    }

    /**
     * Validar dados da propriedade
     * 
     * @param array $data
     * @param bool $isCreate
     * @throws ValidationException
     */
    private function validatePropertyData(array $data, bool $isCreate = true): void
    {
        $errors = [];

        if ($isCreate) {
            if (empty($data['property_name'])) {
                $errors['property_name'] = ['Property name is required'];
            }
        }

        if (isset($data['property_name']) && strlen($data['property_name']) < 2) {
            $errors['property_name'] = ['Property name must be at least 2 characters'];
        }

        if (isset($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = ['Invalid email format'];
        }

        if (!empty($errors)) {
            throw new ValidationException('Property validation failed', $errors);
        }
    }

    /**
     * Validar dados do quarto
     * 
     * @param array $data
     * @param bool $isCreate
     * @throws ValidationException
     */
    private function validateRoomData(array $data, bool $isCreate = true): void
    {
        $errors = [];

        if ($isCreate) {
            if (empty($data['name'])) {
                $errors['name'] = ['Room name is required'];
            }

            if (empty($data['room_type'])) {
                $errors['room_type'] = ['Room type is required'];
            }
        }

        if (isset($data['max_occupancy']) && (!is_int($data['max_occupancy']) || $data['max_occupancy'] < 1)) {
            $errors['max_occupancy'] = ['Max occupancy must be a positive integer'];
        }

        if (!empty($errors)) {
            throw new ValidationException('Room validation failed', $errors);
        }
    }

    /**
     * Validar dados do plano de tarifas
     * 
     * @param array $data
     * @throws ValidationException
     */
    private function validateRatePlanData(array $data): void
    {
        $errors = [];

        if (empty($data['name'])) {
            $errors['name'] = ['Rate plan name is required'];
        }

        if (empty($data['currency'])) {
            $errors['currency'] = ['Currency is required'];
        }

        if (isset($data['currency']) && !preg_match('/^[A-Z]{3}$/', $data['currency'])) {
            $errors['currency'] = ['Currency must be a valid 3-letter ISO code'];
        }

        if (!empty($errors)) {
            throw new ValidationException('Rate plan validation failed', $errors);
        }
    }
}
