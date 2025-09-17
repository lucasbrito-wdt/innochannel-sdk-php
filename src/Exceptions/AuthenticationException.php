<?php

declare(strict_types=1);

namespace Innochannel\Sdk\Exceptions;

/**
 * Exceção para erros de autenticação
 * 
 * @package Innochannel\Sdk\Exceptions
 * @author Innochannel SDK Team
 * @version 1.0.0
 */
class AuthenticationException extends ApiException
{
    /**
     * @param string $message Mensagem de erro
     * @param array $context Contexto adicional
     */
    public function __construct(string $message = 'Authentication failed', array $context = [])
    {
        parent::__construct($message, 401, null, $context);
    }
}