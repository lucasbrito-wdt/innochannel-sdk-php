# Eventos e Listeners para Room e Rate Plan

## Visão Geral

Este documento descreve a implementação dos eventos e listeners para as entidades Room (Quarto) e Rate Plan (Plano de Tarifas) no SDK Innochannel.

## Eventos Disponíveis

### Room Events

Os seguintes eventos estão disponíveis para a entidade Room:

- **RoomCreated**: Disparado quando um novo quarto é criado
- **RoomUpdated**: Disparado quando um quarto é atualizado
- **RoomDeleted**: Disparado quando um quarto é excluído

### Rate Plan Events

Os seguintes eventos estão disponíveis para a entidade Rate Plan:

- **RatePlanCreated**: Disparado quando um novo plano de tarifas é criado
- **RatePlanUpdated**: Disparado quando um plano de tarifas é atualizado
- **RatePlanDeleted**: Disparado quando um plano de tarifas é excluído

## Listeners Implementados

### RoomWebhookListener

Localização: `src/Laravel/Listeners/RoomWebhookListener.php`

Este listener processa webhooks relacionados a eventos de Room:

```php
// Exemplo de uso
$event = new RoomWebhookReceived($webhookData);
event($event);
```

**Métodos principais:**
- `handleRoomCreated()`: Processa criação de quartos
- `handleRoomUpdated()`: Processa atualizações de quartos
- `handleRoomDeleted()`: Processa exclusão de quartos

### RatePlanWebhookListener

Localização: `src/Laravel/Listeners/RatePlanWebhookListener.php`

Este listener processa webhooks relacionados a eventos de Rate Plan:

```php
// Exemplo de uso
$event = new RatePlanWebhookReceived($webhookData);
event($event);
```

**Métodos principais:**
- `handleRatePlanCreated()`: Processa criação de planos de tarifas
- `handleRatePlanUpdated()`: Processa atualizações de planos de tarifas
- `handleRatePlanDeleted()`: Processa exclusão de planos de tarifas

## Configuração

Os eventos e listeners estão configurados no arquivo `src/Laravel/config/innochannel-events.php`:

```php
'auto_event_listeners' => [
    // ... outros listeners
    'room' => [
        'created' => \Innochannel\Sdk\Laravel\Listeners\RoomWebhookListener::class,
        'updated' => \Innochannel\Sdk\Laravel\Listeners\RoomWebhookListener::class,
        'deleted' => \Innochannel\Sdk\Laravel\Listeners\RoomWebhookListener::class,
    ],
    'rate_plan' => [
        'created' => \Innochannel\Sdk\Laravel\Listeners\RatePlanWebhookListener::class,
        'updated' => \Innochannel\Sdk\Laravel\Listeners\RatePlanWebhookListener::class,
        'deleted' => \Innochannel\Sdk\Laravel\Listeners\RatePlanWebhookListener::class,
    ],
],
```

## Modelos com Eventos

### Room Model

O modelo Room (`src/Models/Room.php`) utiliza a trait `HasEvents` e possui os seguintes eventos configurados:

- RoomCreatedEvent
- RoomUpdatedEvent
- RoomDeletedEvent
- RoomActivatedEvent
- RoomDeactivatedEvent
- RoomAmenitiesUpdatedEvent
- RoomBedTypesUpdatedEvent
- RoomCapacityChangedEvent

### RatePlan Model

O modelo RatePlan (`src/Models/RatePlan.php`) utiliza a trait `HasEvents` e possui os seguintes eventos configurados:

- RatePlanCreatedEvent
- RatePlanUpdated
- RatePlanDeleted
- RatePlanActivated
- RatePlanDeactivated
- RatePlanRestrictionsUpdated
- RatePlanCancellationPolicyUpdated

## Funcionalidades dos Listeners

### Logging
Todos os listeners implementam logging detalhado para auditoria e debug:

```php
Log::info('Room webhook received', [
    'event_type' => $eventType,
    'room_id' => $data['room_id'] ?? null,
    'property_id' => $data['property_id'] ?? null
]);
```

### Armazenamento de Webhook
Os webhooks são armazenados no banco de dados para histórico:

```php
$this->storeWebhookRecord($event, $eventType, $data);
```

### Cache Management
Os listeners incluem limpeza de cache quando necessário:

```php
Cache::tags(['rooms', 'property_' . $propertyId])->flush();
```

### Error Handling
Tratamento robusto de erros com logging:

```php
try {
    // Processamento do evento
} catch (\Exception $e) {
    Log::error('Error processing room webhook', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
```

## Configurações de Performance

As configurações de performance estão definidas no arquivo de configuração:

```php
'performance' => [
    'max_listeners_per_event' => 10,
    'timeout' => 30, // segundos
    'memory_limit' => '128M',
],
```

## Configurações de Debug

Para desenvolvimento, as seguintes configurações de debug estão disponíveis:

```php
'debug' => [
    'enabled' => env('INNOCHANNEL_EVENTS_DEBUG', false),
    'trace_events' => env('INNOCHANNEL_EVENTS_TRACE', false),
    'log_performance' => env('INNOCHANNEL_EVENTS_LOG_PERFORMANCE', false),
],
```

## Configurações de Modelo

As configurações específicas para Room e Rate Plan:

```php
'models' => [
    'room' => [
        'events_enabled' => true,
        'auto_sync' => true,
        'webhook_enabled' => false,
    ],
    'rate_plan' => [
        'events_enabled' => true,
        'auto_sync' => true,
        'webhook_enabled' => false,
    ],
],
```

## Exemplo de Uso Completo

```php
use Innochannel\Sdk\Models\Room;
use Innochannel\Sdk\Events\RoomWebhookReceived;

// Criar um novo quarto (dispara RoomCreated)
$room = new Room([
    'property_id' => 123,
    'name' => 'Quarto Deluxe',
    'room_type' => 'deluxe',
    'max_occupancy' => 2
]);

// Processar webhook de quarto
$webhookData = [
    'event_type' => 'room.created',
    'data' => [
        'room_id' => 456,
        'property_id' => 123,
        'name' => 'Novo Quarto'
    ]
];

$event = new RoomWebhookReceived($webhookData);
event($event);
```

## Manutenção e Monitoramento

- Logs são armazenados no canal configurado (`innochannel` por padrão)
- Webhooks são persistidos no banco de dados para auditoria
- Cache é limpo automaticamente quando necessário
- Métricas de performance podem ser habilitadas via configuração

## Próximos Passos

1. Implementar testes unitários para os listeners
2. Adicionar validação de dados de webhook
3. Implementar retry logic para falhas de processamento
4. Adicionar métricas de monitoramento