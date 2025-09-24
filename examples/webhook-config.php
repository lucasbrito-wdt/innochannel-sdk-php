<?php
/**
 * Configuração de Webhooks - SDK PHP Innochannel
 * 
 * Copie este arquivo para webhook-config-local.php e configure suas credenciais
 */

return [
    // ========== CONFIGURAÇÕES DA API ==========
    
    // Sua chave de API do Innochannel
    'api_key' => 'sua_api_key_aqui',
    
    // URL base da API (produção ou sandbox)
    'base_url' => 'https://api.innochannel.com', // ou 'https://sandbox-api.innochannel.com'
    
    // ========== CONFIGURAÇÕES DO WEBHOOK ==========
    
    // Secret para validação de assinatura (mantenha seguro!)
    'webhook_secret' => 'meu_secret_super_seguro_123456',
    
    // URL onde sua aplicação receberá os webhooks
    'webhook_url' => 'https://sua-aplicacao.com/webhook.php',
    
    // Timeout para requisições de webhook (em segundos)
    'webhook_timeout' => 15,
    
    // Número de tentativas em caso de falha
    'webhook_retry_attempts' => 3,
    
    // ========== EVENTOS QUE DESEJA RECEBER ==========
    
    'webhook_events' => [
        // Eventos de reserva
        'reservation.created',      // Nova reserva criada
        'reservation.updated',      // Reserva atualizada
        'reservation.cancelled',    // Reserva cancelada
        'reservation.confirmed',    // Reserva confirmada
        'reservation.checked_in',   // Check-in realizado
        'reservation.checked_out',  // Check-out realizado
        'reservation.no_show',      // No-show
        
        // Eventos de propriedade
        'property.updated',         // Propriedade atualizada
        'property.status_changed',  // Status da propriedade alterado
        
        // Eventos de inventário
        'inventory.updated',        // Inventário atualizado
        'inventory.availability_changed', // Disponibilidade alterada
        'inventory.rate_changed',   // Taxa alterada
        
        // Eventos gerais
        'system.maintenance',       // Manutenção do sistema
        'system.alert'             // Alertas do sistema
    ],
    
    // ========== CONFIGURAÇÕES DE AMBIENTE ==========
    
    // Ambiente (development, staging, production)
    'environment' => 'development',
    
    // Habilitar logs detalhados
    'debug_mode' => true,
    
    // Diretório para logs
    'log_directory' => __DIR__ . '/logs',
    
    // Diretório para dados
    'data_directory' => __DIR__ . '/data',
    
    // ========== CONFIGURAÇÕES DE EMAIL ==========
    
    'email' => [
        'enabled' => false,         // Habilitar envio de emails
        'smtp_host' => 'smtp.gmail.com',
        'smtp_port' => 587,
        'smtp_username' => 'seu_email@gmail.com',
        'smtp_password' => 'sua_senha_app',
        'from_email' => 'noreply@sua-aplicacao.com',
        'from_name' => 'Sua Aplicação Hotel'
    ],
    
    // ========== CONFIGURAÇÕES DE BANCO DE DADOS ==========
    
    'database' => [
        'enabled' => false,         // Usar banco de dados ao invés de arquivos JSON
        'driver' => 'mysql',
        'host' => 'localhost',
        'port' => 3306,
        'database' => 'hotel_app',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4'
    ],
    
    // ========== CONFIGURAÇÕES DE SEGURANÇA ==========
    
    'security' => [
        // IPs permitidos para receber webhooks (vazio = todos)
        'allowed_ips' => [
            // '192.168.1.100',
            // '10.0.0.50'
        ],
        
        // Rate limiting (requisições por minuto)
        'rate_limit' => 60,
        
        // Validar sempre a assinatura
        'validate_signature' => true,
        
        // Timeout para processamento (em segundos)
        'processing_timeout' => 30
    ],
    
    // ========== CONFIGURAÇÕES DE NOTIFICAÇÕES ==========
    
    'notifications' => [
        // Slack webhook URL para notificações
        'slack_webhook_url' => '',
        
        // Discord webhook URL para notificações
        'discord_webhook_url' => '',
        
        // Telegram bot token e chat ID
        'telegram_bot_token' => '',
        'telegram_chat_id' => '',
        
        // Eventos que devem gerar notificações
        'notify_events' => [
            'reservation.created',
            'reservation.cancelled',
            'system.alert'
        ]
    ],
    
    // ========== CONFIGURAÇÕES PERSONALIZADAS ==========
    
    'custom' => [
        // Suas configurações específicas aqui
        'hotel_name' => 'Meu Hotel',
        'timezone' => 'America/Sao_Paulo',
        'currency' => 'BRL',
        'language' => 'pt-BR'
    ]
];