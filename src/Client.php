<?php

declare(strict_types=1);

namespace Innochannel\Sdk;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
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
    
    private HttpClient $httpClient;
    private AuthenticationInterface $auth;
    private LoggerInterface $logger;
    private string $baseUrl;
    private int $retryAttempts;
    private int $retryDelay;
    
    // Services
    private ?PropertyService $propertyService = null;
    private ?InventoryService $inventoryService = null;
    private ?ReservationService $reservationService = null;
    private ?OtaConnectionService $otaConnectionService = null;
    private ?MonitoringService $monitoringService = null;
    
    /**
     * @param array $config Configuração do cliente
     * @param HttpClient|null $httpClient Cliente HTTP customizado (opcional)
     * @throws ValidationException
     */
    public function __construct(array $config = [], ?HttpClient $httpClient = null)
    {
        $this->validateConfig($config);
        
        $this->baseUrl = $config['base_url'] ?? self::DEFAULT_BASE_URL;
        $this->logger = $config['logger'] ?? new NullLogger();
        $this->retryAttempts = $config['retry_attempts'] ?? 0;
        $this->retryDelay = $config['retry_delay'] ?? 1000;
        
        // Configurar autenticação
        $this->auth = $this->createAuthentication($config);
        
        // Configurar cliente HTTP
        if ($httpClient !== null) {
            $this->httpClient = $httpClient;
        } else {
            $this->httpClient = new HttpClient([
                'base_uri' => $this->baseUrl,
                'timeout' => $config['timeout'] ?? self::DEFAULT_TIMEOUT,
                'connect_timeout' => $config['connect_timeout'] ?? self::DEFAULT_CONNECT_TIMEOUT,
            ]);
        }
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
     * Determine if a request should be retried based on the exception
     */
    private function shouldRetry(GuzzleException $e): bool
    {
        // Retry on server errors (5xx) and specific client errors
        if ($e instanceof \GuzzleHttp\Exception\ServerException) {
            return true;
        }
        
        if ($e instanceof \GuzzleHttp\Exception\ConnectException) {
            return true;
        }
        
        if ($e instanceof \GuzzleHttp\Exception\RequestException) {
            $response = $e->getResponse();
            if ($response) {
                $statusCode = $response->getStatusCode();
                // Retry on 503 Service Unavailable, 502 Bad Gateway, 504 Gateway Timeout
                return in_array($statusCode, [502, 503, 504]);
            }
        }
        
        return false;
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
                $options['headers'] = array_merge($defaultHeaders, $options['headers'] ?? []);
                
                // Adicionar autenticação
                $options = $this->auth->authenticate($options);
                
                $this->logger->debug('Making API request', [
                    'method' => $method,
                    'endpoint' => $endpoint,
                    'attempt' => $attempt + 1,
                    'options' => $this->sanitizeLogOptions($options)
                ]);
                
                $response = $this->httpClient->request($method, $endpoint, $options);
                $statusCode = $response->getStatusCode();
                $body = $response->getBody()->getContents();
                
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
                
                $data = json_decode($body, true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new ApiException('Invalid JSON response: ' . json_last_error_msg());
                }
                
                return $data;
                
            } catch (GuzzleException $e) {
                $attempt++;
                
                // Handle HTTP errors that should throw specific exceptions
                if ($e instanceof \GuzzleHttp\Exception\ClientException || 
                    $e instanceof \GuzzleHttp\Exception\ServerException) {
                    $response = $e->getResponse();
                    if ($response) {
                        $statusCode = $response->getStatusCode();
                        $body = $response->getBody()->getContents();
                        
                        // Don't retry authentication errors or validation errors
                        if ($statusCode === 401 || $statusCode === 422) {
                            $this->handleErrorResponse($statusCode, $body);
                        }
                        
                        // Check if we should retry for other errors
                        if ($attempt < $maxAttempts && $this->shouldRetry($e)) {
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
                        $this->handleErrorResponse($statusCode, $body);
                    }
                }
                
                // Check if we should retry for other types of exceptions
                if ($attempt < $maxAttempts && $this->shouldRetry($e)) {
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
            $options[RequestOptions::QUERY] = $query;
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
            $options[RequestOptions::JSON] = $data;
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
            $options[RequestOptions::JSON] = $data;
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
            $options[RequestOptions::JSON] = $data;
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
        if (empty($config['api_key']) && empty($config['auth'])) {
            throw new ValidationException('API key or custom authentication is required');
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
        if (isset($config['auth']) && $config['auth'] instanceof AuthenticationInterface) {
            return $config['auth'];
        }
        
        if (isset($config['api_key'])) {
            return new ApiKeyAuthentication($config['api_key']);
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
        if (isset($sanitized['headers']['Authorization'])) {
            $sanitized['headers']['Authorization'] = '[REDACTED]';
        }
        
        if (isset($sanitized['headers']['X-API-Key'])) {
            $sanitized['headers']['X-API-Key'] = '[REDACTED]';
        }
        
        return $sanitized;
    }
}