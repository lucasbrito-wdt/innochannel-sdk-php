<?php

declare(strict_types=1);

namespace Innochannel\Sdk;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Client\RequestException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Innochannel\Sdk\Auth\AuthenticationInterface;
use Innochannel\Sdk\Auth\ApiKeyAuthentication;
use Innochannel\Sdk\Exceptions\ApiException;
use Innochannel\Sdk\Exceptions\AuthenticationException;
use Innochannel\Sdk\Exceptions\ValidationException;
use Innochannel\Sdk\Exceptions\NotFoundException;
use Innochannel\Sdk\Exceptions\RateLimitException;
use Innochannel\Sdk\Exceptions\InnochannelException;
use Innochannel\Sdk\Services\PropertyService;
use Innochannel\Sdk\Services\InventoryService;
use Innochannel\Sdk\Services\ReservationService;
use Innochannel\Sdk\Services\OtaConnectionService;
use Innochannel\Sdk\Services\MonitoringService;

/**
 * Cliente principal do SDK Innochannel
 * 
 * Esta classe fornece acesso aos serviços do Innochannel Channel Manager
 * 
 * @package Innochannel\Sdk
 * @author Innochannel SDK Team
 * @version 1.0.0
 */
class Client
{
    private const DEFAULT_BASE_URL = 'https://api.innotel.com.br';
    private const DEFAULT_TIMEOUT = 30;
    private const DEFAULT_CONNECT_TIMEOUT = 10;

    private AuthenticationInterface $auth;
    private LoggerInterface $logger;
    private string $baseUrl;
    private int $retryAttempts;
    private int $retryDelay;
    private int $timeout;
    private int $connectTimeout;

    // Services
    private ?PropertyService $propertyService = null;
    private ?InventoryService $inventoryService = null;
    private ?ReservationService $reservationService = null;
    private ?OtaConnectionService $otaConnectionService = null;
    private ?MonitoringService $monitoringService = null;

    /**
     * @param array $config Configuração do cliente
     * @throws ValidationException
     */
    public function __construct(array $config = [])
    {
        $this->validateConfig($config);

        $this->baseUrl = $config['base_url'] ?? self::DEFAULT_BASE_URL;
        $this->logger = $config['logger'] ?? new NullLogger();
        $this->retryAttempts = $config['retry_attempts'] ?? 0;
        $this->retryDelay = $config['retry_delay'] ?? 1000;
        $this->timeout = $config['timeout'] ?? self::DEFAULT_TIMEOUT;
        $this->connectTimeout = $config['connect_timeout'] ?? self::DEFAULT_CONNECT_TIMEOUT;

        // Configurar autenticação
        $this->auth = $this->createAuthentication($config);
    }

    /**
     * Serviço de propriedades
     */
    public function properties(): PropertyService
    {
        if ($this->propertyService === null) {
            $this->propertyService = new PropertyService($this);
        }

        return $this->propertyService;
    }

    /**
     * Serviço de inventário (disponibilidade e tarifas)
     */
    public function inventory(): InventoryService
    {
        if ($this->inventoryService === null) {
            $this->inventoryService = new InventoryService($this);
        }

        return $this->inventoryService;
    }

    /**
     * Serviço de reservas
     */
    public function reservations(): ReservationService
    {
        if ($this->reservationService === null) {
            $this->reservationService = new ReservationService($this);
        }

        return $this->reservationService;
    }

    /**
     * Serviço de conexões OTA
     */
    public function otaConnections(): OtaConnectionService
    {
        if ($this->otaConnectionService === null) {
            $this->otaConnectionService = new OtaConnectionService($this);
        }

        return $this->otaConnectionService;
    }

    /**
     * Serviço de monitoramento
     */
    public function monitoring(): MonitoringService
    {
        if ($this->monitoringService === null) {
            $this->monitoringService = new MonitoringService($this);
        }

        return $this->monitoringService;
    }

    /**
     * Obter propriedades (método de conveniência)
     * 
     * @param array $filters Filtros opcionais
     * @return array
     * @throws ApiException
     */
    public function getProperties(array $filters = []): array
    {
        return $this->properties()->list($filters);
    }

    /**
     * Obter uma propriedade específica (método de conveniência)
     * 
     * @param string $id ID da propriedade
     * @return \Innochannel\Sdk\Models\Property
     * @throws ApiException
     */
    public function getProperty(string $id): \Innochannel\Sdk\Models\Property
    {
        return $this->properties()->get($id);
    }

