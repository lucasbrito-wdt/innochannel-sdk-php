<?php

declare(strict_types=1);

namespace Innochannel\Sdk\Exceptions;

/**
 * Exceção para limite de taxa excedido (429)
 * 
 * @package Innochannel\Sdk\Exceptions
 * @author Innochannel SDK Team
 * @version 1.0.0
 */
class RateLimitException extends ApiException
{
    /**
     * @param string $message Mensagem de erro
     * @param array $context Contexto adicional
     */
    public function __construct(string $message = 'Rate limit exceeded', array $context = [])
    {
        parent::__construct($message, 429, null, $context);
    }
}