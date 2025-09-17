<?php

declare(strict_types=1);

namespace Innochannel\Sdk\Exceptions;

/**
 * Exceção genérica do SDK Innochannel
 * 
 * @package Innochannel\Sdk\Exceptions
 * @author Innochannel SDK Team
 * @version 1.0.0
 */
class InnochannelException extends ApiException
{
    /**
     * @param string $message Mensagem de erro
     * @param int $code Código de erro
     * @param \Exception|null $previous Exceção anterior
     * @param array $context Contexto adicional
     */
    public function __construct(string $message = 'Innochannel SDK error', int $code = 0, ?\Exception $previous = null, array $context = [])
    {
        parent::__construct($message, $code, $previous, $context);
    }
}