    /**
     * Atualizar uma propriedade (método de conveniência)
     * 
     * @param string $id ID da propriedade
     * @param array $data Dados para atualização
     * @return \Innochannel\Sdk\Models\Property
     * @throws ApiException
     */
    public function updateProperty(string $id, array $data): \Innochannel\Sdk\Models\Property
    {
        return $this->properties()->update($id, $data);
    }

    /**
     * Obter inventário (método de conveniência)
     * 
     * @param string $propertyId ID da propriedade
     * @param array $filters Filtros opcionais
     * @return array
     * @throws ApiException
     */
    public function getInventory(string $propertyId, array $filters = []): array
    {
        return $this->inventory()->getAvailability((int)$propertyId, $filters);
    }

    /**
     * Atualizar inventário (método de conveniência)
     * 
     * @param string $propertyId ID da propriedade
     * @param array $data Dados do inventário
     * @return array
     * @throws ApiException
     */
    public function updateInventory(string $propertyId, array $data): array
    {
        return $this->inventory()->updateAvailability((int)$propertyId, $data);
    }

    /**
     * Sincronizar inventário com PMS (método de conveniência)
     * 
     * @param string $propertyId ID da propriedade
     * @param array $options Opções de sincronização
     * @return array
     * @throws ApiException
     */
    public function syncInventoryWithPms(string $propertyId, array $options = []): array
    {
        return $this->inventory()->syncWithPms((int)$propertyId, $options);
    }

    /**
     * Registrar webhook (método de conveniência)
     * 
     * @param string $url URL do webhook
     * @param array $events Eventos para escutar
     * @return bool
     * @throws ApiException
     */
    public function registerWebhook(string $url, array $events = []): bool
    {
        $webhookService = new \Innochannel\Sdk\Services\WebhookService($this);
        $result = $webhookService->create(['url' => $url, 'events' => $events]);
        return !empty($result);
    }

    /**
     * Cancelar registro de webhook (método de conveniência)
     * 
     * @param string $url URL do webhook
     * @return bool
     * @throws ApiException
     */
    public function unregisterWebhook(string $url): bool
    {
        $webhookService = new \Innochannel\Sdk\Services\WebhookService($this);
        // Primeiro buscar o webhook pela URL
        $webhooks = $webhookService->list(['url' => $url]);
        if (!empty($webhooks)) {
            foreach ($webhooks as $webhook) {
                if (isset($webhook['url']) && $webhook['url'] === $url) {
                    return $webhookService->delete($webhook['id']);
                }
            }
        }
        return false;
    }

    /**
     * Obter webhooks registrados (método de conveniência)
     * 
     * @param array $filters Filtros opcionais
     * @return array
     * @throws ApiException
     */
    public function getWebhooks(array $filters = []): array
    {
        $webhookService = new \Innochannel\Sdk\Services\WebhookService($this);
        return $webhookService->list($filters);
    }

    // ========== RESERVATION SERVICE METHODS ==========

    /**
     * Criar uma nova reserva
     * 
     * @param array $reservationData Dados da reserva
     * @return \Innochannel\Sdk\Models\Reservation
     * @throws ApiException
     */
    public function createReservation(array $reservationData): \Innochannel\Sdk\Models\Reservation
    {
        return $this->reservations()->create($reservationData);
    }

    /**
     * Obter uma reserva específica
     * 
     * @param string $reservationId ID da reserva
     * @return \Innochannel\Sdk\Models\Reservation
     * @throws ApiException
     */
    public function getReservation(string $reservationId): \Innochannel\Sdk\Models\Reservation
    {
        return $this->reservations()->get($reservationId);
    }

    /**
     * Listar reservas
     * 
     * @param array $filters Filtros opcionais
     * @return array
     * @throws ApiException
     */
    public function getReservations(array $filters = []): array
    {
        return $this->reservations()->list($filters);
    }

    /**
     * Atualizar uma reserva
     * 
     * @param string $reservationId ID da reserva
     * @param array $updateData Dados para atualização
     * @return \Innochannel\Sdk\Models\Reservation
     * @throws ApiException
     */
    public function updateReservation(string $reservationId, array $updateData): \Innochannel\Sdk\Models\Reservation
    {
        return $this->reservations()->update($reservationId, $updateData);
    }

    /**
     * Cancelar uma reserva
     * 
     * @param string $reservationId ID da reserva
     * @param array $cancellationData Dados de cancelamento
     * @return bool
     * @throws ApiException
     */
    public function cancelReservation(string $reservationId, array $cancellationData = []): bool
    {
        return $this->reservations()->cancel($reservationId, $cancellationData);
    }

