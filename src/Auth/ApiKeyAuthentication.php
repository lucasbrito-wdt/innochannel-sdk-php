<?php

declare(strict_types=1);

namespace Innochannel\Sdk\Auth;

use Innochannel\Sdk\Exceptions\AuthenticationException;

/**
 * Implementação de autenticação por API Key
 * 
 * @package Innochannel\Sdk\Auth
 * @author Innochannel SDK Team
 * @version 1.0.0
 */
class ApiKeyAuthentication implements AuthenticationInterface
{
    private string $apiKey;
    private string $headerName;
    
    /**
     * @param string $apiKey Chave da API
     * @param string $headerName Nome do header (padrão: Authorization)
     * @throws AuthenticationException
     */
    public function __construct(string $apiKey, string $headerName = 'Authorization')
    {
        if (empty($apiKey)) {
            throw new AuthenticationException('API key cannot be empty');
        }
        
        $this->apiKey = $apiKey;
        $this->headerName = $headerName;
    }
    
    /**
     * {@inheritdoc}
     */
    public function authenticate(array $options): array
    {
        if (!isset($options['headers'])) {
            $options['headers'] = [];
        }
        
        // Use Bearer token format for Authorization header
        if ($this->headerName === 'Authorization') {
            $options['headers'][$this->headerName] = 'Bearer ' . $this->apiKey;
        } else {
            $options['headers'][$this->headerName] = $this->apiKey;
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
}