<?php

declare(strict_types=1);

namespace Innochannel\Sdk\Services;

use Innochannel\Sdk\Client;
use Innochannel\Sdk\Exceptions\ApiException;
use Innochannel\Sdk\Exceptions\ValidationException;

/**
 * Serviço para gerenciamento de webhooks
 * 
 * @package Innochannel\Sdk\Services
 * @author Innochannel SDK Team
 * @version 1.0.0
 */
class WebhookService
{
    private Client $client;
    
    public function __construct(Client $client)
    {
        $this->client = $client;
    }
    
    /**
     * Criar webhook
     * 
     * @param array $webhookData
     * @return array
     * @throws ApiException
     * @throws ValidationException
     */
    public function create(array $webhookData): array
    {
        $this->validateWebhookData($webhookData);
        
        return $this->client->post('/api/webhooks', $webhookData);
    }
    
    /**
     * Listar webhooks
     * 
     * @param array $filters
     * @return array
     * @throws ApiException
     */
    public function list(array $filters = []): array
    {
        return $this->client->get('/api/webhooks', $filters);
    }
    
    /**
     * Obter webhook por ID
     * 
     * @param string $webhookId
     * @return array
     * @throws ApiException
     */
    public function get(string $webhookId): array
    {
        return $this->client->get("/api/webhooks/{$webhookId}");
    }
    
    /**
     * Atualizar webhook
     * 
     * @param string $webhookId
     * @param array $updateData
     * @return array
     * @throws ApiException
     * @throws ValidationException
     */
    public function update(string $webhookId, array $updateData): array
    {
        $this->validateUpdateData($updateData);
        
        return $this->client->put("/api/webhooks/{$webhookId}", $updateData);
    }
    
    /**
     * Excluir webhook
     * 
     * @param string $webhookId
     * @return bool
     * @throws ApiException
     */
    public function delete(string $webhookId): bool
    {
        $this->client->delete("/api/webhooks/{$webhookId}");
        return true;
    }
    
    /**
     * Testar webhook
     * 
     * @param string $webhookId
     * @param array $testData
     * @return array
     * @throws ApiException
     */
    public function test(string $webhookId, array $testData = []): array
    {
        return $this->client->post("/api/webhooks/{$webhookId}/test", $testData);
    }
    
    /**
     * Obter logs de webhook
     * 
     * @param string $webhookId
     * @param array $filters
     * @return array
     * @throws ApiException
     */
    public function getLogs(string $webhookId, array $filters = []): array
    {
        return $this->client->get("/api/webhooks/{$webhookId}/logs", $filters);
    }
    
    /**
     * Reenviar webhook
     * 
     * @param string $webhookId
     * @param string $logId
     * @return array
     * @throws ApiException
     */
    public function retry(string $webhookId, string $logId): array
    {
        return $this->client->post("/api/webhooks/{$webhookId}/logs/{$logId}/retry");
    }
    
    /**
     * Ativar/desativar webhook
     * 
     * @param string $webhookId
     * @param bool $active
     * @return array
     * @throws ApiException
     */
    public function setActive(string $webhookId, bool $active): array
    {
        return $this->client->patch("/api/webhooks/{$webhookId}", [
            'active' => $active
        ]);
    }
    
    /**
     * Obter eventos disponíveis
     * 
     * @return array
     * @throws ApiException
     */
    public function getAvailableEvents(): array
    {
        return $this->client->get('/api/webhooks/events');
    }
    
    /**
     * Validar assinatura de webhook
     * 
     * @param string $payload
     * @param string $signature
     * @param string $secret
     * @return bool
     */
    public function validateSignature(string $payload, string $signature, string $secret): bool
    {
        $expectedSignature = hash_hmac('sha256', $payload, $secret);
        
        return hash_equals($expectedSignature, $signature);
    }
    
    /**
     * Processar payload de webhook
     * 
     * @param string $payload
     * @param string $signature
     * @param string $secret
     * @return array
     * @throws ValidationException
     */
    public function processPayload(string $payload, string $signature, string $secret): array
    {
        if (!$this->validateSignature($payload, $signature, $secret)) {
            throw new ValidationException('Invalid webhook signature');
        }
        
        $data = json_decode($payload, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new ValidationException('Invalid JSON payload');
        }
        
        return $data;
    }
    
