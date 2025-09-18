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
     * Cria uma nova propriedade no sistema com validação completa dos dados.
     * Aceita tanto 'name' quanto 'property_name' como nome da propriedade para
     * compatibilidade com diferentes sistemas PMS.
     * 
     * @param array $propertyData Dados da propriedade contendo:
     *                           - property_name|name (string): Nome da propriedade (obrigatório)
     *                           - property_id_in_pms (string): ID da propriedade no PMS (opcional)
     *                           - address (string): Endereço da propriedade (opcional)
     *                           - city (string): Cidade (opcional)
     *                           - country (string): País (opcional)
     *                           - phone (string): Telefone (opcional)
     *                           - email (string): Email (opcional)
     *                           - website (string): Website (opcional)
     *                           - rooms (array): Lista de quartos (opcional)
     * @return Property Objeto Property criado com todos os dados
     * @throws ApiException Se houver erro na comunicação com a API
     * @throws ValidationException Se os dados fornecidos forem inválidos
     * 
     * @example
     * // Criar propriedade básica
     * $property = $propertyService->create([
     *     'property_name' => 'Hotel Exemplo',
     *     'address' => 'Rua das Flores, 123',
     *     'city' => 'São Paulo',
     *     'country' => 'Brasil'
     * ]);
     * 
     * // Criar propriedade com quartos
     * $property = $propertyService->create([
     *     'name' => 'Pousada Vista Mar',
     *     'property_id_in_pms' => 'PMS_PROP_001',
     *     'rooms' => [
     *         [
     *             'id' => 'R001',
     *             'name' => 'Quarto Standard',
     *             'rates' => [
     *                 ['id' => 'RATE1', 'name' => 'Tarifa Padrão']
     *             ]
     *         ]
     *     ]
     * ]);
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
     * Recupera uma propriedade específica usando seu ID único.
     * Retorna todos os dados da propriedade incluindo informações
     * básicas, endereço e configurações.
     * 
     * @param int|string $propertyId ID único da propriedade (pode ser numérico ou string)
     * @return Property Objeto Property com todos os dados da propriedade
     * @throws ApiException Se a propriedade não for encontrada ou houver erro na API
     * 
     * @example
     * // Obter propriedade por ID numérico
     * $property = $propertyService->get(123);
     * 
     * // Obter propriedade por ID string
     * $property = $propertyService->get('PROP_ABC123');
     * 
     * // Acessar dados da propriedade
     * echo $property->getName();
     * echo $property->getAddress();
     */
    public function get($propertyId): Property
    {
        $response = $this->client->get("/api/pms/properties/{$propertyId}");

        return Property::fromArray($response['data']);
    }

    /**
     * Listar todas as propriedades
     * 
     * Recupera uma lista de propriedades com opção de aplicar filtros
     * para refinar os resultados. Suporta paginação e ordenação.
     * 
     * @param array $filters Filtros opcionais para a busca:
     *                      - city (string): Filtrar por cidade
     *                      - country (string): Filtrar por país
     *                      - status (string): Filtrar por status ('active', 'inactive')
     *                      - name (string): Buscar por nome (busca parcial)
     *                      - limit (int): Limite de resultados por página
     *                      - offset (int): Deslocamento para paginação
     *                      - sort_by (string): Campo para ordenação
     *                      - sort_order (string): Ordem ('asc', 'desc')
     * @return array Array de objetos Property
     * @throws ApiException Se houver erro na comunicação com a API
     * 
     * @example
     * // Listar todas as propriedades
     * $properties = $propertyService->list();
     * 
     * // Listar com filtros
     * $properties = $propertyService->list([
     *     'city' => 'São Paulo',
     *     'status' => 'active',
     *     'limit' => 10
     * ]);
     * 
     * // Listar com ordenação
     * $properties = $propertyService->list([
     *     'sort_by' => 'name',
     *     'sort_order' => 'asc'
     * ]);
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

        return Room::fromArray($response['room']);
    }

    /**
     * Listar quartos de uma propriedade
     * 
     * @param int|string $propertyId
     * @param array $filters
     * @return array
     * @throws ApiException
     */
    #[ApiException('This endpoint is temporarily disabled')]
    public function listRooms($propertyId, array $filters = []): array
    {
        $response = $this->client->get("/api/pms/properties/{$propertyId}/rooms", $filters);

        return array_map(
            fn($roomData) => Room::fromArray($roomData),
            $response['rooms']
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
    #[ApiException('This endpoint is temporarily disabled')]
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
    #[ApiException('This endpoint is temporarily disabled')]
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
        throw new ApiException('This endpoint is temporarily disabled');

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

        return RatePlan::fromArray($response['rate_plan']);
    }

    /**
     * Listar planos de tarifas de uma propriedade
     * 
     * @param int|string $propertyId
     * @param array $filters
     * @return array
     * @throws ApiException
     */
    #[ApiException('This endpoint is temporarily disabled')]
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
    #[ApiException('This endpoint is temporarily disabled')]
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
    #[ApiException('This endpoint is temporarily disabled')]
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
    #[ApiException('This endpoint is temporarily disabled')]
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
    #[ApiException('This endpoint is temporarily disabled')]
    public function deleteRatePlan($propertyId, $ratePlanId): bool
    {
        $this->client->delete("/api/pms/properties/{$propertyId}/rate-plans/{$ratePlanId}");

        return true;
    }

    /**
     * Testar conexão PMS
     * 
     * Testa a conectividade e autenticação com um sistema PMS específico.
     * Útil para validar credenciais e configurações antes de realizar
     * operações de sincronização.
     * 
     * @param array $connectionData Dados de conexão contendo:
     *                             - pms_type (string): Tipo do PMS ('opera', 'fidelio', 'mews', etc.)
     *                             - host (string): Endereço do servidor PMS
     *                             - username (string): Nome de usuário
     *                             - password (string): Senha
     *                             - port (int): Porta de conexão (opcional)
     *                             - database (string): Nome do banco de dados (opcional)
     *                             - ssl (bool): Usar SSL/TLS (opcional)
     *                             - timeout (int): Timeout em segundos (opcional)
     * @return array Resultado do teste contendo:
     *               - success (bool): Status da conexão
     *               - message (string): Mensagem descritiva
     *               - pms_version (string): Versão do PMS detectada
     *               - response_time (float): Tempo de resposta em ms
     *               - features (array): Recursos disponíveis no PMS
     * @throws ApiException Se houver erro na comunicação com a API
     * 
     * @example
     * // Testar conexão Opera
     * $result = $propertyService->testPmsConnection([
     *     'pms_type' => 'opera',
     *     'host' => '192.168.1.100',
     *     'username' => 'api_user',
     *     'password' => 'secure_password',
     *     'port' => 8080
     * ]);
     * 
     * // Verificar resultado
     * if ($result['success']) {
     *     echo "Conexão estabelecida com sucesso!";
     *     echo "Versão do PMS: " . $result['pms_version'];
     * }
     */
    public function testPmsConnection(array $connectionData): array
    {
        return $this->client->post('/api/pms/test-connection', $connectionData);
    }

    /**
     * Sincronizar disponibilidade com PMS
     * 
     * Este método permite sincronizar a disponibilidade de quartos e tarifas
     * de uma propriedade específica com o sistema PMS.
     * 
     * @param int|string $propertyIdInPMS ID da propriedade no sistema PMS
     * @param array $syncOptions Opções de sincronização
     *                          - direction (string): Direção da sincronização ('pull', 'push', 'both')
     *                          - date_from (string): Data inicial no formato Y-m-d
     *                          - date_to (string): Data final no formato Y-m-d
     *                          - room_types (array): Lista de tipos de quarto para sincronizar
     *                          - rate_plans (array): Lista de planos de tarifa para sincronizar
     * @return array Resultado da sincronização contendo:
     *               - success (bool): Status da operação
     *               - synced_records (int): Número de registros sincronizados
     *               - errors (array): Lista de erros, se houver
     *               - last_sync (string): Timestamp da última sincronização
     * @throws ApiException Se houver erro na comunicação com a API
     * @throws ValidationException Se os dados fornecidos forem inválidos
     * 
     * @example
     * // Sincronizar disponibilidade básica
     * $result = $propertyService->syncAvailability('PROP123');
     * 
     * // Sincronizar com opções específicas
     * $result = $propertyService->syncAvailability('PROP123', [
     *     'direction' => 'pull',
     *     'date_from' => '2024-01-01',
     *     'date_to' => '2024-01-31',
     *     'room_types' => ['standard', 'deluxe']
     * ]);
     */
    public function syncAvailability($propertyIdInPMS, array $syncOptions = []): array
    {
        $syncOptions['property_id_in_pms'] = $propertyIdInPMS;

        $this->validateAvailability($syncOptions);

        return $this->client->post("/api/pms/availability", $syncOptions);
    }

    /**
     * Sincronizar com PMS
     * 
     * Realiza sincronização bidirecional de dados entre o sistema Innochannel
     * e o PMS da propriedade. Permite sincronizar quartos, planos de tarifa
     * e disponibilidade de forma seletiva.
     * 
     * @param int|string $propertyId ID da propriedade no sistema Innochannel
     * @param array $syncOptions Opções de sincronização:
     *                          - direction (string): Direção da sincronização
     *                            * 'pull': Do PMS para Innochannel (padrão)
     *                            * 'push': Do Innochannel para PMS
     *                            * 'both': Sincronização bidirecional
     *                          - entities (array): Entidades a sincronizar (padrão: ['rooms', 'rate-plans', 'availability'])
     *                            * 'rooms': Quartos e tipos de quarto
     *                            * 'rate-plans': Planos de tarifa
     *                            * 'availability': Disponibilidade e tarifas
     *                            * 'reservations': Reservas
     *                          - date_from (string): Data inicial para sincronização (Y-m-d)
     *                          - date_to (string): Data final para sincronização (Y-m-d)
     *                          - force_update (bool): Forçar atualização mesmo se não houver mudanças
     *                          - batch_size (int): Tamanho do lote para processamento
     * @return array Resultado da sincronização contendo:
     *               - success (bool): Status geral da operação
     *               - entities_synced (array): Detalhes por entidade sincronizada
     *               - total_records (int): Total de registros processados
     *               - errors (array): Lista de erros encontrados
     *               - sync_duration (float): Duração da sincronização em segundos
     *               - last_sync_timestamp (string): Timestamp da sincronização
     * @throws ApiException Se houver erro na comunicação com a API
     * 
     * @example
     * // Sincronização básica (pull de todas as entidades)
     * $result = $propertyService->syncWithPms('PROP123');
     * 
     * // Sincronização específica de quartos e tarifas
     * $result = $propertyService->syncWithPms('PROP123', [
     *     'direction' => 'pull',
     *     'entities' => ['rooms', 'rate-plans'],
     *     'date_from' => '2024-01-01',
     *     'date_to' => '2024-01-31'
     * ]);
     * 
     * // Sincronização bidirecional com força
     * $result = $propertyService->syncWithPms('PROP123', [
     *     'direction' => 'both',
     *     'force_update' => true,
     *     'batch_size' => 100
     * ]);
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

        if (empty($data['property_id_in_pms'])) {
            $errors['property_id_in_pms'] = ['Property ID in PMS is required'];
        }

        if (empty($data['room_id'])) {
            $errors['room_id'] = ['Room ID is required'];
        }

        if (empty($data['rate_plan_id'])) {
            $errors['rate_plan_id'] = ['Rate plan ID is required'];
        }

        if (empty($data['rate_plan_name'])) {
            $errors['rate_plan_name'] = ['Rate plan name is required'];
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

    /**
     * Validar dados da disponibilidade
     * 
     * @param array $data
     * @throws ValidationException
     */
    private function validateAvailability(array $data): void
    {
        $errors = [];

        if (empty($data['property_id_in_pms'])) {
            $errors['property_id_in_pms'] = ['Property ID in PMS is required'];
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
