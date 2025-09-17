# Guia de Publicação do Innochannel Laravel Service Provider

Este documento descreve o processo completo de publicação dos recursos do Service Provider do Innochannel SDK para Laravel, incluindo configurações, migrations, views e outros recursos.

## Índice

1. [Visão Geral](#visão-geral)
2. [Recursos Disponíveis para Publicação](#recursos-disponíveis-para-publicação)
3. [Processo de Instalação](#processo-de-instalação)
4. [Publicação de Configurações](#publicação-de-configurações)
5. [Publicação de Migrations](#publicação-de-migrations)
6. [Publicação de Views](#publicação-de-views)
7. [Publicação de Arquivos de Idioma](#publicação-de-arquivos-de-idioma)
8. [Comandos Artisan Disponíveis](#comandos-artisan-disponíveis)
9. [Exemplos Práticos](#exemplos-práticos)
10. [Solução de Problemas](#solução-de-problemas)

## Visão Geral

O Innochannel Laravel Service Provider (`InnochannelServiceProvider`) é responsável por registrar e configurar todos os serviços, recursos e funcionalidades do SDK no framework Laravel. Ele oferece um sistema completo de publicação de recursos que permite aos desenvolvedores personalizar e estender a funcionalidade do SDK.

### Arquitetura do Service Provider

```
InnochannelServiceProvider
├── register() - Registra serviços no container
├── boot() - Configura recursos e publica assets
├── provides() - Define serviços fornecidos
└── registerEventListeners() - Registra listeners de eventos
```

## Recursos Disponíveis para Publicação

O Service Provider oferece os seguintes recursos para publicação:

### 1. Configurações (`innochannel-config`)

- **Arquivo**: `config/innochannel.php`
- **Descrição**: Arquivo de configuração principal do SDK
- **Localização**: `src/Laravel/config/innochannel.php`

### 2. Migrations (`innochannel-migrations`)

- **Arquivos**: Migrations do banco de dados
- **Descrição**: Tabelas necessárias para logs, cache, webhooks e status de sincronização
- **Localização**: `src/Laravel/database/migrations/`

### 3. Views (`innochannel-views`)

- **Arquivos**: Templates Blade
- **Descrição**: Views para dashboard e interfaces administrativas
- **Localização**: `src/Laravel/resources/views/`

### 4. Arquivos de Idioma (`innochannel-lang`)

- **Arquivos**: Traduções
- **Descrição**: Arquivos de tradução para internacionalização
- **Localização**: `src/Laravel/resources/lang/`

## Processo de Instalação

### Instalação Automática

O método mais simples é usar o comando de instalação automática:

```bash
php artisan innochannel:install
```

#### Opções do Comando de Instalação

```bash
# Instalação completa (padrão)
php artisan innochannel:install

# Forçar sobrescrita de arquivos existentes
php artisan innochannel:install --force

# Pular execução de migrations
php artisan innochannel:install --skip-migrations

# Pular publicação de configurações
php artisan innochannel:install --skip-config
```

### Instalação Manual

Para maior controle, você pode publicar recursos individualmente:

```bash
# Publicar apenas configurações
php artisan vendor:publish --provider="Innochannel\Laravel\InnochannelServiceProvider" --tag="innochannel-config"

# Publicar apenas migrations
php artisan vendor:publish --provider="Innochannel\Laravel\InnochannelServiceProvider" --tag="innochannel-migrations"

# Publicar apenas views
php artisan vendor:publish --provider="Innochannel\Laravel\InnochannelServiceProvider" --tag="innochannel-views"

# Publicar apenas arquivos de idioma
php artisan vendor:publish --provider="Innochannel\Laravel\InnochannelServiceProvider" --tag="innochannel-lang"
```

## Publicação de Configurações

### Processo de Publicação

1. **Comando de Publicação**:

   ```bash
   php artisan vendor:publish --provider="Innochannel\Laravel\InnochannelServiceProvider" --tag="innochannel-config"
   ```

2. **Localização do Arquivo Publicado**:

   ```
   config/innochannel.php
   ```

3. **Estrutura da Configuração**:

```php
<?php

return [
    // Credenciais da API
    'api_key' => env('INNOCHANNEL_API_KEY'),
    'api_secret' => env('INNOCHANNEL_API_SECRET'),
    
    // Endpoints da API
    'base_url' => env('INNOCHANNEL_BASE_URL', 'https://api.innochannel.com'),
    'webhook_url' => env('INNOCHANNEL_WEBHOOK_URL'),
    
    // Configuração de Requisições
    'timeout' => env('INNOCHANNEL_TIMEOUT', 30),
    'retry_attempts' => env('INNOCHANNEL_RETRY_ATTEMPTS', 3),
    'retry_delay' => env('INNOCHANNEL_RETRY_DELAY', 1000),
    
    // Configuração de Logging
    'logging' => [
        'enabled' => env('INNOCHANNEL_LOGGING_ENABLED', true),
        'channel' => env('INNOCHANNEL_LOG_CHANNEL', 'daily'),
        'level' => env('INNOCHANNEL_LOG_LEVEL', 'info'),
        'log_requests' => env('INNOCHANNEL_LOG_REQUESTS', true),
        'log_responses' => env('INNOCHANNEL_LOG_RESPONSES', false),
        'log_errors' => env('INNOCHANNEL_LOG_ERRORS', true),
    ],
    
    // Configuração de Cache
    'cache' => [
        'enabled' => env('INNOCHANNEL_CACHE_ENABLED', true),
        'store' => env('INNOCHANNEL_CACHE_STORE', 'redis'),
        'ttl' => env('INNOCHANNEL_CACHE_TTL', 3600),
        'prefix' => env('INNOCHANNEL_CACHE_PREFIX', 'innochannel'),
    ],
    
    // Configuração de Webhooks
    'webhooks' => [
        'enabled' => env('INNOCHANNEL_WEBHOOKS_ENABLED', true),
        'secret' => env('INNOCHANNEL_WEBHOOK_SECRET'),
        'verify_signature' => env('INNOCHANNEL_WEBHOOK_VERIFY_SIGNATURE', true),
        'routes' => [
            'prefix' => 'innochannel/webhooks',
            'middleware' => ['api', 'innochannel.auth'],
        ],
    ],
    
    // Rate Limiting
    'rate_limiting' => [
        'enabled' => env('INNOCHANNEL_RATE_LIMITING_ENABLED', true),
        'requests_per_minute' => env('INNOCHANNEL_RATE_LIMIT_RPM', 60),
        'burst_limit' => env('INNOCHANNEL_RATE_LIMIT_BURST', 10),
    ],
    
    // Configuração de Serviços
    'services' => [
        'reservation' => [
            'auto_sync' => env('INNOCHANNEL_RESERVATION_AUTO_SYNC', true),
            'sync_direction' => env('INNOCHANNEL_RESERVATION_SYNC_DIRECTION', 'both'),
            'validation_strict' => env('INNOCHANNEL_RESERVATION_VALIDATION_STRICT', true),
        ],
        'property' => [
            'auto_sync' => env('INNOCHANNEL_PROPERTY_AUTO_SYNC', true),
            'cache_duration' => env('INNOCHANNEL_PROPERTY_CACHE_DURATION', 7200),
        ],
        'inventory' => [
            'auto_sync' => env('INNOCHANNEL_INVENTORY_AUTO_SYNC', true),
            'sync_interval' => env('INNOCHANNEL_INVENTORY_SYNC_INTERVAL', 300),
            'batch_size' => env('INNOCHANNEL_INVENTORY_BATCH_SIZE', 100),
        ],
    ],
    
    // Configuração de Eventos
    'events' => [
        'enabled' => env('INNOCHANNEL_EVENTS_ENABLED', true),
        'async' => env('INNOCHANNEL_EVENTS_ASYNC', true),
        'queue' => env('INNOCHANNEL_EVENTS_QUEUE', 'default'),
        'listeners' => [
            'reservation_created' => true,
            'reservation_updated' => true,
            'reservation_cancelled' => true,
            'property_updated' => true,
            'inventory_updated' => true,
        ],
    ],
    
    // Configuração do Banco de Dados
    'database' => [
        'connection' => env('INNOCHANNEL_DB_CONNECTION', 'default'),
        'tables' => [
            'logs' => 'innochannel_logs',
            'webhooks' => 'innochannel_webhooks',
            'cache' => 'innochannel_cache',
            'sync_status' => 'innochannel_sync_status',
        ],
    ],
    
    // Configuração de Desenvolvimento
    'development' => [
        'debug' => env('INNOCHANNEL_DEBUG', false),
        'mock_responses' => env('INNOCHANNEL_MOCK_RESPONSES', false),
        'test_mode' => env('INNOCHANNEL_TEST_MODE', false),
    ],
];
```

### Variáveis de Ambiente Necessárias

Após publicar a configuração, adicione as seguintes variáveis ao seu arquivo `.env`:

```env
# Credenciais obrigatórias
INNOCHANNEL_API_KEY=your_api_key_here
INNOCHANNEL_API_SECRET=your_api_secret_here

# URLs (opcional - usa padrões se não especificado)
INNOCHANNEL_BASE_URL=https://api.innochannel.com
INNOCHANNEL_WEBHOOK_URL=https://your-domain.com/innochannel/webhooks

# Configurações opcionais
INNOCHANNEL_TIMEOUT=30
INNOCHANNEL_RETRY_ATTEMPTS=3
INNOCHANNEL_LOGGING_ENABLED=true
INNOCHANNEL_CACHE_ENABLED=true
INNOCHANNEL_WEBHOOKS_ENABLED=true
```

## Publicação de Migrations

### Migrations Disponíveis

O SDK inclui as seguintes migrations:

1. **`2024_01_01_000001_create_innochannel_logs_table.php`**
   - Tabela para logs de requisições e respostas da API

2. **`2024_01_01_000002_create_innochannel_cache_table.php`**
   - Tabela para cache de dados da API

3. **`2024_01_01_000003_create_innochannel_webhooks_table.php`**
   - Tabela para armazenar webhooks recebidos

4. **`2024_01_01_000004_create_innochannel_sync_status_table.php`**
   - Tabela para controlar status de sincronização

### Processo de Publicação

1. **Publicar Migrations**:

   ```bash
   php artisan vendor:publish --provider="Innochannel\Laravel\InnochannelServiceProvider" --tag="innochannel-migrations"
   ```

2. **Executar Migrations**:

   ```bash
   php artisan migrate
   ```

3. **Executar Migrations Específicas** (se necessário):

   ```bash
   php artisan migrate --path=database/migrations/2024_01_01_000001_create_innochannel_logs_table.php
   ```

### Estrutura das Tabelas

#### Tabela `innochannel_logs`

```sql
CREATE TABLE innochannel_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    request_id VARCHAR(255) NOT NULL,
    method VARCHAR(10) NOT NULL,
    url TEXT NOT NULL,
    headers JSON,
    body TEXT,
    response_status INT,
    response_headers JSON,
    response_body TEXT,
    duration INT,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_request_id (request_id),
    INDEX idx_created_at (created_at)
);
```

#### Tabela `innochannel_cache`

```sql
CREATE TABLE innochannel_cache (
    key VARCHAR(255) PRIMARY KEY,
    value LONGTEXT NOT NULL,
    expiration INT NOT NULL,
    INDEX idx_expiration (expiration)
);
```

#### Tabela `innochannel_webhooks`

```sql
CREATE TABLE innochannel_webhooks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    webhook_id VARCHAR(255) NOT NULL,
    event_type VARCHAR(100) NOT NULL,
    payload JSON NOT NULL,
    signature VARCHAR(255),
    processed_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_webhook_id (webhook_id),
    INDEX idx_event_type (event_type),
    INDEX idx_processed_at (processed_at)
);
```

#### Tabela `innochannel_sync_status`

```sql
CREATE TABLE innochannel_sync_status (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    entity_type VARCHAR(50) NOT NULL,
    entity_id VARCHAR(255) NOT NULL,
    last_sync_at TIMESTAMP NULL,
    sync_status ENUM('pending', 'syncing', 'completed', 'failed') DEFAULT 'pending',
    error_message TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY unique_entity (entity_type, entity_id),
    INDEX idx_sync_status (sync_status),
    INDEX idx_last_sync_at (last_sync_at)
);
```

## Publicação de Views

### Views Disponíveis

O SDK inclui as seguintes views:

1. **`dashboard.blade.php`** - Dashboard administrativo do Innochannel

### Processo de Publicação

1. **Publicar Views**:

   ```bash
   php artisan vendor:publish --provider="Innochannel\Laravel\InnochannelServiceProvider" --tag="innochannel-views"
   ```

2. **Localização das Views Publicadas**:

   ```
   resources/views/vendor/innochannel/
   ```

3. **Usar Views no Código**:

   ```php
   // Usar view publicada
   return view('innochannel::dashboard');
   
   // Ou usar view customizada (após publicação)
   return view('vendor.innochannel.dashboard');
   ```

### Estrutura da View Dashboard

A view dashboard inclui:

- Estatísticas de reservations, propriedades e inventário
- Logs de atividades recentes
- Status de webhooks
- Informações de sincronização

## Publicação de Arquivos de Idioma

### Processo de Publicação

1. **Publicar Arquivos de Idioma**:

   ```bash
   php artisan vendor:publish --provider="Innochannel\Laravel\InnochannelServiceProvider" --tag="innochannel-lang"
   ```

2. **Localização dos Arquivos Publicados**:

   ```
   resources/lang/vendor/innochannel/
   ```

3. **Usar Traduções**:

   ```php
   // Usar tradução
   __('innochannel::messages.reservation_created')
   
   // Ou com parâmetros
   __('innochannel::messages.sync_completed', ['count' => 10])
   ```

## Comandos Artisan Disponíveis

O Service Provider registra os seguintes comandos Artisan:

### 1. `innochannel:install`

Comando principal de instalação que automatiza todo o processo.

```bash
# Instalação completa
php artisan innochannel:install

# Com opções
php artisan innochannel:install --force --skip-migrations
```

### 2. `innochannel:sync`

Comando para sincronização manual de dados.

```bash
# Sincronizar todos os dados
php artisan innochannel:sync

# Sincronizar apenas reservations
php artisan innochannel:sync --type=reservations

# Sincronizar com força (ignorar cache)
php artisan innochannel:sync --force
```

### 3. `innochannel:test-connection`

Comando para testar a conexão com a API.

```bash
# Testar conexão
php artisan innochannel:test-connection

# Testar com detalhes verbosos
php artisan innochannel:test-connection --verbose
```

## Exemplos Práticos

### Exemplo 1: Instalação Completa

```bash
# 1. Instalar o pacote via Composer
composer require lucasbrito-wdt/innochannel-sdk

# 2. Executar instalação automática
php artisan innochannel:install

# 3. Configurar variáveis de ambiente
echo "INNOCHANNEL_API_KEY=your_key" >> .env
echo "INNOCHANNEL_API_SECRET=your_secret" >> .env

# 4. Testar conexão
php artisan innochannel:test-connection
```

### Exemplo 2: Instalação Personalizada

```bash
# 1. Publicar apenas configurações
php artisan vendor:publish --provider="Innochannel\Laravel\InnochannelServiceProvider" --tag="innochannel-config"

# 2. Editar configurações conforme necessário
nano config/innochannel.php

# 3. Publicar e executar migrations
php artisan vendor:publish --provider="Innochannel\Laravel\InnochannelServiceProvider" --tag="innochannel-migrations"
php artisan migrate

# 4. Publicar views para customização
php artisan vendor:publish --provider="Innochannel\Laravel\InnochannelServiceProvider" --tag="innochannel-views"
```

### Exemplo 3: Atualização de Recursos

```bash
# Forçar republicação de configurações
php artisan vendor:publish --provider="Innochannel\Laravel\InnochannelServiceProvider" --tag="innochannel-config" --force

# Executar novas migrations
php artisan migrate

# Limpar cache de configuração
php artisan config:clear
```

## Solução de Problemas

### Problema 1: Arquivo de Configuração Não Encontrado

**Erro**: `Configuration file not found`

**Solução**:

```bash
# Republicar configurações
php artisan vendor:publish --provider="Innochannel\Laravel\InnochannelServiceProvider" --tag="innochannel-config" --force

# Limpar cache
php artisan config:clear
```

### Problema 2: Migrations Não Executadas

**Erro**: `Table doesn't exist`

**Solução**:

```bash
# Verificar status das migrations
php artisan migrate:status

# Executar migrations pendentes
php artisan migrate

# Se necessário, executar migrations específicas
php artisan migrate --path=database/migrations/2024_01_01_000001_create_innochannel_logs_table.php
```

### Problema 3: Views Não Encontradas

**Erro**: `View not found`

**Solução**:

```bash
# Publicar views
php artisan vendor:publish --provider="Innochannel\Laravel\InnochannelServiceProvider" --tag="innochannel-views"

# Limpar cache de views
php artisan view:clear
```

### Problema 4: Comandos Não Registrados

**Erro**: `Command not found`

**Solução**:

```bash
# Verificar se o Service Provider está registrado
php artisan package:discover

# Limpar cache de configuração
php artisan config:clear

# Verificar lista de comandos
php artisan list innochannel
```

### Problema 5: Permissões de Diretório

**Erro**: `Permission denied`

**Solução**:

```bash
# Ajustar permissões dos diretórios de storage
chmod -R 755 storage/app/innochannel/
chown -R www-data:www-data storage/app/innochannel/
```

## Considerações de Segurança

1. **Credenciais da API**: Nunca commite credenciais no código. Use sempre variáveis de ambiente.

2. **Webhooks**: Configure sempre a verificação de assinatura para webhooks em produção.

3. **Logs**: Evite logar informações sensíveis. Configure adequadamente os níveis de log.

4. **Cache**: Use stores de cache seguros (Redis com autenticação) em produção.

## Conclusão

Este guia fornece uma visão completa do processo de publicação dos recursos do Innochannel Laravel Service Provider. Seguindo estas instruções, você poderá instalar, configurar e personalizar o SDK de acordo com suas necessidades específicas.

Para mais informações, consulte:

- [Documentação oficial do Innochannel](https://docs.innochannel.com)
- [Repositório do SDK](https://github.com/lucasbrito-wdt/innochannel-sdk-php)
- [Guia de integração](INTEGRATION_GUIDE.md)