    /**
     * Confirmar uma reserva
     * 
     * @param string $reservationId ID da reserva
     * @param array $confirmationData Dados de confirmação
     * @return \Innochannel\Sdk\Models\Reservation
     * @throws ApiException
     */
    public function confirmReservation(string $reservationId, array $confirmationData = []): \Innochannel\Sdk\Models\Reservation
    {
        return $this->reservations()->confirm($reservationId, $confirmationData);
    }

    /**
     * Modificar uma reserva
     * 
     * @param string $reservationId ID da reserva
     * @param array $modificationData Dados de modificação
     * @return \Innochannel\Sdk\Models\Reservation
     * @throws ApiException
     */
    public function modifyReservation(string $reservationId, array $modificationData): \Innochannel\Sdk\Models\Reservation
    {
        return $this->reservations()->modify($reservationId, $modificationData);
    }

    /**
     * Obter histórico de uma reserva
     * 
     * @param string $reservationId ID da reserva
     * @return array
     * @throws ApiException
     */
    public function getReservationHistory(string $reservationId): array
    {
        return $this->reservations()->getHistory($reservationId);
    }

    /**
     * Sincronizar reserva com PMS
     * 
     * @param string $reservationId ID da reserva
     * @param array $syncOptions Opções de sincronização
     * @return array
     * @throws ApiException
     */
    public function syncReservationWithPms(string $reservationId, array $syncOptions = []): array
    {
        return $this->reservations()->syncWithPms($reservationId, $syncOptions);
    }

    // ========== OTA CONNECTION SERVICE METHODS ==========

    /**
     * Listar conexões OTA
     * 
     * @param array $filters Filtros opcionais
     * @return array
     * @throws ApiException
     */
    public function getOtaConnections(array $filters = []): array
    {
        return $this->otaConnections()->list($filters);
    }

    /**
     * Obter uma conexão OTA específica
     * 
     * @param string $connectionId ID da conexão
     * @return array
     * @throws ApiException
     */
    public function getOtaConnection(string $connectionId): array
    {
        return $this->otaConnections()->get($connectionId);
    }

    /**
     * Criar uma nova conexão OTA
     * 
     * @param array $connectionData Dados da conexão
     * @return array
     * @throws ApiException
     */
    public function createOtaConnection(array $connectionData): array
    {
        return $this->otaConnections()->create($connectionData);
    }

    /**
     * Atualizar uma conexão OTA
     * 
     * @param string $connectionId ID da conexão
     * @param array $updateData Dados para atualização
     * @return array
     * @throws ApiException
     */
    public function updateOtaConnection(string $connectionId, array $updateData): array
    {
        return $this->otaConnections()->update($connectionId, $updateData);
    }

    /**
     * Deletar uma conexão OTA
     * 
     * @param string $connectionId ID da conexão
     * @return bool
     * @throws ApiException
     */
    public function deleteOtaConnection(string $connectionId): bool
    {
        return $this->otaConnections()->delete($connectionId);
    }

    /**
     * Testar conectividade com uma OTA
     * 
     * @param string $connectionId ID da conexão
     * @return array
     * @throws ApiException
     */
    public function testOtaConnection(string $connectionId): array
    {
        return $this->otaConnections()->testConnection($connectionId);
    }

    /**
     * Testar conexão com o PMS
     * 
     * @param array $connectionData Dados da conexão (opcional)
     * @return array
     * @throws ApiException
     */
    public function testConnection(array $connectionData = []): array
    {
        return $this->properties()->testPmsConnection($connectionData);
    }

    /**
     * Sincronizar dados com uma OTA
     * 
     * @param string $connectionId ID da conexão
     * @param array $syncOptions Opções de sincronização
     * @return array
     * @throws ApiException
     */
    public function syncOtaConnection(string $connectionId, array $syncOptions = []): array
    {
        return $this->otaConnections()->sync($connectionId, $syncOptions);
    }

    /**
     * Obter status de uma conexão OTA
     * 
     * @param string $connectionId ID da conexão
     * @return array
     * @throws ApiException
     */
    public function getOtaConnectionStatus(string $connectionId): array
    {
        return $this->otaConnections()->getStatus($connectionId);
    }

    /**
     * Ativar uma conexão OTA
     * 
     * @param string $connectionId ID da conexão
     * @return bool
     * @throws ApiException
     */
    public function activateOtaConnection(string $connectionId): bool
    {
        return $this->otaConnections()->activate($connectionId);
    }

