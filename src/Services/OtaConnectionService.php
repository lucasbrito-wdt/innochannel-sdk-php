<?php

declare(strict_types=1);

namespace Innochannel\Sdk\Services;

use Innochannel\Sdk\Client;
use Innochannel\Sdk\Exceptions\ApiException;

/**
 * Serviço de Conexões OTA
 * 
 * Gerencia conexões com Online Travel Agencies (OTAs)
 */
class OtaConnectionService
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Listar todas as conexões OTA
     * 
     * @param array $filters Filtros opcionais
     * @return array
     * @throws ApiException
     */
    public function list(array $filters = []): array
    {
        $response = $this->client->get('/api/ota-connections', $filters);
        return $response['data'] ?? [];
    }

    /**
     * Obter uma conexão OTA específica
     * 
     * @param string $connectionId ID da conexão
     * @return array
     * @throws ApiException
     */
    public function get(string $connectionId): array
    {
        $response = $this->client->get("/api/ota-connections/{$connectionId}");
        return $response['data'] ?? [];
    }

    /**
     * Criar uma nova conexão OTA
     * 
     * @param array $connectionData Dados da conexão
     * @return array
     * @throws ApiException
     */
    public function create(array $connectionData): array
    {
        $response = $this->client->post('/api/ota-connections', $connectionData);
        return $response['data'] ?? [];
    }

    /**
     * Atualizar uma conexão OTA
     * 
     * @param string $connectionId ID da conexão
     * @param array $updateData Dados para atualização
     * @return array
     * @throws ApiException
     */
    public function update(string $connectionId, array $updateData): array
    {
        $response = $this->client->put("/api/ota-connections/{$connectionId}", $updateData);
        return $response['data'] ?? [];
    }

    /**
     * Deletar uma conexão OTA
     * 
     * @param string $connectionId ID da conexão
     * @return bool
     * @throws ApiException
     */
    public function delete(string $connectionId): bool
    {
        $response = $this->client->delete("/api/ota-connections/{$connectionId}");
        return $response['success'] ?? false;
    }

    /**
     * Testar conectividade com uma OTA
     * 
     * @param string $connectionId ID da conexão
     * @return array
     * @throws ApiException
     */
    public function testConnection(string $connectionId): array
    {
        $response = $this->client->post("/api/ota-connections/{$connectionId}/test");
        return $response['data'] ?? [];
    }

    /**
     * Sincronizar dados com uma OTA
     * 
     * @param string $connectionId ID da conexão
     * @param array $syncOptions Opções de sincronização
     * @return array
     * @throws ApiException
     */
    public function sync(string $connectionId, array $syncOptions = []): array
    {
        $response = $this->client->post("/api/ota-connections/{$connectionId}/sync", $syncOptions);
        return $response['data'] ?? [];
    }

    /**
     * Obter status de uma conexão OTA
     * 
     * @param string $connectionId ID da conexão
     * @return array
     * @throws ApiException
     */
    public function getStatus(string $connectionId): array
    {
        $response = $this->client->get("/api/ota-connections/{$connectionId}/status");
        return $response['data'] ?? [];
    }

    /**
     * Ativar uma conexão OTA
     * 
     * @param string $connectionId ID da conexão
     * @return bool
     * @throws ApiException
     */
    public function activate(string $connectionId): bool
    {
        $response = $this->client->post("/api/ota-connections/{$connectionId}/activate");
        return $response['success'] ?? false;
    }

    /**
     * Desativar uma conexão OTA
     * 
     * @param string $connectionId ID da conexão
     * @return bool
     * @throws ApiException
     */
    public function deactivate(string $connectionId): bool
    {
        $response = $this->client->post("/api/ota-connections/{$connectionId}/deactivate");
        return $response['success'] ?? false;
    }

    /**
     * Obter configurações de uma conexão OTA
     * 
     * @param string $connectionId ID da conexão
     * @return array
     * @throws ApiException
     */
    public function getConfiguration(string $connectionId): array
    {
        $response = $this->client->get("/api/ota-connections/{$connectionId}/configuration");
        return $response['data'] ?? [];
    }

    /**
     * Atualizar configurações de uma conexão OTA
     * 
     * @param string $connectionId ID da conexão
     * @param array $configData Dados de configuração
     * @return array
     * @throws ApiException
     */
    public function updateConfiguration(string $connectionId, array $configData): array
    {
        $response = $this->client->put("/api/ota-connections/{$connectionId}/configuration", $configData);
        return $response['data'] ?? [];
    }
}