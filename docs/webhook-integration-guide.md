# Guia de Integração de Webhooks - SDK PHP Innochannel

Este guia explica como integrar webhooks do Innochannel na sua aplicação usando o SDK PHP.

## 📋 Índice

1. [Conceitos Básicos](#conceitos-básicos)
2. [Configuração Inicial](#configuração-inicial)
3. [Implementação Básica](#implementação-básica)
4. [Integração com Laravel](#integração-com-laravel)
5. [Integração com Outras Frameworks](#integração-com-outras-frameworks)
6. [Segurança e Validação](#segurança-e-validação)
7. [Tratamento de Eventos](#tratamento-de-eventos)
8. [Monitoramento e Logs](#monitoramento-e-logs)
9. [Boas Práticas](#boas-práticas)
10. [Troubleshooting](#troubleshooting)

## 🎯 Conceitos Básicos

### O que são Webhooks?

Webhooks são notificações HTTP enviadas automaticamente pelo Innochannel para sua aplicação quando eventos específicos ocorrem (nova reserva, cancelamento, atualização de inventário, etc.).

### Fluxo de Funcionamento

```
Innochannel → [Evento Ocorre] → [Webhook Enviado] → Sua Aplicação → [Processa] → [Responde]
```

### Eventos Disponíveis

- `reservation.created` - Nova reserva criada
- `reservation.updated` - Reserva atualizada
- `reservation.cancelled` - Reserva cancelada
- `reservation.confirmed` - Reserva confirmada
- `inventory.updated` - Inventário atualizado
- `rates.updated` - Tarifas atualizadas
- `property.updated` - Propriedade atualizada
- `room.updated` - Quarto atualizado

## ⚙️ Configuração Inicial

### 1. Instalar o SDK

```bash
composer require innochannel/sdk-php
```

### 2. Configurar Credenciais

```php
// config.php
return [
    'innochannel' => [
        'api_key' => 'sua_api_key_aqui',
        'base_url' => 'https://api.innochannel.com',
        'webhook_secret' => 'seu_secret_super_seguro_123456', // Mínimo 16 caracteres
    ]
];
```

### 3. Registrar Webhook

```php
<?php
require_once 'vendor/autoload.php';

use Innochannel\Sdk\Client;
use Innochannel\Sdk\Services\WebhookService;

$client = new Client([
    'api_key' => 'sua_api_key',
    'base_url' => 'https://api.innochannel.com'
]);

$webhookService = new WebhookService($client);

// Registrar webhook
$webhook = $webhookService->create([
    'url' => 'https://sua-aplicacao.com/webhook/innochannel',
    'events' => [
        'reservation.created',
        'reservation.updated',
        'reservation.cancelled',
        'inventory.updated'
    ],
    'secret' => 'seu_secret_super_seguro_123456',
    'active' => true
]);

echo "Webhook registrado! ID: " . $webhook['id'];
```

## 🔧 Implementação Básica

### Endpoint Receptor de Webhook

```php
<?php
// webhook-endpoint.php

require_once 'vendor/autoload.php';

use Innochannel\Sdk\Client;
use Innochannel\Sdk\Services\WebhookService;
use Innochannel\Sdk\Exceptions\ValidationException;

// Configurar cliente
$client = new Client([
    'api_key' => 'sua_api_key',
    'base_url' => 'https://api.innochannel.com'
]);

$webhookService = new WebhookService($client);

try {
    // Obter dados da requisição
    $payload = file_get_contents('php://input');
    $signature = $_SERVER['HTTP_X_INNOCHANNEL_SIGNATURE'] ?? '';
    $secret = 'seu_secret_super_seguro_123456';

    // Validar e processar
    $data = $webhookService->processPayload($payload, $signature, $secret);

    // Log do evento recebido
    error_log("Webhook recebido: " . $data['event'] . " - ID: " . ($data['data']['id'] ?? 'N/A'));

    // Processar evento
    processarEvento($data);

    // Responder com sucesso
    $response = $webhookService->createResponse(true, 'Processado com sucesso');
    
    header('Content-Type: application/json');
    echo json_encode($response);

} catch (ValidationException $e) {
    // Assinatura inválida
    error_log("Webhook inválido: " . $e->getMessage());
    
    $response = $webhookService->createResponse(false, 'Assinatura inválida');
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode($response);

} catch (Exception $e) {
    // Erro interno
    error_log("Erro no webhook: " . $e->getMessage());
    
    $response = $webhookService->createResponse(false, 'Erro interno');
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode($response);
}

function processarEvento($data)
{
    switch ($data['event']) {
        case 'reservation.created':
            processarNovaReserva($data['data']);
            break;
            
        case 'reservation.updated':
            processarReservaAtualizada($data['data']);
            break;
            
        case 'reservation.cancelled':
            processarReservaCancelada($data['data']);
            break;
            
        case 'inventory.updated':
            processarInventarioAtualizado($data['data']);
            break;
            
        default:
            error_log("Evento não tratado: " . $data['event']);
    }
}

function processarNovaReserva($reserva)
{
    // Exemplo: Salvar no banco de dados
    $pdo = new PDO('mysql:host=localhost;dbname=hotel', $user, $pass);
    
    $stmt = $pdo->prepare("
        INSERT INTO reservas (innochannel_id, guest_name, check_in, check_out, status, created_at) 
        VALUES (?, ?, ?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE 
        guest_name = VALUES(guest_name),
        check_in = VALUES(check_in),
        check_out = VALUES(check_out),
        status = VALUES(status)
    ");
    
    $stmt->execute([
        $reserva['id'],
        $reserva['guest_name'],
        $reserva['check_in'],
        $reserva['check_out'],
        $reserva['status']
    ]);
    
    // Enviar email de confirmação
    enviarEmailConfirmacao($reserva);
    
    error_log("Nova reserva processada: " . $reserva['id']);
}

function processarReservaAtualizada($reserva)
{
    // Atualizar banco de dados
    $pdo = new PDO('mysql:host=localhost;dbname=hotel', $user, $pass);
    
    $stmt = $pdo->prepare("
        UPDATE reservas 
        SET guest_name = ?, check_in = ?, check_out = ?, status = ?, updated_at = NOW()
        WHERE innochannel_id = ?
    ");
    
    $stmt->execute([
        $reserva['guest_name'],
        $reserva['check_in'],
        $reserva['check_out'],
        $reserva['status'],
        $reserva['id']
    ]);
    
    error_log("Reserva atualizada: " . $reserva['id']);
}

function processarReservaCancelada($reserva)
{
    // Marcar como cancelada
    $pdo = new PDO('mysql:host=localhost;dbname=hotel', $user, $pass);
    
    $stmt = $pdo->prepare("
        UPDATE reservas 
        SET status = 'cancelled', cancelled_at = NOW()
        WHERE innochannel_id = ?
    ");
    
    $stmt->execute([$reserva['id']]);
    
    // Processar reembolso se necessário
    processarReembolso($reserva);
    
    error_log("Reserva cancelada: " . $reserva['id']);
}

function processarInventarioAtualizado($inventario)
{
    // Atualizar disponibilidade local
    $pdo = new PDO('mysql:host=localhost;dbname=hotel', $user, $pass);
    
    $stmt = $pdo->prepare("
        INSERT INTO inventario (property_id, room_type_id, date, availability, updated_at) 
        VALUES (?, ?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE 
        availability = VALUES(availability),
        updated_at = VALUES(updated_at)
    ");
    
    $stmt->execute([
        $inventario['property_id'],
        $inventario['room_type_id'],
        $inventario['date'],
        $inventario['availability']
    ]);
    
    error_log("Inventário atualizado: " . $inventario['property_id'] . " - " . $inventario['date']);
}

function enviarEmailConfirmacao($reserva)
{
    // Implementar envio de email
    // mail(), PHPMailer, etc.
}

function processarReembolso($reserva)
{
    // Implementar lógica de reembolso
    // Gateway de pagamento, etc.
}
```

## 🚀 Integração com Laravel

### 1. Service Provider

```php
// config/services.php
'innochannel' => [
    'api_key' => env('INNOCHANNEL_API_KEY'),
    'base_url' => env('INNOCHANNEL_BASE_URL', 'https://api.innochannel.com'),
    'webhook_secret' => env('INNOCHANNEL_WEBHOOK_SECRET'),
],
```

### 2. Controller

```php
<?php
// app/Http/Controllers/WebhookController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Innochannel\Sdk\Client;
use Innochannel\Sdk\Services\WebhookService;
use Innochannel\Sdk\Exceptions\ValidationException;
use Illuminate\Support\Facades\Log;
use App\Jobs\ProcessWebhookJob;

class WebhookController extends Controller
{
    private WebhookService $webhookService;

    public function __construct()
    {
        $client = new Client([
            'api_key' => config('services.innochannel.api_key'),
            'base_url' => config('services.innochannel.base_url'),
        ]);

        $this->webhookService = new WebhookService($client);
    }

    public function handle(Request $request): JsonResponse
    {
        try {
            $payload = $request->getContent();
            $signature = $request->header('X-Innochannel-Signature', '');
            $secret = config('services.innochannel.webhook_secret');

            // Validar e processar
            $data = $this->webhookService->processPayload($payload, $signature, $secret);

            Log::info('Webhook recebido', [
                'event' => $data['event'],
                'id' => $data['data']['id'] ?? null
            ]);

            // Processar de forma assíncrona
            ProcessWebhookJob::dispatch($data);

            return response()->json(['success' => true]);

        } catch (ValidationException $e) {
            Log::error('Webhook inválido', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Invalid signature'], 400);

        } catch (\Exception $e) {
            Log::error('Erro no webhook', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Internal error'], 500);
        }
    }
}
```

### 3. Job para Processamento Assíncrono

```php
<?php
// app/Jobs/ProcessWebhookJob.php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Reservation;
use Illuminate\Support\Facades\Log;

class ProcessWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private array $webhookData) {}

    public function handle(): void
    {
        $event = $this->webhookData['event'];
        $data = $this->webhookData['data'];

        Log::info('Processando webhook', ['event' => $event]);

        switch ($event) {
            case 'reservation.created':
                $this->handleNewReservation($data);
                break;

            case 'reservation.updated':
                $this->handleUpdatedReservation($data);
                break;

            case 'reservation.cancelled':
                $this->handleCancelledReservation($data);
                break;
        }
    }

    private function handleNewReservation(array $data): void
    {
        Reservation::updateOrCreate(
            ['innochannel_id' => $data['id']],
            [
                'guest_name' => $data['guest_name'],
                'guest_email' => $data['guest_email'],
                'check_in' => $data['check_in'],
                'check_out' => $data['check_out'],
                'status' => $data['status'],
                'total_amount' => $data['total_amount'],
            ]
        );

        Log::info('Nova reserva criada', ['id' => $data['id']]);
    }

    private function handleUpdatedReservation(array $data): void
    {
        $reservation = Reservation::where('innochannel_id', $data['id'])->first();
        
        if ($reservation) {
            $reservation->update([
                'status' => $data['status'],
                'guest_name' => $data['guest_name'],
                'check_in' => $data['check_in'],
                'check_out' => $data['check_out'],
            ]);
        }

        Log::info('Reserva atualizada', ['id' => $data['id']]);
    }

    private function handleCancelledReservation(array $data): void
    {
        $reservation = Reservation::where('innochannel_id', $data['id'])->first();
        
        if ($reservation) {
            $reservation->update([
                'status' => 'cancelled',
                'cancelled_at' => now()
            ]);
        }

        Log::info('Reserva cancelada', ['id' => $data['id']]);
    }
}
```

### 4. Rotas

```php
// routes/api.php
Route::post('/webhook/innochannel', [WebhookController::class, 'handle']);
```

### 5. Middleware de Validação

```php
<?php
// app/Http/Middleware/ValidateWebhook.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Innochannel\Sdk\Client;
use Innochannel\Sdk\Services\WebhookService;

class ValidateWebhook
{
    public function handle(Request $request, Closure $next)
    {
        $payload = $request->getContent();
        $signature = $request->header('X-Innochannel-Signature', '');
        $secret = config('services.innochannel.webhook_secret');

        $client = new Client([
            'api_key' => config('services.innochannel.api_key'),
            'base_url' => config('services.innochannel.base_url'),
        ]);

        $webhookService = new WebhookService($client);

        if (!$webhookService->validateSignature($payload, $signature, $secret)) {
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        return $next($request);
    }
}
```

## 🔐 Segurança e Validação

### Validação de Assinatura

```php
// Sempre validar assinaturas
$isValid = $webhookService->validateSignature($payload, $signature, $secret);

if (!$isValid) {
    // Rejeitar webhook
    http_response_code(401);
    exit('Invalid signature');
}
```

### Configuração de Secret Seguro

```php
// Gerar secret seguro
$secret = bin2hex(random_bytes(32)); // 64 caracteres hexadecimais

// Ou usar uma string forte
$secret = 'meu_secret_super_seguro_com_mais_de_16_caracteres_123456';
```

### Rate Limiting

```php
// Implementar rate limiting no endpoint
$ip = $_SERVER['REMOTE_ADDR'];
$key = "webhook_rate_limit_$ip";

// Usar Redis ou cache para controlar
if (cache_get($key) > 100) { // Máximo 100 requests por minuto
    http_response_code(429);
    exit('Rate limit exceeded');
}

cache_increment($key, 1, 60); // Incrementar por 1 minuto
```

## 📊 Monitoramento e Logs

### Logs Estruturados

```php
function logWebhook($event, $data, $status = 'success', $error = null)
{
    $logData = [
        'timestamp' => date('c'),
        'event' => $event,
        'status' => $status,
        'data_id' => $data['id'] ?? null,
        'processing_time' => microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'],
    ];

    if ($error) {
        $logData['error'] = $error;
    }

    error_log(json_encode($logData));
}
```

### Métricas de Performance

```php
$startTime = microtime(true);

// Processar webhook...

$processingTime = microtime(true) - $startTime;

// Log performance
if ($processingTime > 5.0) { // Mais de 5 segundos
    error_log("Webhook lento: {$processingTime}s para evento {$data['event']}");
}
```

### Monitoramento de Falhas

```php
// Obter logs de webhook
$logs = $webhookService->getLogs($webhookId, [
    'status' => 'failed',
    'limit' => 50
]);

foreach ($logs as $log) {
    echo "Falha em {$log['created_at']}: {$log['error_message']}\n";
    
    // Reenviar se necessário
    if ($log['retry_count'] < 3) {
        $webhookService->retry($webhookId, $log['id']);
    }
}
```

## ✅ Boas Práticas

### 1. Resposta Rápida

```php
// Responder rapidamente (< 15 segundos)
// Processar de forma assíncrona se necessário

// ❌ Ruim - processamento síncrono lento
processarReservaCompleta($data); // 30 segundos
echo json_encode(['success' => true]);

// ✅ Bom - resposta rápida + processamento assíncrono
adicionarFilaProcessamento($data); // < 1 segundo
echo json_encode(['success' => true]);
```

### 2. Idempotência

```php
function processarReserva($reservaData)
{
    $reservaId = $reservaData['id'];
    
    // Verificar se já foi processada
    if (jaFoiProcessada($reservaId)) {
        return; // Não processar novamente
    }
    
    // Processar...
    
    // Marcar como processada
    marcarComoProcessada($reservaId);
}
```

### 3. Tratamento de Erros Robusto

```php
try {
    processarEvento($data);
    
} catch (DatabaseException $e) {
    // Erro de banco - pode ser temporário
    error_log("Erro de banco: " . $e->getMessage());
    http_response_code(500); // Innochannel vai tentar novamente
    
} catch (ValidationException $e) {
    // Erro de validação - não tentar novamente
    error_log("Dados inválidos: " . $e->getMessage());
    http_response_code(200); // Não reenviar
    
} catch (Exception $e) {
    // Erro genérico
    error_log("Erro inesperado: " . $e->getMessage());
    http_response_code(500);
}
```

### 4. Configuração de Timeout

```php
// Configurar timeout adequado no webhook
$webhookData = [
    'url' => 'https://sua-app.com/webhook',
    'events' => ['reservation.created'],
    'timeout' => 15, // 15 segundos
    'retry_attempts' => 3
];
```

## 🔧 Troubleshooting

### Problemas Comuns

#### 1. Webhook não recebido

```php
// Verificar se webhook está ativo
$webhook = $webhookService->get($webhookId);
if (!$webhook['active']) {
    $webhookService->setActive($webhookId, true);
}

// Testar webhook
$testResult = $webhookService->test($webhookId);
if (!$testResult['success']) {
    echo "Erro no teste: " . $testResult['error'];
}
```

#### 2. Assinatura inválida

```php
// Debug da validação
$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_INNOCHANNEL_SIGNATURE'] ?? '';
$secret = 'seu_secret';

$expectedSignature = hash_hmac('sha256', $payload, $secret);

echo "Payload: " . $payload . "\n";
echo "Signature recebida: " . $signature . "\n";
echo "Signature esperada: " . $expectedSignature . "\n";
echo "Válida: " . (hash_equals($expectedSignature, $signature) ? 'Sim' : 'Não');
```

#### 3. Timeout no processamento

```php
// Configurar timeout do PHP
set_time_limit(30);

// Ou processar de forma assíncrona
function processarAsync($data) {
    // Adicionar à fila de processamento
    $redis = new Redis();
    $redis->lpush('webhook_queue', json_encode($data));
}
```

### Logs de Debug

```php
// Habilitar logs detalhados
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/webhook.log');

// Log de debug
function debugWebhook($message, $data = []) {
    $logEntry = [
        'timestamp' => date('c'),
        'message' => $message,
        'data' => $data,
        'request_id' => uniqid()
    ];
    
    error_log('[WEBHOOK DEBUG] ' . json_encode($logEntry));
}
```

## 📝 Exemplo Completo de Aplicação

```php
<?php
// app.php - Aplicação completa com webhook

require_once 'vendor/autoload.php';

use Innochannel\Sdk\Client;
use Innochannel\Sdk\Services\WebhookService;

class HotelApp
{
    private $client;
    private $webhookService;
    private $pdo;

    public function __construct($config)
    {
        $this->client = new Client($config);
        $this->webhookService = new WebhookService($this->client);
        $this->pdo = new PDO($config['database_dsn'], $config['db_user'], $config['db_pass']);
    }

    public function registrarWebhook($url)
    {
        return $this->webhookService->create([
            'url' => $url,
            'events' => [
                'reservation.created',
                'reservation.updated',
                'reservation.cancelled',
                'inventory.updated'
            ],
            'secret' => 'meu_secret_super_seguro_123456',
            'active' => true
        ]);
    }

    public function processarWebhook()
    {
        try {
            $payload = file_get_contents('php://input');
            $signature = $_SERVER['HTTP_X_INNOCHANNEL_SIGNATURE'] ?? '';
            $secret = 'meu_secret_super_seguro_123456';

            $data = $this->webhookService->processPayload($payload, $signature, $secret);

            $this->processarEvento($data);

            $response = $this->webhookService->createResponse(true);
            header('Content-Type: application/json');
            echo json_encode($response);

        } catch (Exception $e) {
            error_log("Erro no webhook: " . $e->getMessage());
            
            $response = $this->webhookService->createResponse(false, $e->getMessage());
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode($response);
        }
    }

    private function processarEvento($data)
    {
        switch ($data['event']) {
            case 'reservation.created':
                $this->criarReserva($data['data']);
                break;
            case 'reservation.updated':
                $this->atualizarReserva($data['data']);
                break;
            case 'reservation.cancelled':
                $this->cancelarReserva($data['data']);
                break;
            case 'inventory.updated':
                $this->atualizarInventario($data['data']);
                break;
        }
    }

    private function criarReserva($reserva)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO reservas (innochannel_id, guest_name, check_in, check_out, status) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $reserva['id'],
            $reserva['guest_name'],
            $reserva['check_in'],
            $reserva['check_out'],
            $reserva['status']
        ]);

        $this->enviarEmailConfirmacao($reserva);
    }

    private function atualizarReserva($reserva)
    {
        $stmt = $this->pdo->prepare("
            UPDATE reservas 
            SET guest_name = ?, check_in = ?, check_out = ?, status = ?
            WHERE innochannel_id = ?
        ");
        
        $stmt->execute([
            $reserva['guest_name'],
            $reserva['check_in'],
            $reserva['check_out'],
            $reserva['status'],
            $reserva['id']
        ]);
    }

    private function cancelarReserva($reserva)
    {
        $stmt = $this->pdo->prepare("
            UPDATE reservas 
            SET status = 'cancelled', cancelled_at = NOW()
            WHERE innochannel_id = ?
        ");
        
        $stmt->execute([$reserva['id']]);
    }

    private function atualizarInventario($inventario)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO inventario (property_id, room_type_id, date, availability) 
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE availability = VALUES(availability)
        ");
        
        $stmt->execute([
            $inventario['property_id'],
            $inventario['room_type_id'],
            $inventario['date'],
            $inventario['availability']
        ]);
    }

    private function enviarEmailConfirmacao($reserva)
    {
        // Implementar envio de email
        mail(
            $reserva['guest_email'],
            'Confirmação de Reserva',
            "Sua reserva #{$reserva['id']} foi confirmada!"
        );
    }
}

// Uso da aplicação
$config = [
    'api_key' => 'sua_api_key',
    'base_url' => 'https://api.innochannel.com',
    'database_dsn' => 'mysql:host=localhost;dbname=hotel',
    'db_user' => 'user',
    'db_pass' => 'pass'
];

$app = new HotelApp($config);

// Registrar webhook (executar uma vez)
if (isset($_GET['register'])) {
    $webhook = $app->registrarWebhook('https://sua-app.com/webhook.php');
    echo "Webhook registrado: " . $webhook['id'];
    exit;
}

// Processar webhook
$app->processarWebhook();
```

## 🎯 Resumo

Para usar webhooks na sua aplicação:

1. **Configure** o SDK com suas credenciais
2. **Registre** o webhook com os eventos desejados
3. **Implemente** um endpoint para receber webhooks
4. **Valide** sempre as assinaturas por segurança
5. **Processe** os eventos conforme sua lógica de negócio
6. **Responda** rapidamente (< 15 segundos)
7. **Monitore** logs e performance
8. **Implemente** retry logic para falhas

Os webhooks permitem que sua aplicação seja notificada em tempo real sobre mudanças no Innochannel, mantendo seus dados sempre sincronizados!