    /**
     * Desativar uma conexão OTA
     * 
     * @param string $connectionId ID da conexão
     * @return bool
     * @throws ApiException
     */
    public function deactivateOtaConnection(string $connectionId): bool
    {
        return $this->otaConnections()->deactivate($connectionId);
    }

    // ========== MONITORING SERVICE METHODS ==========

    /**
     * Obter métricas do sistema
     * 
     * @param array $filters Filtros opcionais
     * @return array
     * @throws ApiException
     */
    public function getMetrics(array $filters = []): array
    {
        return $this->monitoring()->getMetrics($filters);
    }

    /**
     * Obter status de saúde do sistema
     * 
     * @return array
     * @throws ApiException
     */
    public function getHealthStatus(): array
    {
        return $this->monitoring()->getHealthStatus();
    }

    /**
     * Obter logs do sistema
     * 
     * @param array $filters Filtros opcionais
     * @return array
     * @throws ApiException
     */
    public function getLogs(array $filters = []): array
    {
        return $this->monitoring()->getLogs($filters);
    }

    /**
     * Obter alertas ativos
     * 
     * @param array $filters Filtros opcionais
     * @return array
     * @throws ApiException
     */
    public function getAlerts(array $filters = []): array
    {
        return $this->monitoring()->getAlerts($filters);
    }

    /**
     * Criar um novo alerta
     * 
     * @param array $alertData Dados do alerta
     * @return array
     * @throws ApiException
     */
    public function createAlert(array $alertData): array
    {
        return $this->monitoring()->createAlert($alertData);
    }

    /**
     * Atualizar um alerta
     * 
     * @param string $alertId ID do alerta
     * @param array $updateData Dados para atualização
     * @return array
     * @throws ApiException
     */
    public function updateAlert(string $alertId, array $updateData): array
    {
        return $this->monitoring()->updateAlert($alertId, $updateData);
    }

    /**
     * Deletar um alerta
     * 
     * @param string $alertId ID do alerta
     * @return bool
     * @throws ApiException
     */
    public function deleteAlert(string $alertId): bool
    {
        return $this->monitoring()->deleteAlert($alertId);
    }

    /**
     * Obter estatísticas de performance
     * 
     * @param array $filters Filtros opcionais
     * @return array
     * @throws ApiException
     */
    public function getPerformanceStats(array $filters = []): array
    {
        return $this->monitoring()->getPerformanceStats($filters);
    }

    /**
     * Obter estatísticas de uso da API
     * 
     * @param array $filters Filtros opcionais
     * @return array
     * @throws ApiException
     */
    public function getApiUsageStats(array $filters = []): array
    {
        return $this->monitoring()->getApiUsageStats($filters);
    }

    /**
     * Executar diagnóstico do sistema
     * 
     * @param array $options Opções de diagnóstico
     * @return array
     * @throws ApiException
     */
    public function runDiagnostics(array $options = []): array
    {
        return $this->monitoring()->runDiagnostics($options);
    }

    /**
     * Gerar relatório de monitoramento
     * 
     * @param string $reportType Tipo do relatório
     * @param array $parameters Parâmetros do relatório
     * @return array
     * @throws ApiException
     */
    public function generateMonitoringReport(string $reportType, array $parameters = []): array
    {
        return $this->monitoring()->generateReport($reportType, $parameters);
    }

    // ========== PROPERTY SERVICE ADDITIONAL METHODS ==========

    /**
     * Criar um quarto para uma propriedade
     * 
     * @param int|string $propertyId ID da propriedade
     * @param array $roomData Dados do quarto
     * @return \Innochannel\Sdk\Models\Room
     * @throws ApiException
     */
    public function createRoom($propertyId, array $roomData): \Innochannel\Sdk\Models\Room
    {
        return $this->properties()->createRoom($propertyId, $roomData);
    }

    /**
     * Listar quartos de uma propriedade
     * 
     * @param int|string $propertyId ID da propriedade
     * @param array $filters Filtros opcionais
     * @return array
     * @throws ApiException
     */
    public function getRooms($propertyId, array $filters = []): array
    {
        return $this->properties()->listRooms($propertyId, $filters);
    }

    /**
     * Obter um quarto específico
     * 
     * @param int|string $propertyId ID da propriedade
     * @param int|string $roomId ID do quarto
     * @return \Innochannel\Sdk\Models\Room
     * @throws ApiException
     */
    public function getRoom($propertyId, $roomId): \Innochannel\Sdk\Models\Room
    {
        return $this->properties()->getRoom($propertyId, $roomId);
    }

