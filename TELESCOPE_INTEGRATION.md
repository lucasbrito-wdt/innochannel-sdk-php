# Integração Laravel Telescope - Innochannel SDK

Este documento descreve como configurar e usar a integração do Laravel Telescope com o Innochannel SDK para logging e monitoramento de requisições HTTP.

## Visão Geral

A integração do Telescope com o Innochannel SDK fornece:

- **Logging automático** de todas as requisições HTTP relacionadas ao Innochannel
- **Monitoramento de performance** com métricas de tempo de resposta
- **Filtragem inteligente** para capturar apenas requisições relevantes
- **Proteção de dados sensíveis** com ocultação automática de campos críticos
- **Watchers personalizados** para diferentes tipos de requisições
- **Tags organizacionais** para facilitar busca e filtragem no Telescope

## Instalação

### 1. Instalar o Laravel Telescope

```bash
composer require laravel/telescope
php artisan telescope:install
php artisan migrate
```

### 2. Configurar o Innochannel SDK

O Innochannel SDK já inclui a integração com o Telescope. Certifique-se de que o Service Provider está registrado:

```php
// config/app.php
'providers' => [
    // ...
    Innochannel\Laravel\InnochannelServiceProvider::class,
],
```

### 3. Publicar Configurações (Opcional)

```bash
php artisan vendor:publish --tag=innochannel-telescope-config
```

## Configuração

### Variáveis de Ambiente

Adicione as seguintes variáveis ao seu arquivo `.env`:

```env
# Habilitar integração com Telescope
INNOCHANNEL_TELESCOPE_ENABLED=true

# Canal de log específico
INNOCHANNEL_LOG_CHANNEL=innochannel

# Configurações de logging HTTP
INNOCHANNEL_LOG_INCOMING=true
INNOCHANNEL_LOG_OUTGOING=true
INNOCHANNEL_LOG_HEADERS=true
INNOCHANNEL_LOG_BODY=true
INNOCHANNEL_MAX_BODY_SIZE=10000
INNOCHANNEL_LOG_RESPONSES=true
INNOCHANNEL_MAX_RESPONSE_SIZE=20000

# Monitoramento de performance
INNOCHANNEL_SLOW_REQUEST_THRESHOLD=1000
INNOCHANNEL_LOG_SLOW_REQUESTS=true
INNOCHANNEL_REQUEST_TIMEOUT=30000

# Configurações de armazenamento
INNOCHANNEL_LOG_DIRECTORY=storage/logs/innochannel
INNOCHANNEL_LOG_ROTATION_DAYS=30
INNOCHANNEL_LOG_FORMAT=daily

# Modo debug (apenas desenvolvimento)
INNOCHANNEL_DEBUG_MODE=false
```

### Configuração do Canal de Log

Adicione um canal específico para o Innochannel no `config/logging.php`:

```php
'channels' => [
    // ...
    'innochannel' => [
        'driver' => 'daily',
        'path' => storage_path('logs/innochannel/innochannel.log'),
        'level' => env('LOG_LEVEL', 'debug'),
        'days' => 30,
        'replace_placeholders' => true,
    ],
],
```

## Funcionalidades

### 1. Logging Automático de Requisições

A integração captura automaticamente:

- **Requisições de entrada** para rotas do Innochannel
- **Requisições de saída** do cliente HTTP do SDK
- **Headers** (com ocultação de dados sensíveis)
- **Corpo das requisições e respostas**
- **Métricas de performance** (tempo de resposta, tamanho)
- **Status codes** e informações de erro

### 2. Watchers Personalizados

#### InnochannelHttpWatcher

Monitora especificamente as requisições HTTP do cliente Innochannel:

```php
// Registrado automaticamente pelo Service Provider
Innochannel\Laravel\Watchers\InnochannelHttpWatcher::class
```

### 3. Middleware de Logging

#### InnochannelTelescopeMiddleware

Captura requisições de entrada relacionadas ao Innochannel:

```php
// Registrar no Kernel.php se necessário
protected $middlewareGroups = [
    'api' => [
        // ...
        \Innochannel\Laravel\Middleware\InnochannelTelescopeMiddleware::class,
    ],
];
```