    /**
     * Criar resposta de webhook
     * 
     * @param bool $success
     * @param string|null $message
     * @param array $data
     * @return array
     */
    public function createResponse(bool $success = true, ?string $message = null, array $data = []): array
    {
        return [
            'success' => $success,
            'message' => $message ?? ($success ? 'Webhook processed successfully' : 'Webhook processing failed'),
            'data' => $data,
            'timestamp' => date('c')
        ];
    }
    
    /**
     * Validar dados de webhook
     * 
     * @param array $data
     * @throws ValidationException
     */
    private function validateWebhookData(array $data): void
    {
        $errors = [];
        
        if (empty($data['url'])) {
            $errors['url'] = ['Webhook URL is required'];
        } elseif (!filter_var($data['url'], FILTER_VALIDATE_URL)) {
            $errors['url'] = ['Webhook URL must be valid'];
        } elseif (!in_array(parse_url($data['url'], PHP_URL_SCHEME), ['http', 'https'])) {
            $errors['url'] = ['Webhook URL must use HTTP or HTTPS'];
        }
        
        if (empty($data['events'])) {
            $errors['events'] = ['At least one event is required'];
        } elseif (!is_array($data['events'])) {
            $errors['events'] = ['Events must be an array'];
        } else {
            $validEvents = [
                'reservation.created',
            'reservation.updated',
            'reservation.cancelled',
            'reservation.confirmed',
                'inventory.updated',
                'rates.updated',
                'property.updated',
                'room.updated'
            ];
            
            foreach ($data['events'] as $event) {
                if (!in_array($event, $validEvents)) {
                    $errors['events'][] = "Invalid event: {$event}";
                }
            }
        }
        
        if (isset($data['secret']) && (strlen($data['secret']) < 16 || strlen($data['secret']) > 64)) {
            $errors['secret'] = ['Secret must be between 16 and 64 characters'];
        }
        
        if (isset($data['timeout']) && (!is_int($data['timeout']) || $data['timeout'] < 1 || $data['timeout'] > 30)) {
            $errors['timeout'] = ['Timeout must be between 1 and 30 seconds'];
        }
        
        if (isset($data['retry_attempts']) && (!is_int($data['retry_attempts']) || $data['retry_attempts'] < 0 || $data['retry_attempts'] > 5)) {
            $errors['retry_attempts'] = ['Retry attempts must be between 0 and 5'];
        }
        
        if (!empty($errors)) {
            throw new ValidationException('Webhook validation failed', $errors);
        }
    }
    
    /**
     * Validar dados de atualização
     * 
     * @param array $data
     * @throws ValidationException
     */
    private function validateUpdateData(array $data): void
    {
        $errors = [];
        
        if (isset($data['url'])) {
            if (!filter_var($data['url'], FILTER_VALIDATE_URL)) {
                $errors['url'] = ['Webhook URL must be valid'];
            } elseif (!in_array(parse_url($data['url'], PHP_URL_SCHEME), ['http', 'https'])) {
                $errors['url'] = ['Webhook URL must use HTTP or HTTPS'];
            }
        }
        
        if (isset($data['events'])) {
            if (!is_array($data['events'])) {
                $errors['events'] = ['Events must be an array'];
            } else {
                $validEvents = [
                    'reservation.created',
            'reservation.updated',
            'reservation.cancelled',
            'reservation.confirmed',
                    'inventory.updated',
                    'rates.updated',
                    'property.updated',
                    'room.updated'
                ];
                
                foreach ($data['events'] as $event) {
                    if (!in_array($event, $validEvents)) {
                        $errors['events'][] = "Invalid event: {$event}";
                    }
                }
            }
        }
        
        if (isset($data['secret']) && (strlen($data['secret']) < 16 || strlen($data['secret']) > 64)) {
            $errors['secret'] = ['Secret must be between 16 and 64 characters'];
        }
        
        if (isset($data['timeout']) && (!is_int($data['timeout']) || $data['timeout'] < 1 || $data['timeout'] > 30)) {
            $errors['timeout'] = ['Timeout must be between 1 and 30 seconds'];
        }
        
        if (isset($data['retry_attempts']) && (!is_int($data['retry_attempts']) || $data['retry_attempts'] < 0 || $data['retry_attempts'] > 5)) {
            $errors['retry_attempts'] = ['Retry attempts must be between 0 and 5'];
        }
        
        if (!empty($errors)) {
            throw new ValidationException('Webhook update validation failed', $errors);
        }
    }
}