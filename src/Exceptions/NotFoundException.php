<?php

declare(strict_types=1);

namespace Innochannel\Sdk\Exceptions;

/**
 * Exceção para recursos não encontrados (404)
 * 
 * @package Innochannel\Sdk\Exceptions
 * @author Innochannel SDK Team
 * @version 1.0.0
 */
class NotFoundException extends ApiException
{
    /**
     * @param string $message Mensagem de erro
     * @param array $context Contexto adicional
     */
    public function __construct(string $message = 'Resource not found', array $context = [])
    {
        parent::__construct($message, 404, null, $context);
    }
}