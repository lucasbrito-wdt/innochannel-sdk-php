<?php

declare(strict_types=1);

namespace Innochannel\Sdk\Exceptions;

/**
 * Exceção para erros de validação
 * 
 * @package Innochannel\Sdk\Exceptions
 * @author Innochannel SDK Team
 * @version 1.0.0
 */
class ValidationException extends ApiException
{
    private array $errors;
    
    /**
     * @param string $message Mensagem de erro
     * @param array $errors Lista de erros de validação
     * @param array $context Contexto adicional
     */
    public function __construct(string $message = 'Validation failed', array $errors = [], array $context = [])
    {
        parent::__construct($message, 422, null, $context);
        $this->errors = $errors;
    }
    
    /**
     * Obter erros de validação
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
    
    /**
     * Verificar se há erros para um campo específico
     */
    public function hasError(string $field): bool
    {
        return isset($this->errors[$field]);
    }
    
    /**
     * Obter erros de um campo específico
     */
    public function getFieldErrors(string $field): array
    {
        return $this->errors[$field] ?? [];
    }
    
    /**
     * Obter todos os erros como string formatada
     */
    public function getFormattedErrors(): string
    {
        $formatted = [];
        
        foreach ($this->errors as $field => $fieldErrors) {
            if (is_array($fieldErrors)) {
                foreach ($fieldErrors as $error) {
                    $formatted[] = "{$field}: {$error}";
                }
            } else {
                $formatted[] = "{$field}: {$fieldErrors}";
            }
        }
        
        return implode('; ', $formatted);
    }
}