    /**
     * Atualizar um quarto
     * 
     * @param int|string $propertyId ID da propriedade
     * @param int|string $roomId ID do quarto
     * @param array $roomData Dados para atualização
     * @return \Innochannel\Sdk\Models\Room
     * @throws ApiException
     */
    public function updateRoom($propertyId, $roomId, array $roomData): \Innochannel\Sdk\Models\Room
    {
        return $this->properties()->updateRoom($propertyId, $roomId, $roomData);
    }

    /**
     * Deletar um quarto
     * 
     * @param int|string $propertyId ID da propriedade
     * @param int|string $roomId ID do quarto
     * @return bool
     * @throws ApiException
     */
    public function deleteRoom($propertyId, $roomId): bool
    {
        return $this->properties()->deleteRoom($propertyId, $roomId);
    }

    /**
     * Criar um plano de tarifa
     * 
     * @param int|string $propertyId ID da propriedade
     * @param array $ratePlanData Dados do plano de tarifa
     * @return \Innochannel\Sdk\Models\RatePlan
     * @throws ApiException
     */
    public function createRatePlan($propertyId, array $ratePlanData): \Innochannel\Sdk\Models\RatePlan
    {
        return $this->properties()->createRatePlan($propertyId, $ratePlanData);
    }

    /**
     * Listar quartos de uma propriedade
     * 
     * Lista todos os quartos de uma propriedade específica com opção
     * de aplicar filtros para refinar os resultados.
     * 
     * @param int|string $propertyId ID da propriedade
     * @param array $filters Filtros opcionais para a busca:
     *                      - room_type (string): Filtrar por tipo de quarto
     *                      - status (string): Filtrar por status ('active', 'inactive')
     *                      - floor (int): Filtrar por andar
     *                      - capacity (int): Filtrar por capacidade
     *                      - amenities (array): Filtrar por comodidades
     * @return array Array de objetos Room
     * @throws ApiException Se houver erro na comunicação com a API
     * 
     * @example
     * // Listar todos os quartos
     * $rooms = $client->listRooms('PROP123');
     * 
     * // Listar com filtros
     * $rooms = $client->listRooms('PROP123', [
     *     'room_type' => 'deluxe',
     *     'status' => 'active'
     * ]);
     */
    public function listRooms($propertyId, array $filters = []): array
    {
        return $this->properties()->listRooms($propertyId, $filters);
    }

    /**
     * Listar planos de tarifa de uma propriedade
     * 
     * Lista todos os planos de tarifa de uma propriedade específica
     * com opção de aplicar filtros para refinar os resultados.
     * 
     * @param int|string $propertyId ID da propriedade
     * @param array $filters Filtros opcionais para a busca:
     *                      - rate_type (string): Filtrar por tipo de tarifa
     *                      - status (string): Filtrar por status ('active', 'inactive')
     *                      - currency (string): Filtrar por moeda
     *                      - date_from (string): Data inicial de validade
     *                      - date_to (string): Data final de validade
     * @return array Array de objetos RatePlan
     * @throws ApiException Se houver erro na comunicação com a API
     * 
     * @example
     * // Listar todos os planos de tarifa
     * $ratePlans = $client->listRatePlans('PROP123');
     * 
     * // Listar com filtros
     * $ratePlans = $client->listRatePlans('PROP123', [
     *     'status' => 'active',
     *     'currency' => 'BRL'
     * ]);
     */
    public function listRatePlans($propertyId, array $filters = []): array
    {
        return $this->properties()->listRatePlans($propertyId, $filters);
    }

    /**
     * Listar planos de tarifa de uma propriedade (método legado)
     * 
     * @param int|string $propertyId ID da propriedade
     * @param array $filters Filtros opcionais
     * @return array
     * @throws ApiException
     */
    public function getRatePlans($propertyId, array $filters = []): array
    {
        return $this->properties()->listRatePlans($propertyId, $filters);
    }

    /**
     * Obter um plano de tarifa específico
     * 
     * @param int|string $propertyId ID da propriedade
     * @param int|string $ratePlanId ID do plano de tarifa
     * @return \Innochannel\Sdk\Models\RatePlan
     * @throws ApiException
     */
    public function getRatePlan($propertyId, $ratePlanId): \Innochannel\Sdk\Models\RatePlan
    {
        return $this->properties()->getRatePlan($propertyId, $ratePlanId);
    }