### 4. Filtragem Inteligente

A integração filtra automaticamente:

- URLs contendo `innochannel`, `/api/innochannel`
- Headers `X-Innochannel-SDK` ou `User-Agent: Innochannel-SDK`
- Requisições para domínios `innochannel.com` ou `api.innochannel`

### 5. Proteção de Dados Sensíveis

Campos automaticamente ocultados:

- `password`, `password_confirmation`
- `api_key`, `api_secret`, `token`
- `authorization`, `x-api-key`, `x-api-secret`
- `x-innochannel-secret`, `_token`

## Uso no Telescope

### Visualizando Logs

1. Acesse `/telescope` no seu navegador
2. Use as seguintes tags para filtrar:
   - `innochannel-sdk`: Todas as atividades do SDK
   - `innochannel-request`: Requisições de entrada
   - `innochannel-http-client`: Requisições de saída
   - `innochannel-error`: Erros relacionados
   - `innochannel-slow`: Requisições lentas

### Exemplos de Filtros

```
# Todas as requisições do Innochannel
tag:innochannel-sdk

# Apenas requisições HTTP de saída
tag:innochannel-http-client

# Requisições com erro
tag:innochannel-error

# Requisições lentas
tag:innochannel-slow

# Combinação de filtros
tag:innochannel-sdk tag:error
```

## Monitoramento de Performance

### Métricas Capturadas

- **Tempo de resposta** (em millisegundos)
- **Tamanho da requisição** e resposta
- **Status codes** HTTP
- **Identificação de requisições lentas**

### Alertas de Performance

Requisições que excedem o limite configurado (`INNOCHANNEL_SLOW_REQUEST_THRESHOLD`) são automaticamente marcadas com a tag `innochannel-slow`.

## Troubleshooting

### Telescope não está capturando requisições

1. Verifique se o Telescope está instalado e configurado
2. Confirme que `INNOCHANNEL_TELESCOPE_ENABLED=true`
3. Verifique se as requisições atendem aos critérios de filtragem

### Logs não aparecem no canal específico

1. Verifique a configuração do canal no `config/logging.php`
2. Confirme as permissões do diretório de logs
3. Verifique se `INNOCHANNEL_LOG_CHANNEL` está configurado corretamente

### Performance degradada

1. Ajuste `INNOCHANNEL_MAX_BODY_SIZE` e `INNOCHANNEL_MAX_RESPONSE_SIZE`
2. Desabilite logging de corpo se não necessário
3. Configure rotação de logs adequada

## Configuração Avançada

### Customizar Filtros

```php
// No Service Provider
Telescope::filter(function ($entry) {
    // Sua lógica personalizada de filtragem
    return $this->shouldLogEntry($entry);
});
```

### Adicionar Tags Personalizadas

```php
// No Service Provider
Telescope::tag(function ($entry) {
    $tags = [];
    
    // Sua lógica de tags personalizada
    if ($this->isSpecialRequest($entry)) {
        $tags[] = 'special-innochannel';
    }
    
    return $tags;
});
```

### Watchers Personalizados

```php
// Criar um novo watcher
class CustomInnochannelWatcher extends Watcher
{
    public function register($app)
    {
        // Registrar eventos específicos
    }
}
```

## Segurança

### Dados Sensíveis

- Nunca desabilite a proteção de dados sensíveis em produção
- Revise regularmente a lista de campos sensíveis
- Use HTTPS para todas as comunicações

### Acesso ao Telescope

- Configure autenticação adequada para o Telescope
- Restrinja acesso em produção
- Use gates/policies para controle de acesso

## Exemplo de Uso

```php
// Fazendo uma requisição que será automaticamente logada
$client = app(Innochannel\Sdk\Client::class);
$response = $client->reservations()->list();

// A requisição aparecerá no Telescope com as tags:
// - innochannel-sdk
// - innochannel-http-client
// - get (método HTTP)
// - success (se status 2xx)
```

## Suporte

Para suporte adicional:

- Documentação: [GitHub Repository](https://github.com/lucasbrito-wdt/innochannel-sdk)
- Issues: [GitHub Issues](https://github.com/lucasbrito-wdt/innochannel-sdk/issues)
- Email: support@innochannel.com