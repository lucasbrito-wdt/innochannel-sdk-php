<?php

declare(strict_types=1);

namespace Innochannel\Sdk\Services;

use Innochannel\Sdk\Client;
use Innochannel\Sdk\Exceptions\ApiException;

/**
 * Serviço de Monitoramento
 * 
 * Gerencia operações de monitoramento e métricas do sistema
 */
class MonitoringService
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Obter métricas gerais do sistema
     * 
     * @param array $filters Filtros opcionais (período, tipo de métrica, etc.)
     * @return array
     * @throws ApiException
     */
    public function getMetrics(array $filters = []): array
    {
        $response = $this->client->get('/api/monitoring/metrics', $filters);
        return $response['data'] ?? [];
    }

    /**
     * Obter status de saúde do sistema
     * 
     * @return array
     * @throws ApiException
     */
    public function getHealthStatus(): array
    {
        $response = $this->client->get('/api/monitoring/health');
        return $response['data'] ?? [];
    }

    /**
     * Obter logs do sistema
     * 
     * @param array $filters Filtros (nível, período, serviço, etc.)
     * @return array
     * @throws ApiException
     */
    public function getLogs(array $filters = []): array
    {
        $response = $this->client->get('/api/monitoring/logs', $filters);
        return $response['data'] ?? [];
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
        $response = $this->client->get('/api/monitoring/alerts', $filters);
        return $response['data'] ?? [];
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
        $response = $this->client->post('/api/monitoring/alerts', $alertData);
        return $response['data'] ?? [];
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
        $response = $this->client->put("/api/monitoring/alerts/{$alertId}", $updateData);
        return $response['data'] ?? [];
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
        $response = $this->client->delete("/api/monitoring/alerts/{$alertId}");
        return $response['success'] ?? false;
    }

    /**
     * Obter estatísticas de performance
     * 
     * @param array $filters Filtros (período, endpoint, etc.)
     * @return array
     * @throws ApiException
     */
    public function getPerformanceStats(array $filters = []): array
    {
        $response = $this->client->get('/api/monitoring/performance', $filters);
        return $response['data'] ?? [];
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
        $response = $this->client->get('/api/monitoring/api-usage', $filters);
        return $response['data'] ?? [];
    }

    /**
     * Obter estatísticas de erros
     * 
     * @param array $filters Filtros opcionais
     * @return array
     * @throws ApiException
     */
    public function getErrorStats(array $filters = []): array
    {
        $response = $this->client->get('/api/monitoring/errors', $filters);
        return $response['data'] ?? [];
    }

    /**
     * Obter uptime do sistema
     * 
     * @param array $filters Filtros opcionais
     * @return array
     * @throws ApiException
     */
    public function getUptime(array $filters = []): array
    {
        $response = $this->client->get('/api/monitoring/uptime', $filters);
        return $response['data'] ?? [];
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
        $response = $this->client->post('/api/monitoring/diagnostics', $options);
        return $response['data'] ?? [];
    }

    /**
     * Obter relatório de monitoramento
     * 
     * @param string $reportType Tipo do relatório
     * @param array $parameters Parâmetros do relatório
     * @return array
     * @throws ApiException
     */
    public function generateReport(string $reportType, array $parameters = []): array
    {
        $data = array_merge(['type' => $reportType], $parameters);
        $response = $this->client->post('/api/monitoring/reports', $data);
        return $response['data'] ?? [];
    }

    /**
     * Configurar notificações de monitoramento
     * 
     * @param array $notificationConfig Configuração de notificações
     * @return array
     * @throws ApiException
     */
    public function configureNotifications(array $notificationConfig): array
    {
        $response = $this->client->post('/api/monitoring/notifications/config', $notificationConfig);
        return $response['data'] ?? [];
    }

    /**
     * Obter configurações de notificações
     * 
     * @return array
     * @throws ApiException
     */
    public function getNotificationConfig(): array
    {
        $response = $this->client->get('/api/monitoring/notifications/config');
        return $response['data'] ?? [];
    }
}