    /**
     * Atualizar um plano de tarifa
     * 
     * @param int|string $propertyId ID da propriedade
     * @param int|string $ratePlanId ID do plano de tarifa
     * @param array $ratePlanData Dados para atualização
     * @return \Innochannel\Sdk\Models\RatePlan
     * @throws ApiException
     */
    public function updateRatePlan($propertyId, $ratePlanId, array $ratePlanData): \Innochannel\Sdk\Models\RatePlan
    {
        return $this->properties()->updateRatePlan($propertyId, $ratePlanId, $ratePlanData);
    }

    /**
     * Deletar um plano de tarifa
     * 
     * @param int|string $propertyId ID da propriedade
     * @param int|string $ratePlanId ID do plano de tarifa
     * @return bool
     * @throws ApiException
     */
    public function deleteRatePlan($propertyId, $ratePlanId): bool
    {
        return $this->properties()->deleteRatePlan($propertyId, $ratePlanId);
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
     * $result = $client->testPmsConnection([
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
        return $this->properties()->testPmsConnection($connectionData);
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
     * $result = $client->syncAvailability('PROP123');
     * 
     * // Sincronizar com opções específicas
     * $result = $client->syncAvailability('PROP123', [
     *     'direction' => 'pull',
     *     'date_from' => '2024-01-01',
     *     'date_to' => '2024-01-31',
     *     'room_types' => ['standard', 'deluxe']
     * ]);
     */
    public function syncAvailability($propertyIdInPMS, array $syncOptions = []): array
    {
        return $this->properties()->syncAvailability($propertyIdInPMS, $syncOptions);
    }

    /**
     * Sincronizar propriedade com PMS
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
     * $result = $client->syncPropertyWithPms('PROP123');
     * 
     * // Sincronização específica de quartos e tarifas
     * $result = $client->syncPropertyWithPms('PROP123', [
     *     'direction' => 'pull',
     *     'entities' => ['rooms', 'rate-plans'],
     *     'date_from' => '2024-01-01',
     *     'date_to' => '2024-01-31'
     * ]);
     * 
     * // Sincronização bidirecional com força
     * $result = $client->syncPropertyWithPms('PROP123', [
     *     'direction' => 'both',
     *     'force_update' => true,
     *     'batch_size' => 100
     * ]);
     */
    public function syncPropertyWithPms($propertyId, array $syncOptions = []): array
    {
        return $this->properties()->syncWithPms($propertyId, $syncOptions);
    }

    // ========== INVENTORY SERVICE ADDITIONAL METHODS ==========

    /**
     * Atualizar disponibilidade
     * 
     * @param int $propertyId ID da propriedade
     * @param array $availabilityData Dados de disponibilidade
     * @return array
     * @throws ApiException
     */
    public function updateAvailability(int $propertyId, array $availabilityData): array
    {
        return $this->inventory()->updateAvailability($propertyId, $availabilityData);
    }

    /**
     * Obter disponibilidade
     * 
     * @param int $propertyId ID da propriedade
     * @param array $filters Filtros opcionais
     * @return array
     * @throws ApiException
     */
    public function getAvailability(int $propertyId, array $filters = []): array
    {
        return $this->inventory()->getAvailability($propertyId, $filters);
    }

    /**
     * Atualizar tarifas
     * 
     * @param int $propertyId ID da propriedade
     * @param array $rateData Dados de tarifas
     * @return array
     * @throws ApiException
     */
    public function updateRates(int $propertyId, array $rateData): array
    {
        return $this->inventory()->updateRates($propertyId, $rateData);
    }

    /**
     * Obter tarifas
     * 
     * @param int $propertyId ID da propriedade
     * @param array $filters Filtros opcionais
     * @return array
     * @throws ApiException
     */
    public function getRates(int $propertyId, array $filters = []): array
    {
        return $this->inventory()->getRates($propertyId, $filters);
    }

    /**
     * Atualizar inventário em lote
     * 
     * @param int $propertyId ID da propriedade
     * @param array $batchData Dados em lote
     * @return array
     * @throws ApiException
     */
    public function updateInventoryBatch(int $propertyId, array $batchData): array
    {
        return $this->inventory()->updateBatch($propertyId, $batchData);
    }

    /**
     * Obter calendário de inventário
     * 
     * @param int $propertyId ID da propriedade
     * @param string $dateFrom Data inicial
     * @param string $dateTo Data final
     * @param array $filters Filtros opcionais
     * @return array
     * @throws ApiException
     */
    public function getInventoryCalendar(int $propertyId, string $dateFrom, string $dateTo, array $filters = []): array
    {
        return $this->inventory()->getCalendar($propertyId, $dateFrom, $dateTo, $filters);
    }

    /**
     * Definir restrições de inventário
     * 
     * @param int $propertyId ID da propriedade
     * @param array $restrictionData Dados de restrições
     * @return array
     * @throws ApiException
     */
    public function setInventoryRestrictions(int $propertyId, array $restrictionData): array
    {
        return $this->inventory()->setRestrictions($propertyId, $restrictionData);
    }

    /**
     * Obter restrições de inventário
     * 
     * @param int $propertyId ID da propriedade
     * @param array $filters Filtros opcionais
     * @return array
     * @throws ApiException
     */
    public function getInventoryRestrictions(int $propertyId, array $filters = []): array
    {
        return $this->inventory()->getRestrictions($propertyId, $filters);
    }

    /**
     * Fazer requisição HTTP
     * 
     * @param string $method
     * @param string $endpoint
     * @param array $options
     * @return array
     * @throws ApiException
     * @throws AuthenticationException
     */
    /**
     * Determine if a request should be retried based on the status code
     */
    private function shouldRetry(int $statusCode): bool
    {
        // Retry on server errors (5xx) and specific client errors
        return in_array($statusCode, [502, 503, 504]);
    }

    public function request(string $method, string $endpoint, array $options = []): ?array
    {
        $attempt = 0;
        $maxAttempts = $this->retryAttempts + 1;

        while ($attempt < $maxAttempts) {
            try {
                // Ensure default headers are always present
                $defaultHeaders = [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'Innochannel-PHP-SDK/1.0',
                ];

                // Merge default headers with any provided headers
                $headers = array_merge($defaultHeaders, $options['headers'] ?? []);

                // Adicionar autenticação
                $authOptions = $this->auth->authenticate(['headers' => $headers]);
                $headers = $authOptions['headers'];

                $this->logger->debug('Making API request', [
                    'method' => $method,
                    'endpoint' => $endpoint,
                    'attempt' => $attempt + 1,
                    'options' => $this->sanitizeLogOptions(['headers' => $headers])
                ]);

                // Construir URL completa
                $url = rtrim($this->baseUrl, '/') . '/' . ltrim($endpoint, '/');

                // Configurar o cliente HTTP do Laravel
                $httpClient = Http::withHeaders($headers)
                    ->timeout($this->timeout)
                    ->connectTimeout($this->connectTimeout);

                // Fazer a requisição baseada no método
                $response = match (strtoupper($method)) {
                    'GET' => $httpClient->get($url, $options['query'] ?? []),
                    'POST' => $httpClient->post($url, $options['json'] ?? []),
                    'PUT' => $httpClient->put($url, $options['json'] ?? []),
                    'PATCH' => $httpClient->patch($url, $options['json'] ?? []),
                    'DELETE' => $httpClient->delete($url),
                    default => throw new ApiException("Unsupported HTTP method: {$method}")
                };

                $statusCode = $response->status();
                $body = $response->body();

                $this->logger->debug('API response received', [
                    'status_code' => $statusCode,
                    'body_length' => strlen($body)
                ]);

                if ($statusCode >= 400) {
                    $this->handleErrorResponse($statusCode, $body);
                }

                // Handle empty responses (e.g., 204 No Content)
                if (empty($body)) {
                    return null;
                }

                $data = $response->json();

                if ($data === null && !empty($body)) {
                    throw new ApiException('Invalid JSON response');
                }

                return $data;
            } catch (RequestException $e) {
                $attempt++;

                $statusCode = $e->response ? $e->response->status() : 0;
                $body = $e->response ? $e->response->body() : '';

                // Don't retry authentication errors or validation errors
                if ($statusCode === 401 || $statusCode === 422) {
                    $this->handleErrorResponse($statusCode, $body);
                }

                // Check if we should retry for other errors
                if ($attempt < $maxAttempts && $this->shouldRetry($statusCode)) {
                    $this->logger->warning('Request failed, retrying', [
                        'method' => $method,
                        'endpoint' => $endpoint,
                        'attempt' => $attempt,
                        'error' => $e->getMessage(),
                        'retry_delay' => $this->retryDelay
                    ]);

                    // Wait before retrying
                    usleep($this->retryDelay * 1000); // Convert to microseconds
                    continue;
                }

                // If we can't retry or max attempts reached, handle the error
                if ($statusCode > 0) {
                    $this->handleErrorResponse($statusCode, $body);
                }

                $this->logger->error('HTTP request failed', [
                    'method' => $method,
                    'endpoint' => $endpoint,
                    'error' => $e->getMessage()
                ]);

                throw new ApiException('HTTP request failed: ' . $e->getMessage(), 0, $e);
            } catch (\Exception $e) {
                $attempt++;

                // Check if we should retry for other types of exceptions
                if ($attempt < $maxAttempts) {
                    $this->logger->warning('Request failed, retrying', [
                        'method' => $method,
                        'endpoint' => $endpoint,
                        'attempt' => $attempt,
                        'error' => $e->getMessage(),
                        'retry_delay' => $this->retryDelay
                    ]);

                    // Wait before retrying
                    usleep($this->retryDelay * 1000); // Convert to microseconds
                    continue;
                }

                $this->logger->error('HTTP request failed', [
                    'method' => $method,
                    'endpoint' => $endpoint,
                    'error' => $e->getMessage()
                ]);

                throw new ApiException('HTTP request failed: ' . $e->getMessage(), 0, $e);
            }
        }

        throw new ApiException('Request failed after all retry attempts');
    }

    /**
     * Fazer requisição GET
     */
    public function get(string $endpoint, array $query = []): ?array
    {
        $options = [];
        if (!empty($query)) {
            $options['query'] = $query;
        }

        return $this->request('GET', $endpoint, $options);
    }

    /**
     * Fazer requisição POST
     */
    public function post(string $endpoint, array $data = []): ?array
    {
        $options = [];
        if (!empty($data)) {
            $options['json'] = $data;
        }

        return $this->request('POST', $endpoint, $options);
    }

    /**
     * Fazer requisição PUT
     */
    public function put(string $endpoint, array $data = []): ?array
    {
        $options = [];
        if (!empty($data)) {
            $options['json'] = $data;
        }

        return $this->request('PUT', $endpoint, $options);
    }

    /**
     * Fazer requisição DELETE
     */
    public function delete(string $endpoint)
    {
        $result = $this->request('DELETE', $endpoint);

        // If there's content in the response, return it
        if ($result !== null && !empty($result)) {
            return $result;
        }

        // Otherwise return true for successful deletion
        return true;
    }

    /**
     * Fazer requisição PATCH
     */
    public function patch(string $endpoint, array $data = []): ?array
    {
        $options = [];
        if (!empty($data)) {
            $options['json'] = $data;
        }

        return $this->request('PATCH', $endpoint, $options);
    }

    /**
     * Obter logger
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * Obter URL base
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * Validar configuração
     * 
     * @throws ValidationException
     */
    private function validateConfig(array $config): void
    {
        if (!isset($config['api_key'])) {
            throw new ValidationException('API key is required');
        }

        if (!isset($config['api_secret'])) {
            throw new ValidationException('API Secret is required');
        }

        if (!empty($config['base_url']) && !filter_var($config['base_url'], FILTER_VALIDATE_URL)) {
            throw new ValidationException('Invalid base URL provided');
        }
    }

    /**
     * Criar instância de autenticação
     */
    private function createAuthentication(array $config): AuthenticationInterface
    {
        if (isset($config['api_key']) && isset($config['api_secret'])) {
            $apiKey = $config['api_key'] ?? '';
            $apiSecret = $config['api_secret'] ?? '';

            return new ApiKeyAuthentication($apiKey, $apiSecret);
        }

        throw new ValidationException('No valid authentication method provided');
    }

    /**
     * Tratar resposta de erro
     * 
     * @throws ApiException
     * @throws AuthenticationException
     */
    private function handleErrorResponse(int $statusCode, string $body): void
    {
        $data = json_decode($body, true);
        $message = $data['message'] ?? 'Unknown error';
        $errors = $data['errors'] ?? [];

        $this->logger->error('API error response', [
            'status_code' => $statusCode,
            'message' => $message,
            'errors' => $errors
        ]);

        switch ($statusCode) {
            case 400:
            case 422:
                throw new ValidationException($message, $errors);
            case 401:
                throw new AuthenticationException($message);
            case 404:
                throw new NotFoundException($message);
            case 429:
                throw new RateLimitException($message);
            case 500:
                throw new InnochannelException($message);
            default:
                throw new ApiException($message, $statusCode);
        }
    }

    /**
     * Sanitizar opções para log (remover dados sensíveis)
     */
    private function sanitizeLogOptions(array $options): array
    {
        $sanitized = $options;

        // Remover headers de autenticação
        if (isset($sanitized['headers']['X-API-KEY'])) {
            $sanitized['headers']['X-API-KEY'] = '[REDACTED]';
        }

        if (isset($sanitized['headers']['X-API-SECRET'])) {
            $sanitized['headers']['X-API-SECRET'] = '[REDACTED]';
        }

        return $sanitized;
    }
}
