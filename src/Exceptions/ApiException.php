<?php

declare(strict_types=1);

namespace Innochannel\Sdk\Exceptions;

use Exception;

/**
 * Exceção base para erros da API
 * 
 * @package Innochannel\Sdk\Exceptions
 * @author Innochannel SDK Team
 * @version 1.0.0
 */
class ApiException extends Exception
{
    protected array $context;
    
    /**
     * @param string $message Mensagem de erro
     * @param int $code Código de erro HTTP
     * @param Exception|null $previous Exceção anterior
     * @param array $context Contexto adicional
     */
    public function __construct(string $message = '', int $code = 0, ?Exception $previous = null, array $context = [])
    {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }
    
    /**
     * Obter contexto adicional
     */
    public function getContext(): array
    {
        return $this->context;
    }
    
    /**
     * Verificar se é erro de cliente (4xx)
     */
    public function isClientError(): bool
    {
        return $this->code >= 400 && $this->code < 500;
    }
    
    /**
     * Verificar se é erro de servidor (5xx)
     */
    public function isServerError(): bool
    {
        return $this->code >= 500 && $this->code < 600;
    }
}