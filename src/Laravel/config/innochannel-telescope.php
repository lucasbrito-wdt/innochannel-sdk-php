<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Innochannel Telescope Configuration
    |--------------------------------------------------------------------------
    |
    | Configurações específicas para a integração do Laravel Telescope
    | com o Innochannel SDK para logging e monitoramento de HTTP requests.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Enable Telescope Integration
    |--------------------------------------------------------------------------
    |
    | Determina se a integração com o Telescope está habilitada.
    | Quando habilitada, todas as requisições HTTP do Innochannel SDK
    | serão automaticamente logadas no Telescope.
    |
    */
    'enabled' => env('INNOCHANNEL_TELESCOPE_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Log Channel
    |--------------------------------------------------------------------------
    |
    | Canal de log específico para o Innochannel SDK.
    | Este canal será usado para logs adicionais além do Telescope.
    |
    */
    'log_channel' => env('INNOCHANNEL_LOG_CHANNEL', 'innochannel'),

    /*
    |--------------------------------------------------------------------------
    | HTTP Request Logging
    |--------------------------------------------------------------------------
    |
    | Configurações para logging de requisições HTTP.
    |
    */
    'http_logging' => [
        // Logar requisições de entrada (incoming requests)
        'log_incoming_requests' => env('INNOCHANNEL_LOG_INCOMING', true),
        
        // Logar requisições de saída (outgoing HTTP client requests)
        'log_outgoing_requests' => env('INNOCHANNEL_LOG_OUTGOING', true),
        
        // Logar headers das requisições
        'log_headers' => env('INNOCHANNEL_LOG_HEADERS', true),
        
        // Logar corpo das requisições
        'log_body' => env('INNOCHANNEL_LOG_BODY', true),
        
        // Tamanho máximo do corpo da requisição para log (em bytes)
        'max_body_size' => env('INNOCHANNEL_MAX_BODY_SIZE', 10000),
        
        // Logar respostas
        'log_responses' => env('INNOCHANNEL_LOG_RESPONSES', true),
        
        // Tamanho máximo da resposta para log (em bytes)
        'max_response_size' => env('INNOCHANNEL_MAX_RESPONSE_SIZE', 20000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Sensitive Data Protection
    |--------------------------------------------------------------------------
    |
    | Lista de campos que devem ser ocultados nos logs por conterem
    | informações sensíveis.
    |
    */
    'sensitive_fields' => [
        'password',
        'password_confirmation',
        'api_key',
        'api_secret',
        'token',
        'authorization',
        'secret',
        'x-api-key',
        'x-api-secret',
        'x-innochannel-secret',
        '_token',
    ],

    /*
    |--------------------------------------------------------------------------
    | Request Filtering
    |--------------------------------------------------------------------------
    |
    | Configurações para filtrar quais requisições devem ser logadas.
    |
    */
    'filtering' => [
        // Padrões de URI que devem ser incluídos nos logs
        'include_patterns' => [
            '/innochannel',
            '/api/innochannel',
            'innochannel.com',
            'api.innochannel',
        ],
        
        // Padrões de URI que devem ser excluídos dos logs
        'exclude_patterns' => [
            '/telescope',
            '/horizon',
            '/_debugbar',
            '/favicon.ico',
        ],
        
        // Headers que indicam requisições do Innochannel SDK
        'innochannel_headers' => [
            'X-Innochannel-SDK',
            'User-Agent' => 'Innochannel-SDK',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Monitoring
    |--------------------------------------------------------------------------
    |
    | Configurações para monitoramento de performance.
    |
    */
    'performance' => [
        // Limite de tempo (em ms) para considerar uma requisição lenta
        'slow_request_threshold' => env('INNOCHANNEL_SLOW_REQUEST_THRESHOLD', 1000),
        
        // Logar requisições lentas separadamente
        'log_slow_requests' => env('INNOCHANNEL_LOG_SLOW_REQUESTS', true),
        
        // Limite de tempo (em ms) para timeout de requisições
        'request_timeout' => env('INNOCHANNEL_REQUEST_TIMEOUT', 30000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Telescope Tags
    |--------------------------------------------------------------------------
    |
    | Tags personalizadas que serão aplicadas às entradas do Telescope
    | para facilitar a filtragem e busca.
    |
    */
    'telescope_tags' => [
        'default' => ['innochannel-sdk'],
        'incoming_requests' => ['innochannel-request', 'http-incoming'],
        'outgoing_requests' => ['innochannel-http-client', 'http-outgoing'],
        'errors' => ['innochannel-error', 'error'],
        'slow_requests' => ['innochannel-slow', 'performance'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Storage Configuration
    |--------------------------------------------------------------------------
    |
    | Configurações para armazenamento dos logs.
    |
    */
    'storage' => [
        // Diretório para armazenar logs do Innochannel
        'log_directory' => env('INNOCHANNEL_LOG_DIRECTORY', storage_path('logs/innochannel')),
        
        // Rotação de logs (em dias)
        'log_rotation_days' => env('INNOCHANNEL_LOG_ROTATION_DAYS', 30),
        
        // Formato dos arquivos de log
        'log_format' => env('INNOCHANNEL_LOG_FORMAT', 'daily'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Debug Mode
    |--------------------------------------------------------------------------
    |
    | Quando habilitado, logs adicionais de debug serão gerados.
    | Recomendado apenas para desenvolvimento.
    |
    */
    'debug_mode' => env('INNOCHANNEL_DEBUG_MODE', false),
];