<?php

declare(strict_types=1);

namespace Innochannel\Sdk;

/**
 * Classe para configurar opções de requisição HTTP
 * 
 * @package Innochannel\Sdk
 * @author Innochannel SDK Team
 * @version 1.0.0
 */
class RequestOptions
{
    // Constants for Guzzle options
    public const QUERY = 'query';
    public const JSON = 'json';
    public const HEADERS = 'headers';
    public const TIMEOUT = 'timeout';
    public const DEBUG = 'debug';
    
    private array $headers = [];
    private array $query = [];
    private ?int $timeout = null;
    private bool $debug = false;
    private array $options = [];

    /**
     * Definir cabeçalhos da requisição
     */
    public function setHeaders(array $headers): self
    {
        $this->headers = array_merge($this->headers, $headers);
        return $this;
    }

    /**
     * Adicionar um cabeçalho
     */
    public function addHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * Obter cabeçalhos
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Definir parâmetros de query
     */
    public function setQuery(array $query): self
    {
        $this->query = array_merge($this->query, $query);
        return $this;
    }

    /**
     * Adicionar parâmetro de query
     */
    public function addQuery(string $name, $value): self
    {
        $this->query[$name] = $value;
        return $this;
    }

    /**
     * Obter parâmetros de query
     */
    public function getQuery(): array
    {
        return $this->query;
    }

    /**
     * Definir timeout da requisição
     */
    public function setTimeout(int $timeout): self
    {
        $this->timeout = $timeout;
        return $this;
    }

    /**
     * Obter timeout
     */
    public function getTimeout(): ?int
    {
        return $this->timeout;
    }

    /**
     * Habilitar/desabilitar debug
     */
    public function setDebug(bool $debug): self
    {
        $this->debug = $debug;
        return $this;
    }

    /**
     * Verificar se debug está habilitado
     */
    public function isDebug(): bool
    {
        return $this->debug;
    }

    /**
     * Definir opções adicionais
     */
    public function setOptions(array $options): self
    {
        $this->options = array_merge($this->options, $options);
        return $this;
    }

    /**
     * Adicionar uma opção
     */
    public function addOption(string $name, $value): self
    {
        $this->options[$name] = $value;
        return $this;
    }

    /**
     * Obter opções
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Converter para array de opções do Guzzle
     */
    public function toGuzzleOptions(): array
    {
        $options = $this->options;

        if (!empty($this->headers)) {
            $options['headers'] = $this->headers;
        }

        if (!empty($this->query)) {
            $options['query'] = $this->query;
        }

        if ($this->timeout !== null) {
            $options['timeout'] = $this->timeout;
        }

        if ($this->debug) {
            $options['debug'] = true;
        }

        return $options;
    }

    /**
     * Criar instância a partir de array
     */
    public static function fromArray(array $data): self
    {
        $instance = new self();

        if (isset($data['headers'])) {
            $instance->setHeaders($data['headers']);
        }

        if (isset($data['query'])) {
            $instance->setQuery($data['query']);
        }

        if (isset($data['timeout'])) {
            $instance->setTimeout($data['timeout']);
        }

        if (isset($data['debug'])) {
            $instance->setDebug($data['debug']);
        }

        if (isset($data['options'])) {
            $instance->setOptions($data['options']);
        }

        return $instance;
    }
}