<?php

declare(strict_types=1);

namespace Innochannel\Sdk\Auth;

use Innochannel\Sdk\Exceptions\AuthenticationException;

/**
 * Implementação de autenticação por API Key e API Secret
 * 
 * @package Innochannel\Sdk\Auth
 * @author Innochannel SDK Team
 * @version 1.0.0
 */
class ApiKeyAuthentication implements AuthenticationInterface
{
    private string $apiKey;
    private string $apiSecret;
    
    /**
     * @param string $apiKey Chave da API
     * @param string $apiSecret Segredo da API
     * @throws AuthenticationException
     */
    public function __construct(string $apiKey, string $apiSecret = '')
    {
        if (empty($apiKey)) {
            throw new AuthenticationException('API key cannot be empty');
        }
        
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
    }
    
    /**
     * {@inheritdoc}
     */
    public function authenticate(array $options): array
    {
        if (!isset($options['headers'])) {
            $options['headers'] = [];
        }
        
        // Adicionar cabeçalhos de autenticação
        $options['headers']['X-API-KEY'] = $this->apiKey;
        
        // Adicionar X-API-SECRET apenas se fornecido
        if (!empty($this->apiSecret)) {
            $options['headers']['X-API-SECRET'] = $this->apiSecret;
        }
        
        return $options;
    }
    
    /**
     * {@inheritdoc}
     */
    public function isValid(): bool
    {
        return !empty($this->apiKey) && strlen($this->apiKey) >= 10;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'api_key';
    }
    
    /**
     * Obter API key (mascarada para logs)
     */
    public function getMaskedApiKey(): string
    {
        if (strlen($this->apiKey) <= 8) {
            return str_repeat('*', strlen($this->apiKey));
        }
        
        return substr($this->apiKey, 0, 4) . str_repeat('*', strlen($this->apiKey) - 8) . substr($this->apiKey, -4);
    }
    
    /**
     * Obter API secret (mascarado para logs)
     */
    public function getMaskedApiSecret(): string
    {
        if (empty($this->apiSecret)) {
            return '';
        }
        
        if (strlen($this->apiSecret) <= 8) {
            return str_repeat('*', strlen($this->apiSecret));
        }
        
        return substr($this->apiSecret, 0, 4) . str_repeat('*', strlen($this->apiSecret) - 8) . substr($this->apiSecret, -4);
    }
}