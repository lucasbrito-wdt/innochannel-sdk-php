# Guia de Integração Innochannel Laravel SDK

## 📋 Índice

1. [Visão Geral da Integração](#visão-geral-da-integração)
2. [Requisitos do Sistema](#requisitos-do-sistema)
3. [Passo a Passo para Implementação](#passo-a-passo-para-implementação)
4. [Exemplos de Código](#exemplos-de-código)
5. [Sistema de Eventos](#sistema-de-eventos)
6. [Melhores Práticas](#melhores-práticas)
7. [Solução de Problemas](#solução-de-problemas)

---

## 🎯 Visão Geral da Integração

O **Innochannel Laravel SDK** é uma solução completa para integração entre aplicações Laravel e a plataforma Innochannel. O SDK oferece:

### Funcionalidades Principais

- **Integração Nativa Laravel**: Service Providers, Facades, Artisan Commands e Middleware
- **Sistema de Webhooks**: Processamento automático de eventos em tempo real
- **Sincronização PMS**: Comunicação bidirecional com sistemas de gestão hoteleira
- **Cache Inteligente**: Sistema de cache configurável para otimização de performance
- **Eventos Laravel**: Sistema robusto de eventos para customização
- **Comandos Artisan**: Ferramentas de linha de comando para instalação e manutenção

### Arquitetura da Integração

```
┌─────────────────┐    ┌──────────────────┐    ┌─────────────────┐
│   Aplicação     │    │  Innochannel     │    │      PMS        │
│    Laravel      │◄──►│      SDK         │◄──►│    Sistema      │
└─────────────────┘    └──────────────────┘    └─────────────────┘
         │                       │                       │
         ▼                       ▼                       ▼
┌─────────────────┐    ┌──────────────────┐    ┌─────────────────┐
│    Eventos      │    │    Webhooks      │    │  Sincronização  │
│   & Listeners   │    │   & Callbacks    │    │   Automática    │
└─────────────────┘    └──────────────────┘    └─────────────────┘
```

---

## 🔧 Requisitos do Sistema

### Requisitos Mínimos

| Componente | Versão Mínima | Recomendado |
|------------|---------------|-------------|
| **PHP** | 8.1 | 8.2+ |
| **Laravel** | 10.0 | 11.x |
| **MySQL** | 5.7 | 8.0+ |
| **PostgreSQL** | 10.0 | 14.0+ |
| **Redis** | 5.0 | 7.0+ |
| **Composer** | 2.0 | 2.6+ |

### Extensões PHP Necessárias

```bash
# Extensões obrigatórias
php -m | grep -E "(json|curl|mbstring|openssl|pdo|tokenizer|xml)"

# Extensões recomendadas
php -m | grep -E "(redis|bcmath|gd|intl|zip)"
```

### Configuração do Servidor

```nginx
# Nginx - Configuração recomendada
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/your/laravel/public;
    
    # Configuração para webhooks
    location /innochannel/webhooks {
        try_files $uri $uri/ /index.php?$query_string;
        client_max_body_size 10M;
    }
}
```

---

## 🚀 Passo a Passo para Implementação

### Etapa 1: Instalação via Composer

```bash
# Instalar o pacote
composer require innochannel/laravel-sdk

# Verificar instalação
composer show innochannel/laravel-sdk
```

### Etapa 2: Executar Comando de Instalação

```bash
# Comando de instalação automática
php artisan innochannel:install

# O comando executará:
# ✓ Publicação dos arquivos de configuração
# ✓ Execução das migrations
# ✓ Criação de diretórios necessários
# ✓ Configuração inicial
```

### Etapa 3: Configuração do Ambiente

Adicione as seguintes variáveis ao seu arquivo `.env`:

```env
# === CONFIGURAÇÃO DA API INNOCHANNEL ===
INNOCHANNEL_API_URL=https://api.innochannel.com
INNOCHANNEL_API_KEY=sua_api_key_aqui
INNOCHANNEL_API_SECRET=seu_api_secret_aqui
INNOCHANNEL_ENVIRONMENT=production

# === CONFIGURAÇÃO DO PMS ===
INNOCHANNEL_PMS_SYSTEM=opera
INNOCHANNEL_PMS_URL=https://seu-pms.com/api
INNOCHANNEL_PMS_USERNAME=usuario_pms
INNOCHANNEL_PMS_PASSWORD=senha_pms

# === CONFIGURAÇÃO DE WEBHOOKS ===
INNOCHANNEL_WEBHOOK_SECRET=webhook_secret_seguro
INNOCHANNEL_WEBHOOK_VERIFY_SIGNATURE=true
INNOCHANNEL_WEBHOOK_TIMEOUT=30

# === CONFIGURAÇÃO DE CACHE ===
INNOCHANNEL_CACHE_DRIVER=redis
INNOCHANNEL_CACHE_TTL=3600
INNOCHANNEL_CACHE_PREFIX=innochannel

# === CONFIGURAÇÃO DE LOGS ===
INNOCHANNEL_LOG_LEVEL=info
INNOCHANNEL_DEBUG=false
```

### Etapa 4: Teste de Conectividade

```bash
# Testar conexão com a API
php artisan innochannel:test-connection

# Saída esperada:
# ✓ Conectividade com API: OK
# ✓ Autenticação: OK
# ✓ Endpoints disponíveis: OK
# ✓ Configuração PMS: OK
```

### Etapa 5: Configuração de Webhooks

```bash
# Registrar webhooks na plataforma Innochannel
curl -X POST https://api.innochannel.com/webhooks \
  -H "Authorization: Bearer $INNOCHANNEL_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "url": "https://seu-dominio.com/innochannel/webhooks/booking",
    "events": ["booking.created", "booking.updated", "booking.cancelled"],
    "secret": "webhook_secret_seguro"
  }'
```

---

## 💻 Exemplos de Código

### Usando a Facade Innochannel

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Innochannel;
use Illuminate\Http\JsonResponse;

class HotelController extends Controller
{
    /**
     * Listar propriedades disponíveis
     */
    public function getProperties(): JsonResponse
    {
        try {
            $properties = Innochannel::getProperties([
                'status' => 'active',
                'limit' => 50
            ]);

            return response()->json([
                'success' => true,
                'data' => $properties,
                'total' => count($properties)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter detalhes de uma propriedade
     */
    public function getProperty(string $propertyId): JsonResponse
    {
        try {
            $property = Innochannel::getProperty($propertyId);

            return response()->json([
                'success' => true,
                'data' => $property
            ]);

        } catch (\Innochannel\Exceptions\NotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Propriedade não encontrada'
            ], 404);
        }
    }

    /**
     * Criar nova reserva
     */
    public function createBooking(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'property_id' => 'required|string',
            'guest.name' => 'required|string|max:255',
            'guest.email' => 'required|email',
            'guest.phone' => 'required|string',
            'check_in' => 'required|date|after:today',
            'check_out' => 'required|date|after:check_in',
            'rooms' => 'required|integer|min:1',
            'adults' => 'required|integer|min:1',
            'children' => 'integer|min:0'
        ]);

        try {
            $booking = Innochannel::createBooking($validated);

            return response()->json([
                'success' => true,
                'data' => $booking,
                'booking_id' => $booking['id']
            ], 201);

        } catch (\Innochannel\Exceptions\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Dados inválidos',
                'details' => $e->getValidationErrors()
            ], 422);
        }
    }

    /**
     * Atualizar inventário
     */
    public function updateInventory(Request $request, string $propertyId): JsonResponse
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'room_type' => 'required|string',
            'availability' => 'required|integer|min:0',
            'rate' => 'required|numeric|min:0',
            'restrictions' => 'array'
        ]);

        try {
            $result = Innochannel::updateInventory($propertyId, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Inventário atualizado com sucesso',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
```

### Usando Injeção de Dependência

```php
<?php

namespace App\Services;

use Innochannel\Core\InnochannelClient;
use Innochannel\Exceptions\InnochannelException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class BookingService
{
    public function __construct(
        private InnochannelClient $innochannel
    ) {}

    /**
     * Processar nova reserva com validações
     */
    public function processBooking(array $bookingData): array
    {
        // Validar disponibilidade
        $availability = $this->checkAvailability(
            $bookingData['property_id'],
            $bookingData['check_in'],
            $bookingData['check_out'],
            $bookingData['rooms']
        );

        if (!$availability['available']) {
            throw new \Exception('Não há disponibilidade para as datas selecionadas');
        }

        // Calcular preço total
        $pricing = $this->calculatePricing($bookingData, $availability['rates']);
        $bookingData['total_amount'] = $pricing['total'];
        $bookingData['breakdown'] = $pricing['breakdown'];

        // Criar reserva
        try {
            $booking = $this->innochannel->createBooking($bookingData);
            
            // Log da operação
            Log::info('Nova reserva criada', [
                'booking_id' => $booking['id'],
                'property_id' => $bookingData['property_id'],
                'guest_email' => $bookingData['guest']['email'],
                'total_amount' => $bookingData['total_amount']
            ]);

            // Limpar cache relacionado
            $this->clearRelatedCache($bookingData['property_id']);

            return $booking;

        } catch (InnochannelException $e) {
            Log::error('Erro ao criar reserva', [
                'error' => $e->getMessage(),
                'booking_data' => $bookingData
            ]);
            throw $e;
        }
    }

    /**
     * Verificar disponibilidade
     */
    private function checkAvailability(string $propertyId, string $checkIn, string $checkOut, int $rooms): array
    {
        $cacheKey = "availability:{$propertyId}:{$checkIn}:{$checkOut}:{$rooms}";
        
        return Cache::remember($cacheKey, 300, function () use ($propertyId, $checkIn, $checkOut, $rooms) {
            return $this->innochannel->checkAvailability([
                'property_id' => $propertyId,
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'rooms' => $rooms
            ]);
        });
    }

    /**
     * Calcular preços
     */
    private function calculatePricing(array $bookingData, array $rates): array
    {
        $nights = \Carbon\Carbon::parse($bookingData['check_in'])
            ->diffInDays(\Carbon\Carbon::parse($bookingData['check_out']));

        $subtotal = $rates['base_rate'] * $nights * $bookingData['rooms'];
        $taxes = $subtotal * 0.1; // 10% de impostos
        $total = $subtotal + $taxes;

        return [
            'total' => $total,
            'breakdown' => [
                'subtotal' => $subtotal,
                'taxes' => $taxes,
                'nights' => $nights,
                'base_rate' => $rates['base_rate']
            ]
        ];
    }

    /**
     * Limpar cache relacionado
     */
    private function clearRelatedCache(string $propertyId): void
    {
        $keys = [
            "availability:{$propertyId}:*",
            "property:{$propertyId}:rates",
            "inventory:{$propertyId}"
        ];

        foreach ($keys as $pattern) {
            Cache::tags(['innochannel', $propertyId])->flush();
        }
    }
}
```

### Operações de Sincronização

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Innochannel;
use Illuminate\Support\Facades\Log;

class SyncInventoryCommand extends Command
{
    protected $signature = 'hotel:sync-inventory 
                           {property_id : ID da propriedade}
                           {--from= : Data inicial (Y-m-d)}
                           {--to= : Data final (Y-m-d)}
                           {--dry-run : Executar sem aplicar mudanças}';

    protected $description = 'Sincronizar inventário com o PMS';

    public function handle(): int
    {
        $propertyId = $this->argument('property_id');
        $from = $this->option('from') ?? now()->format('Y-m-d');
        $to = $this->option('to') ?? now()->addDays(30)->format('Y-m-d');
        $dryRun = $this->option('dry-run');

        $this->info("Iniciando sincronização de inventário...");
        $this->info("Propriedade: {$propertyId}");
        $this->info("Período: {$from} até {$to}");

        if ($dryRun) {
            $this->warn("MODO DRY-RUN: Nenhuma alteração será aplicada");
        }

        try {
            // Obter dados do PMS
            $this->info("Obtendo dados do PMS...");
            $pmsData = Innochannel::getPmsInventory($propertyId, $from, $to);
            
            $this->info("Encontrados " . count($pmsData) . " registros no PMS");

            // Obter dados atuais do Innochannel
            $this->info("Obtendo dados atuais do Innochannel...");
            $currentData = Innochannel::getInventory($propertyId, $from, $to);

            // Comparar e identificar diferenças
            $differences = $this->compareInventory($pmsData, $currentData);
            
            if (empty($differences)) {
                $this->info("✓ Inventário já está sincronizado");
                return 0;
            }

            $this->warn("Encontradas " . count($differences) . " diferenças");

            // Mostrar diferenças
            $this->table(
                ['Data', 'Tipo Quarto', 'Campo', 'PMS', 'Atual', 'Ação'],
                array_map(function ($diff) {
                    return [
                        $diff['date'],
                        $diff['room_type'],
                        $diff['field'],
                        $diff['pms_value'],
                        $diff['current_value'],
                        $diff['action']
                    ];
                }, $differences)
            );

            if (!$dryRun) {
                if ($this->confirm('Aplicar as alterações?')) {
                    $this->applyChanges($propertyId, $differences);
                    $this->info("✓ Sincronização concluída com sucesso");
                } else {
                    $this->info("Sincronização cancelada pelo usuário");
                }
            }

            return 0;

        } catch (\Exception $e) {
            $this->error("Erro durante a sincronização: " . $e->getMessage());
            Log::error('Erro na sincronização de inventário', [
                'property_id' => $propertyId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    private function compareInventory(array $pmsData, array $currentData): array
    {
        $differences = [];
        
        foreach ($pmsData as $date => $pmsRooms) {
            foreach ($pmsRooms as $roomType => $pmsRoom) {
                $currentRoom = $currentData[$date][$roomType] ?? [];
                
                // Comparar disponibilidade
                if (($pmsRoom['availability'] ?? 0) !== ($currentRoom['availability'] ?? 0)) {
                    $differences[] = [
                        'date' => $date,
                        'room_type' => $roomType,
                        'field' => 'availability',
                        'pms_value' => $pmsRoom['availability'] ?? 0,
                        'current_value' => $currentRoom['availability'] ?? 0,
                        'action' => 'update'
                    ];
                }
                
                // Comparar tarifas
                if (($pmsRoom['rate'] ?? 0) !== ($currentRoom['rate'] ?? 0)) {
                    $differences[] = [
                        'date' => $date,
                        'room_type' => $roomType,
                        'field' => 'rate',
                        'pms_value' => $pmsRoom['rate'] ?? 0,
                        'current_value' => $currentRoom['rate'] ?? 0,
                        'action' => 'update'
                    ];
                }
            }
        }
        
        return $differences;
    }

    private function applyChanges(string $propertyId, array $differences): void
    {
        $progressBar = $this->output->createProgressBar(count($differences));
        $progressBar->start();

        foreach ($differences as $diff) {
            try {
                Innochannel::updateInventory($propertyId, [
                    'date' => $diff['date'],
                    'room_type' => $diff['room_type'],
                    $diff['field'] => $diff['pms_value']
                ]);
                
                $progressBar->advance();
                
            } catch (\Exception $e) {
                $this->error("\nErro ao atualizar {$diff['date']} - {$diff['room_type']}: " . $e->getMessage());
            }
        }

        $progressBar->finish();
        $this->newLine();
    }
}
```

---

## 🎭 Sistema de Eventos

### Eventos Disponíveis

O SDK dispara eventos Laravel para todos os webhooks recebidos, permitindo que sua aplicação responda a mudanças em tempo real.

#### 1. BookingWebhookReceived

Disparado quando webhooks de reservas são recebidos.

```php
<?php

namespace App\Listeners;

use Innochannel\Laravel\Events\BookingWebhookReceived;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\BookingConfirmation;
use App\Models\LocalBooking;

class HandleBookingWebhook
{
    public function handle(BookingWebhookReceived $event): void
    {
        $bookingId = $event->getBookingId();
        $eventType = $event->getEventType();
        $bookingData = $event->getBookingData();

        Log::info("Processando webhook de reserva", [
            'booking_id' => $bookingId,
            'event_type' => $eventType,
            'timestamp' => now()
        ]);

        match ($eventType) {
            'booking.created' => $this->handleBookingCreated($bookingData),
            'booking.updated' => $this->handleBookingUpdated($bookingData),
            'booking.cancelled' => $this->handleBookingCancelled($bookingData),
            'booking.modified' => $this->handleBookingModified($bookingData),
            default => $this->handleUnknownEvent($eventType, $bookingData),
        };
    }

    private function handleBookingCreated(array $bookingData): void
    {
        // Criar registro local
        $localBooking = LocalBooking::create([
            'innochannel_id' => $bookingData['id'],
            'property_id' => $bookingData['property_id'],
            'guest_name' => $bookingData['guest']['name'],
            'guest_email' => $bookingData['guest']['email'],
            'check_in' => $bookingData['check_in'],
            'check_out' => $bookingData['check_out'],
            'status' => 'confirmed',
            'total_amount' => $bookingData['total_amount']
        ]);

        // Enviar email de confirmação
        Mail::to($bookingData['guest']['email'])
            ->send(new BookingConfirmation($localBooking));

        // Sincronizar com PMS
        $this->syncWithPms($bookingData);

        Log::info("Nova reserva processada", [
            'local_id' => $localBooking->id,
            'innochannel_id' => $bookingData['id']
        ]);
    }

    private function handleBookingUpdated(array $bookingData): void
    {
        $localBooking = LocalBooking::where('innochannel_id', $bookingData['id'])->first();
        
        if ($localBooking) {
            $localBooking->update([
                'guest_name' => $bookingData['guest']['name'],
                'guest_email' => $bookingData['guest']['email'],
                'check_in' => $bookingData['check_in'],
                'check_out' => $bookingData['check_out'],
                'total_amount' => $bookingData['total_amount']
            ]);

            // Notificar sobre mudanças
            $this->notifyBookingChanges($localBooking, $bookingData['changes'] ?? []);
        }
    }

    private function handleBookingCancelled(array $bookingData): void
    {
        $localBooking = LocalBooking::where('innochannel_id', $bookingData['id'])->first();
        
        if ($localBooking) {
            $localBooking->update(['status' => 'cancelled']);
            
            // Processar reembolso se aplicável
            $this->processRefund($localBooking, $bookingData);
            
            // Liberar inventário
            $this->releaseInventory($bookingData);
        }
    }

    private function syncWithPms(array $bookingData): void
    {
        // Implementar sincronização com PMS
        // Esta é uma implementação exemplo
        try {
            \Innochannel::syncBookingWithPms($bookingData['id']);
        } catch (\Exception $e) {
            Log::error("Erro ao sincronizar com PMS", [
                'booking_id' => $bookingData['id'],
                'error' => $e->getMessage()
            ]);
        }
    }
}
```

#### 2. PropertyWebhookReceived

Disparado para eventos relacionados a propriedades.

```php
<?php

namespace App\Listeners;

use Innochannel\Laravel\Events\PropertyWebhookReceived;
use App\Models\Property;
use Illuminate\Support\Facades\Cache;

class HandlePropertyWebhook
{
    public function handle(PropertyWebhookReceived $event): void
    {
        $propertyId = $event->getPropertyId();
        $eventType = $event->getEventType();
        $propertyData = $event->getPropertyData();

        match ($eventType) {
            'property.created' => $this->handlePropertyCreated($propertyData),
            'property.updated' => $this->handlePropertyUpdated($propertyData),
            'property.deleted' => $this->handlePropertyDeleted($propertyData),
            'property.status_changed' => $this->handleStatusChanged($propertyData),
            default => Log::warning("Evento de propriedade desconhecido: {$eventType}"),
        };
    }

    private function handlePropertyCreated(array $propertyData): void
    {
        // Criar propriedade local
        Property::create([
            'innochannel_id' => $propertyData['id'],
            'name' => $propertyData['name'],
            'type' => $propertyData['type'],
            'address' => $propertyData['address'],
            'city' => $propertyData['city'],
            'country' => $propertyData['country'],
            'status' => 'active'
        ]);

        // Configurar inventário inicial
        $this->setupInitialInventory($propertyData);
    }

    private function handlePropertyUpdated(array $propertyData): void
    {
        $property = Property::where('innochannel_id', $propertyData['id'])->first();
        
        if ($property) {
            $property->update([
                'name' => $propertyData['name'],
                'address' => $propertyData['address'],
                'city' => $propertyData['city'],
                'country' => $propertyData['country']
            ]);

            // Limpar cache relacionado
            Cache::tags(['properties', "property:{$propertyData['id']}"])->flush();
        }
    }
}
```

#### 3. InventoryWebhookReceived

Disparado para atualizações de inventário.

```php
<?php

namespace App\Listeners;

use Innochannel\Laravel\Events\InventoryWebhookReceived;
use App\Models\Inventory;
use Illuminate\Support\Facades\Cache;

class HandleInventoryWebhook
{
    public function handle(InventoryWebhookReceived $event): void
    {
        $propertyId = $event->getPropertyId();
        $eventType = $event->getEventType();
        $inventoryData = $event->getInventoryData();

        match ($eventType) {
            'inventory.rates_updated' => $this->handleRatesUpdated($inventoryData),
            'inventory.availability_updated' => $this->handleAvailabilityUpdated($inventoryData),
            'inventory.restrictions_updated' => $this->handleRestrictionsUpdated($inventoryData),
            'inventory.bulk_updated' => $this->handleBulkUpdated($inventoryData),
            default => Log::warning("Evento de inventário desconhecido: {$eventType}"),
        };

        // Limpar cache de inventário
        $this->clearInventoryCache($propertyId, $event->getDateRange());
    }

    private function handleRatesUpdated(array $inventoryData): void
    {
        foreach ($inventoryData['rates'] as $rate) {
            Inventory::updateOrCreate(
                [
                    'property_id' => $inventoryData['property_id'],
                    'room_type' => $rate['room_type'],
                    'date' => $rate['date']
                ],
                [
                    'rate' => $rate['amount'],
                    'currency' => $rate['currency'],
                    'updated_at' => now()
                ]
            );
        }

        // Notificar sistema de revenue management
        $this->notifyRevenueSystem($inventoryData);
    }

    private function handleAvailabilityUpdated(array $inventoryData): void
    {
        foreach ($inventoryData['availability'] as $availability) {
            Inventory::updateOrCreate(
                [
                    'property_id' => $inventoryData['property_id'],
                    'room_type' => $availability['room_type'],
                    'date' => $availability['date']
                ],
                [
                    'availability' => $availability['count'],
                    'updated_at' => now()
                ]
            );
        }

        // Verificar alertas de overbooking
        $this->checkOverbookingAlerts($inventoryData);
    }
}
```

#### 4. GeneralWebhookReceived

Disparado para eventos gerais do sistema.

```php
<?php

namespace App\Listeners;

use Innochannel\Laravel\Events\GeneralWebhookReceived;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use App\Notifications\SystemAlert;

class HandleGeneralWebhook
{
    public function handle(GeneralWebhookReceived $event): void
    {
        if ($event->isSystemNotification()) {
            $this->handleSystemNotification($event);
        } elseif ($event->isMaintenanceEvent()) {
            $this->handleMaintenanceEvent($event);
        } elseif ($event->isApiStatusEvent()) {
            $this->handleApiStatusEvent($event);
        }
    }

    private function handleSystemNotification(GeneralWebhookReceived $event): void
    {
        $message = $event->getNotificationMessage();
        $severity = $event->getSeverityLevel();

        // Armazenar notificação
        Cache::put("system_notification:" . time(), [
            'message' => $message,
            'severity' => $severity,
            'timestamp' => now()
        ], now()->addHours(24));

        // Enviar alertas baseados na severidade
        if ($severity === 'critical') {
            // Notificar administradores imediatamente
            $admins = User::where('role', 'admin')->get();
            Notification::send($admins, new SystemAlert($message, $severity));
        }
    }

    private function handleMaintenanceEvent(GeneralWebhookReceived $event): void
    {
        $eventData = $event->getEventData();
        
        // Armazenar status de manutenção
        Cache::put('maintenance_status', [
            'type' => $eventData['maintenance_type'],
            'scheduled_time' => $eventData['scheduled_time'],
            'duration' => $eventData['estimated_duration'],
            'status' => $eventData['status']
        ], now()->addHours(48));

        // Preparar sistema para manutenção
        if ($eventData['status'] === 'starting') {
            $this->prepareForMaintenance();
        }
    }
}
```

### Registrando Event Listeners

No seu `EventServiceProvider`:

```php
<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Innochannel\Laravel\Events\BookingWebhookReceived;
use Innochannel\Laravel\Events\PropertyWebhookReceived;
use Innochannel\Laravel\Events\InventoryWebhookReceived;
use Innochannel\Laravel\Events\GeneralWebhookReceived;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        BookingWebhookReceived::class => [
            \App\Listeners\HandleBookingWebhook::class,
            \App\Listeners\UpdateLocalBooking::class,
            \App\Listeners\SyncWithPms::class,
        ],
        
        PropertyWebhookReceived::class => [
            \App\Listeners\HandlePropertyWebhook::class,
            \App\Listeners\UpdateSearchIndex::class,
        ],
        
        InventoryWebhookReceived::class => [
            \App\Listeners\HandleInventoryWebhook::class,
            \App\Listeners\UpdatePricingEngine::class,
        ],
        
        GeneralWebhookReceived::class => [
            \App\Listeners\HandleGeneralWebhook::class,
            \App\Listeners\LogSystemEvents::class,
        ],
    ];
}
```

---

## 🏆 Melhores Práticas

### 1. Configuração e Segurança

```php
// ✅ BOM: Usar variáveis de ambiente
$apiKey = env('INNOCHANNEL_API_KEY');

// ❌ RUIM: Hardcoded credentials
$apiKey = 'sk_live_abc123...';

// ✅ BOM: Validar assinatura de webhooks
if (!$this->verifyWebhookSignature($request)) {
    abort(401, 'Invalid signature');
}

// ✅ BOM: Usar HTTPS em produção
if (app()->environment('production') && !$request->secure()) {
    abort(403, 'HTTPS required');
}
```

### 2. Tratamento de Erros

```php
// ✅ BOM: Tratamento específico de exceções
try {
    $booking = Innochannel::createBooking($data);
} catch (ValidationException $e) {
    // Tratar erros de validação
    return response()->json([
        'error' => 'Dados inválidos',
        'details' => $e->getValidationErrors()
    ], 422);
} catch (RateLimitException $e) {
    // Implementar retry com backoff
    return $this->retryWithBackoff($data);
} catch (ApiException $e) {
    // Log e notificar administradores
    Log::error('API Error', ['error' => $e->getMessage()]);
    return response()->json(['error' => 'Erro interno'], 500);
}
```

### 3. Cache Estratégico

```php
// ✅ BOM: Cache com TTL apropriado
$properties = Cache::remember('properties:active', 3600, function () {
    return Innochannel::getProperties(['status' => 'active']);
});

// ✅ BOM: Cache com tags para invalidação seletiva
Cache::tags(['inventory', "property:{$propertyId}"])
    ->put("rates:{$propertyId}:{$date}", $rates, 1800);

// ✅ BOM: Invalidação inteligente de cache
public function clearPropertyCache(string $propertyId): void
{
    $patterns = [
        "property:{$propertyId}",
        "inventory:{$propertyId}:*",
        "rates:{$propertyId}:*"
    ];
    
    foreach ($patterns as $pattern) {
        Cache::tags(['properties', $propertyId])->flush();
    }
}
```

### 4. Logging e Monitoramento

```php
// ✅ BOM: Logs estruturados
Log::info('Booking created', [
    'booking_id' => $booking['id'],
    'property_id' => $booking['property_id'],
    'guest_email' => $booking['guest']['email'],
    'amount' => $booking['total_amount'],
    'source' => 'innochannel_webhook'
]);

// ✅ BOM: Métricas de performance
$startTime = microtime(true);
$result = Innochannel::getProperties();
$duration = microtime(true) - $startTime;

Log::debug('API call performance', [
    'endpoint' => 'getProperties',
    'duration_ms' => round($duration * 1000, 2),
    'result_count' => count($result)
]);
```

### 5. Processamento de Webhooks

```php
// ✅ BOM: Processamento assíncrono
public function handleWebhook(Request $request)
{
    // Validar rapidamente
    if (!$this->isValidWebhook($request)) {
        return response('Invalid', 400);
    }
    
    // Processar em background
    ProcessWebhookJob::dispatch($request->all());
    
    // Responder rapidamente
    return response('OK', 200);
}

// ✅ BOM: Idempotência
public function processBookingWebhook(array $webhookData)
{
    $webhookId = $webhookData['webhook_id'];
    
    // Verificar se já foi processado
    if (Cache::has("processed_webhook:{$webhookId}")) {
        Log::info("Webhook já processado: {$webhookId}");
        return;
    }
    
    // Processar
    $this->handleBookingEvent($webhookData);
    
    // Marcar como processado
    Cache::put("processed_webhook:{$webhookId}", true, 86400);
}
```

### 6. Sincronização com PMS

```php
// ✅ BOM: Sincronização incremental
public function syncInventory(string $propertyId, ?Carbon $since = null): void
{
    $since = $since ?? $this->getLastSyncTime($propertyId);
    
    $changes = Innochannel::getInventoryChanges($propertyId, $since);
    
    foreach ($changes as $change) {
        $this->applySingleChange($change);
        $this->updateSyncStatus($propertyId, $change['timestamp']);
    }
}

// ✅ BOM: Retry com exponential backoff
public function syncWithRetry(callable $operation, int $maxRetries = 3): mixed
{
    $attempt = 0;
    
    while ($attempt < $maxRetries) {
        try {
            return $operation();
        } catch (Exception $e) {
            $attempt++;
            $delay = pow(2, $attempt); // 2, 4, 8 segundos
            
            if ($attempt >= $maxRetries) {
                throw $e;
            }
            
            Log::warning("Sync failed, retrying in {$delay}s", [
                'attempt' => $attempt,
                'error' => $e->getMessage()
            ]);
            
            sleep($delay);
        }
    }
}
```

---

## 🔧 Solução de Problemas

### Problemas Comuns e Soluções

#### 1. Erro de Autenticação

**Problema**: `AuthenticationException: Invalid API credentials`

**Soluções**:
```bash
# Verificar variáveis de ambiente
php artisan config:clear
php artisan cache:clear

# Testar credenciais
php artisan innochannel:test-connection

# Verificar configuração
php artisan config:show innochannel
```

#### 2. Webhooks Não Recebidos

**Problema**: Webhooks não chegam à aplicação

**Diagnóstico**:
```bash
# Verificar logs do servidor web
tail -f /var/log/nginx/error.log

# Testar endpoint manualmente
curl -X POST https://seu-dominio.com/innochannel/webhooks/booking \
  -H "Content-Type: application/json" \
  -d '{"test": true}'

# Verificar firewall
sudo ufw status
```

**Soluções**:
```php
// Verificar middleware
Route::post('/innochannel/webhooks/{type}', [WebhookController::class, 'handle'])
    ->middleware(['api'])  // Remover auth se necessário
    ->where('type', 'booking|property|inventory|general');

// Debug de webhooks
Log::info('Webhook received', [
    'headers' => $request->headers->all(),
    'body' => $request->getContent(),
    'ip' => $request->ip()
]);
```

#### 3. Problemas de Performance

**Problema**: Lentidão nas chamadas da API

**Diagnóstico**:
```php
// Adicionar logging de performance
$start = microtime(true);
$result = Innochannel::getProperties();
$duration = microtime(true) - $start;

Log::info('API Performance', [
    'method' => 'getProperties',
    'duration' => $duration,
    'memory_usage' => memory_get_peak_usage(true)
]);
```

**Soluções**:
```php
// Implementar cache agressivo
$properties = Cache::remember('properties:all', 1800, function () {
    return Innochannel::getProperties();
});

// Usar paginação
$properties = Innochannel::getProperties([
    'limit' => 50,
    'offset' => 0
]);

// Implementar circuit breaker
if ($this->circuitBreaker->isOpen()) {
    return $this->getFallbackData();
}
```

#### 4. Erros de Sincronização

**Problema**: Dados inconsistentes entre sistemas

**Diagnóstico**:
```bash
# Verificar logs de sincronização
php artisan log:show --filter="sync"

# Executar sincronização manual
php artisan innochannel:sync --property-id=123 --dry-run

# Verificar status de sincronização
SELECT * FROM innochannel_sync_status 
WHERE property_id = '123' 
ORDER BY last_sync_at DESC;
```

**Soluções**:
```php
// Implementar reconciliação
public function reconcileData(string $propertyId): array
{
    $pmsData = $this->getPmsData($propertyId);
    $innochannelData = $this->getInnochannelData($propertyId);
    
    $differences = $this->compareData($pmsData, $innochannelData);
    
    foreach ($differences as $diff) {
        $this->resolveDifference($diff);
    }
    
    return $differences;
}

// Adicionar checksums para validação
public function validateDataIntegrity(array $data): bool
{
    $expectedChecksum = $data['checksum'] ?? null;
    $calculatedChecksum = md5(json_encode($data['payload']));
    
    return $expectedChecksum === $calculatedChecksum;
}
```

#### 5. Problemas de Rate Limiting

**Problema**: `RateLimitException: Too many requests`

**Soluções**:
```php
// Implementar retry com backoff
public function callWithRetry(callable $operation, int $maxRetries = 3): mixed
{
    $attempt = 0;
    
    while ($attempt < $maxRetries) {
        try {
            return $operation();
        } catch (RateLimitException $e) {
            $retryAfter = $e->getRetryAfter() ?? pow(2, $attempt);
            
            Log::warning("Rate limited, waiting {$retryAfter}s", [
                'attempt' => $attempt + 1,
                'max_retries' => $maxRetries
            ]);
            
            sleep($retryAfter);
            $attempt++;
        }
    }
    
    throw new Exception('Max retries exceeded');
}

// Implementar queue para operações em lote
Queue::bulk([
    new SyncInventoryJob($propertyId, $date1),
    new SyncInventoryJob($propertyId, $date2),
    new SyncInventoryJob($propertyId, $date3),
], 'low-priority');
```

### Ferramentas de Debug

#### 1. Comando de Diagnóstico

```bash
# Criar comando personalizado
php artisan make:command DiagnoseInnochannelCommand
```

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Innochannel;

class DiagnoseInnochannelCommand extends Command
{
    protected $signature = 'innochannel:diagnose';
    protected $description = 'Diagnosticar problemas de integração';

    public function handle(): int
    {
        $this->info('🔍 Diagnóstico Innochannel SDK');
        $this->newLine();

        // Verificar configuração
        $this->checkConfiguration();
        
        // Testar conectividade
        $this->testConnectivity();
        
        // Verificar banco de dados
        $this->checkDatabase();
        
        // Verificar cache
        $this->checkCache();
        
        // Verificar webhooks
        $this->checkWebhooks();

        return 0;
    }

    private function checkConfiguration(): void
    {
        $this->info('📋 Verificando configuração...');
        
        $required = [
            'INNOCHANNEL_API_KEY',
            'INNOCHANNEL_API_SECRET',
            'INNOCHANNEL_API_URL'
        ];
        
        foreach ($required as $key) {
            $value = env($key);
            if (empty($value)) {
                $this->error("❌ {$key} não configurado");
            } else {
                $this->info("✅ {$key} configurado");
            }
        }
    }

    private function testConnectivity(): void
    {
        $this->info('🌐 Testando conectividade...');
        
        try {
            $response = Innochannel::testConnection();
            $this->info('✅ Conexão com API: OK');
        } catch (\Exception $e) {
            $this->error("❌ Erro de conexão: " . $e->getMessage());
        }
    }

    private function checkDatabase(): void
    {
        $this->info('🗄️ Verificando banco de dados...');
        
        $tables = [
            'innochannel_cache',
            'innochannel_webhooks',
            'innochannel_sync_status'
        ];
        
        foreach ($tables as $table) {
            try {
                \DB::table($table)->count();
                $this->info("✅ Tabela {$table}: OK");
            } catch (\Exception $e) {
                $this->error("❌ Tabela {$table}: " . $e->getMessage());
            }
        }
    }
}
```

#### 2. Middleware de Debug

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DebugInnochannelWebhooks
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->is('innochannel/webhooks/*')) {
            Log::debug('Webhook Debug', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'headers' => $request->headers->all(),
                'body' => $request->getContent(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
        }

        return $next($request);
    }
}
```

### Monitoramento e Alertas

```php
// Implementar health checks
Route::get('/health/innochannel', function () {
    try {
        $status = Innochannel::getSystemStatus();
        
        return response()->json([
            'status' => 'healthy',
            'api_status' => $status['api_status'],
            'last_sync' => $status['last_sync'],
            'webhook_status' => $status['webhook_status']
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'unhealthy',
            'error' => $e->getMessage()
        ], 503);
    }
});

// Alertas automáticos
if ($errorRate > 0.05) { // 5% de erro
    Notification::route('slack', config('slack.webhook'))
        ->notify(new HighErrorRateAlert($errorRate));
}
```

---

## 📞 Suporte e Recursos

### Documentação Adicional
- [API Reference](https://docs.innochannel.com/api)
- [Webhook Guide](https://docs.innochannel.com/webhooks)
- [PMS Integration](https://docs.innochannel.com/pms)

### Suporte Técnico
- **Email**: support@innochannel.com
- **Slack**: [Innochannel Developers](https://innochannel-dev.slack.com)
- **GitHub Issues**: [innochannel/laravel-sdk](https://github.com/innochannel/laravel-sdk/issues)

### Recursos da Comunidade
- [Stack Overflow](https://stackoverflow.com/questions/tagged/innochannel)
- [Discord Server](https://discord.gg/innochannel)
- [Developer Blog](https://blog.innochannel.com/developers)

---

*Documentação atualizada em: {{ date('Y-m-d') }}*
*Versão do SDK: 1.0.0*