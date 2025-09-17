<?php

declare(strict_types=1);

namespace Innochannel\Sdk\Auth;

/**
 * Interface para autenticação
 * 
 * @package Innochannel\Sdk\Auth
 * @author Innochannel SDK Team
 * @version 1.0.0
 */
interface AuthenticationInterface
{
    /**
     * Adicionar credenciais de autenticação às opções da requisição
     * 
     * @param array $options Opções da requisição HTTP
     * @return array Opções modificadas com autenticação
     */
    public function authenticate(array $options): array;
    
    /**
     * Verificar se as credenciais são válidas
     * 
     * @return bool
     */
    public function isValid(): bool;
    
    /**
     * Obter tipo de autenticação
     * 
     * @return string
     */
    public function getType(): string;
}