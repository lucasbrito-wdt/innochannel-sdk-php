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

        return Property::fromArray($response['property']);
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
            // Campos obrigatórios para criação - aceita tanto 'name' quanto 'property_name'
            $propertyName = $data['property_name'] ?? $data['name'] ?? null;
            if (empty($propertyName)) {
                $errors['property_name'] = ['Property name is required'];
            }

            // Para compatibilidade com API PMS, verifica property_id_in_pms se fornecido
            if (isset($data['property_id_in_pms']) && empty($data['property_id_in_pms'])) {
                $errors['property_id_in_pms'] = ['Property ID in PMS cannot be empty'];
            }

            // Campos obrigatórios apenas se fornecidos (para compatibilidade)
            if (isset($data['address']) && empty($data['address'])) {
                $errors['address'] = ['Address cannot be empty'];
            }

            if (isset($data['city']) && empty($data['city'])) {
                $errors['city'] = ['City cannot be empty'];
            }

            if (isset($data['country']) && empty($data['country'])) {
                $errors['country'] = ['Country cannot be empty'];
            }
        }

        // Validações de formato e tamanho - aceita tanto 'name' quanto 'property_name'
        $propertyName = $data['property_name'] ?? $data['name'] ?? null;
        if (isset($propertyName)) {
            if (is_string($propertyName)) {
                if (strlen($propertyName) < 2) {
                    $errors['property_name'] = ['Property name must be at least 2 characters'];
                }
                if (strlen($propertyName) > 255) {
                    $errors['property_name'] = ['Property name must not exceed 255 characters'];
                }
            } else {
                $errors['property_name'] = ['Property name must be a string'];
            }
        }

        if (isset($data['address'])) {
            if (is_string($data['address']) && strlen($data['address']) > 255) {
                $errors['address'] = ['Address must not exceed 255 characters'];
            } elseif (!is_string($data['address'])) {
                $errors['address'] = ['Address must be a string'];
            }
        }

        if (isset($data['city'])) {
            if (is_string($data['city']) && strlen($data['city']) > 255) {
                $errors['city'] = ['City must not exceed 255 characters'];
            } elseif (!is_string($data['city'])) {
                $errors['city'] = ['City must be a string'];
            }
        }

        if (isset($data['country'])) {
            if (is_string($data['country']) && strlen($data['country']) > 255) {
                $errors['country'] = ['Country must not exceed 255 characters'];
            } elseif (!is_string($data['country'])) {
                $errors['country'] = ['Country must be a string'];
            }
        }

        if (isset($data['phone'])) {
            if (is_string($data['phone']) && strlen($data['phone']) > 255) {
                $errors['phone'] = ['Phone must not exceed 255 characters'];
            } elseif (!is_string($data['phone'])) {
                $errors['phone'] = ['Phone must be a string'];
            }
        }

        if (isset($data['email'])) {
            if (is_string($data['email'])) {
                if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                    $errors['email'] = ['Invalid email format'];
                }
                if (strlen($data['email']) > 255) {
                    $errors['email'] = ['Email must not exceed 255 characters'];
                }
            } else {
                $errors['email'] = ['Email must be a string'];
            }
        }

        if (isset($data['website'])) {
            if (is_string($data['website'])) {
                if (!filter_var($data['website'], FILTER_VALIDATE_URL)) {
                    $errors['website'] = ['Invalid website URL format'];
                }
                if (strlen($data['website']) > 255) {
                    $errors['website'] = ['Website must not exceed 255 characters'];
                }
            } else {
                $errors['website'] = ['Website must be a string'];
            }
        }

        // Validação de quartos se fornecidos
        if (isset($data['rooms'])) {
            if (!is_array($data['rooms']) || empty($data['rooms'])) {
                $errors['rooms'] = ['Rooms must be a non-empty array'];
            } else {
                foreach ($data['rooms'] as $index => $room) {
                    if (empty($room['id'])) {
                        $errors["rooms.{$index}.id"] = ['Room ID is required'];
                    }

                    if (empty($room['name'])) {
                        $errors["rooms.{$index}.name"] = ['Room name is required'];
                    } elseif (strlen($room['name']) > 255) {
                        $errors["rooms.{$index}.name"] = ['Room name must not exceed 255 characters'];
                    }

                    if (empty($room['rates']) || !is_array($room['rates'])) {
                        $errors["rooms.{$index}.rates"] = ['Room rates must be a non-empty array'];
                    } else {
                        foreach ($room['rates'] as $rateIndex => $rate) {
                            if (empty($rate['id'])) {
                                $errors["rooms.{$index}.rates.{$rateIndex}.id"] = ['Rate ID is required'];
                            }

                            if (empty($rate['name'])) {
                                $errors["rooms.{$index}.rates.{$rateIndex}.name"] = ['Rate name is required'];
                            } elseif (strlen($rate['name']) > 255) {
                                $errors["rooms.{$index}.rates.{$rateIndex}.name"] = ['Rate name must not exceed 255 characters'];
                            }
                        }
                    }
                }
            }
        }

        if (!empty($errors)) {
            // Criar uma mensagem mais específica baseada nos erros encontrados
            $errorSummary = [];
            foreach ($errors as $field => $fieldErrors) {
                $errorSummary[] = "{$field}: " . implode(', ', $fieldErrors);
            }

            $detailedMessage = 'Property validation failed. Errors: ' . implode('; ', $errorSummary);
            throw new ValidationException($detailedMessage, $errors);
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
            if (empty($data['room_name'])) {
                $errors['room_name'] = ['Room Name is required'];
            }

            if (empty($data['pms_room_id'])) {
                $errors['pms_room_id'] = ['Room Id is required'];
            }
        }

        if (!empty($errors)) {
            // Criar uma mensagem mais específica baseada nos erros encontrados
            $errorSummary = [];
            foreach ($errors as $field => $fieldErrors) {
                $errorSummary[] = "{$field}: " . implode(', ', $fieldErrors);
            }

            $detailedMessage = 'Room validation failed. Errors: ' . implode('; ', $errorSummary);
            throw new ValidationException($detailedMessage, $errors);
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
            // Criar uma mensagem mais específica baseada nos erros encontrados
            $errorSummary = [];
            foreach ($errors as $field => $fieldErrors) {
                $errorSummary[] = "{$field}: " . implode(', ', $fieldErrors);
            }

            $detailedMessage = 'Rate plan validation failed. Errors: ' . implode('; ', $errorSummary);
            throw new ValidationException($detailedMessage, $errors);
        }
    }
}
