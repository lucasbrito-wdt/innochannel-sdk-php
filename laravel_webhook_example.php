<?php
/**
 * Exemplo de integração de Webhooks com Laravel
 * SDK PHP do Innochannel
 * 
 * Este arquivo demonstra como integrar webhooks do Innochannel
 * em uma aplicação Laravel usando os recursos fornecidos pelo SDK.
 */

// ========== 1. CONFIGURAÇÃO NO LARAVEL ==========

/**
 * 1.1 - Adicione no seu config/services.php:
 */
/*
'innochannel' => [
    'api_key' => env('INNOCHANNEL_API_KEY'),
    'base_url' => env('INNOCHANNEL_BASE_URL', 'https://api.innochannel.com'),
    'webhook_secret' => env('INNOCHANNEL_WEBHOOK_SECRET'),
],
*/

/**
 * 1.2 - Adicione no seu .env:
 */
/*
INNOCHANNEL_API_KEY=sua_api_key_aqui
INNOCHANNEL_BASE_URL=https://api.innochannel.com
INNOCHANNEL_WEBHOOK_SECRET=seu_secret_super_seguro_aqui_123456
*/

/**
 * 1.3 - Registre as rotas no routes/web.php ou routes/api.php:
 */
/*
use Innochannel\Laravel\Http\Controllers\WebhookController;

Route::post('/webhook/innochannel/reservation', [WebhookController::class, 'handleReservationWebhook']);
Route::post('/webhook/innochannel/property', [WebhookController::class, 'handlePropertyWebhook']);
Route::post('/webhook/innochannel/inventory', [WebhookController::class, 'handleInventoryWebhook']);
Route::post('/webhook/innochannel/general', [WebhookController::class, 'handleGeneralWebhook']);
*/

// ========== 2. USANDO O SERVICE PROVIDER ==========

/**
 * 2.1 - Registre o Service Provider no config/app.php:
 */
/*
'providers' => [
    // ...
    Innochannel\Laravel\InnochannelServiceProvider::class,
],
*/

/**
 * 2.2 - Publique as configurações:
 */
/*
php artisan vendor:publish --provider="Innochannel\Laravel\InnochannelServiceProvider"
*/

// ========== 3. CRIANDO LISTENERS PERSONALIZADOS ==========

/**
 * 3.1 - Criar Listener para Reservas
 * app/Listeners/ProcessReservationWebhook.php
 */
/*
<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Innochannel\Sdk\Laravel\Events\ReservationWebhookReceived;
use Illuminate\Support\Facades\Log;
use App\Models\Reservation;
use App\Notifications\NewReservationNotification;

class ProcessReservationWebhook implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(ReservationWebhookReceived $event): void
    {
        $payload = $event->payload;
        $eventType = $payload['event_type'] ?? '';

        Log::info('Processing reservation webhook', [
            'event_type' => $eventType,
            'reservation_id' => $payload['data']['id'] ?? null
        ]);

        switch ($eventType) {
            case 'reservation.created':
                $this->handleNewReservation($payload['data']);
                break;

            case 'reservation.updated':
                $this->handleUpdatedReservation($payload['data']);
                break;

            case 'reservation.cancelled':
                $this->handleCancelledReservation($payload['data']);
                break;

            case 'reservation.confirmed':
                $this->handleConfirmedReservation($payload['data']);
                break;
        }
    }

    private function handleNewReservation(array $data): void
    {
        // Criar ou atualizar reserva local
        $reservation = Reservation::updateOrCreate(
            ['innochannel_id' => $data['id']],
            [
                'guest_name' => $data['guest_name'],
                'guest_email' => $data['guest_email'],
                'check_in' => $data['check_in'],
                'check_out' => $data['check_out'],
                'status' => $data['status'],
                'total_amount' => $data['total_amount'],
                'property_id' => $data['property_id'],
                'room_type_id' => $data['room_type_id'],
            ]
        );

        // Enviar notificação
        $reservation->notify(new NewReservationNotification());

        Log::info('New reservation processed', ['id' => $reservation->id]);
    }

    private function handleUpdatedReservation(array $data): void
    {
        $reservation = Reservation::where('innochannel_id', $data['id'])->first();
        
        if ($reservation) {
            $reservation->update([
                'status' => $data['status'],
                'guest_name' => $data['guest_name'],
                'guest_email' => $data['guest_email'],
                'check_in' => $data['check_in'],
                'check_out' => $data['check_out'],
                'total_amount' => $data['total_amount'],
            ]);

            Log::info('Reservation updated', ['id' => $reservation->id]);
        }
    }

    private function handleCancelledReservation(array $data): void
    {
        $reservation = Reservation::where('innochannel_id', $data['id'])->first();
        
        if ($reservation) {
            $reservation->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancellation_reason' => $data['cancellation_reason'] ?? null
            ]);

            // Processar reembolso se necessário
            // $this->processRefund($reservation);

            Log::info('Reservation cancelled', ['id' => $reservation->id]);
        }
    }

    private function handleConfirmedReservation(array $data): void
    {
        $reservation = Reservation::where('innochannel_id', $data['id'])->first();
        
        if ($reservation) {
            $reservation->update([
                'status' => 'confirmed',
                'confirmed_at' => now()
            ]);

            // Enviar email de confirmação
            // $reservation->sendConfirmationEmail();

            Log::info('Reservation confirmed', ['id' => $reservation->id]);
        }
    }
}
*/

/**
 * 3.2 - Registrar o Listener no EventServiceProvider
 * app/Providers/EventServiceProvider.php
 */
/*
use Innochannel\Sdk\Laravel\Events\ReservationWebhookReceived;
use App\Listeners\ProcessReservationWebhook;

protected $listen = [
    ReservationWebhookReceived::class => [
        ProcessReservationWebhook::class,
    ],
    // ... outros listeners
];
*/

// ========== 4. CONTROLLER PERSONALIZADO ==========

/**
 * 4.1 - Criar Controller personalizado
 * app/Http/Controllers/InnochannelWebhookController.php
 */
/*
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Innochannel\Sdk\Client;
use Innochannel\Sdk\Services\WebhookService;
use Innochannel\Sdk\Exceptions\ValidationException;
use Illuminate\Support\Facades\Log;

class InnochannelWebhookController extends Controller
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
            // Obter dados da requisição
            $payload = $request->getContent();
            $signature = $request->header('X-Innochannel-Signature', '');
            $secret = config('services.innochannel.webhook_secret');

            // Validar e processar payload
            $data = $this->webhookService->processPayload($payload, $signature, $secret);

            Log::info('Webhook received', [
                'event' => $data['event'],
                'object_type' => $data['object_type'],
                'timestamp' => now()
            ]);

            // Processar evento
            $this->processEvent($data);

            return response()->json([
                'success' => true,
                'message' => 'Webhook processed successfully'
            ]);

        } catch (ValidationException $e) {
            Log::error('Webhook validation failed', [
                'error' => $e->getMessage(),
                'signature' => $request->header('X-Innochannel-Signature')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Invalid webhook signature'
            ], 400);

        } catch (\Exception $e) {
            Log::error('Webhook processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    private function processEvent(array $data): void
    {
        switch ($data['event']) {
            case 'reservation.created':
                $this->handleReservationCreated($data['data']);
                break;

            case 'reservation.updated':
                $this->handleReservationUpdated($data['data']);
                break;

            case 'inventory.updated':
                $this->handleInventoryUpdated($data['data']);
                break;

            default:
                Log::info('Unhandled webhook event', ['event' => $data['event']]);
        }
    }

    private function handleReservationCreated(array $reservationData): void
    {
        // Implementar lógica específica
        Log::info('Processing new reservation', ['id' => $reservationData['id']]);
    }

    private function handleReservationUpdated(array $reservationData): void
    {
        // Implementar lógica específica
        Log::info('Processing updated reservation', ['id' => $reservationData['id']]);
    }

    private function handleInventoryUpdated(array $inventoryData): void
    {
        // Implementar lógica específica
        Log::info('Processing inventory update', [
            'property_id' => $inventoryData['property_id'],
            'date' => $inventoryData['date']
        ]);
    }
}
*/

// ========== 5. MIDDLEWARE DE VALIDAÇÃO ==========

/**
 * 5.1 - Criar Middleware para validar webhooks
 * app/Http/Middleware/ValidateInnochannelWebhook.php
 */
/*
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Innochannel\Sdk\Client;
use Innochannel\Sdk\Services\WebhookService;
use Symfony\Component\HttpFoundation\Response;

class ValidateInnochannelWebhook
{
    public function handle(Request $request, Closure $next): Response
    {
        $payload = $request->getContent();
        $signature = $request->header('X-Innochannel-Signature', '');
        $secret = config('services.innochannel.webhook_secret');

        if (empty($signature) || empty($secret)) {
            return response()->json(['error' => 'Missing webhook signature or secret'], 400);
        }

        $client = new Client([
            'api_key' => config('services.innochannel.api_key'),
            'base_url' => config('services.innochannel.base_url'),
        ]);

        $webhookService = new WebhookService($client);

        if (!$webhookService->validateSignature($payload, $signature, $secret)) {
            return response()->json(['error' => 'Invalid webhook signature'], 401);
        }

        return $next($request);
    }
}
*/

/**
 * 5.2 - Registrar o Middleware no Kernel
 * app/Http/Kernel.php
 */
/*
protected $routeMiddleware = [
    // ...
    'innochannel.webhook' => \App\Http\Middleware\ValidateInnochannelWebhook::class,
];
*/

/**
 * 5.3 - Usar o Middleware nas rotas
 */
/*
Route::post('/webhook/innochannel', [InnochannelWebhookController::class, 'handle'])
    ->middleware('innochannel.webhook');
*/

// ========== 6. COMMAND PARA GERENCIAR WEBHOOKS ==========

/**
 * 6.1 - Criar Command para registrar webhooks
 * app/Console/Commands/RegisterInnochannelWebhooks.php
 */
/*
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Innochannel\Sdk\Client;
use Innochannel\Sdk\Services\WebhookService;

class RegisterInnochannelWebhooks extends Command
{
    protected $signature = 'innochannel:register-webhooks';
    protected $description = 'Register Innochannel webhooks';

    public function handle(): int
    {
        $client = new Client([
            'api_key' => config('services.innochannel.api_key'),
            'base_url' => config('services.innochannel.base_url'),
        ]);

        $webhookService = new WebhookService($client);

        try {
            $webhookData = [
                'url' => url('/webhook/innochannel'),
                'events' => [
                    'reservation.created',
                    'reservation.updated',
                    'reservation.cancelled',
                    'reservation.confirmed',
                    'inventory.updated',
                    'rates.updated',
                ],
                'secret' => config('services.innochannel.webhook_secret'),
                'active' => true
            ];

            $webhook = $webhookService->create($webhookData);

            $this->info('Webhook registered successfully!');
            $this->info('Webhook ID: ' . $webhook['id']);
            $this->info('URL: ' . $webhook['url']);

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Failed to register webhook: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
*/

// ========== 7. JOB PARA PROCESSAMENTO ASSÍNCRONO ==========

/**
 * 7.1 - Criar Job para processar webhooks
 * app/Jobs/ProcessInnochannelWebhook.php
 */
/*
<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessInnochannelWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private array $webhookData,
        private string $eventType
    ) {}

    public function handle(): void
    {
        Log::info('Processing webhook job', [
            'event_type' => $this->eventType,
            'data_id' => $this->webhookData['id'] ?? null
        ]);

        switch ($this->eventType) {
            case 'reservation.created':
                $this->processNewReservation();
                break;

            case 'inventory.updated':
                $this->processInventoryUpdate();
                break;

            // ... outros casos
        }
    }

    private function processNewReservation(): void
    {
        // Processar nova reserva
        Log::info('Processing new reservation in job', [
            'reservation_id' => $this->webhookData['id']
        ]);
    }

    private function processInventoryUpdate(): void
    {
        // Processar atualização de inventário
        Log::info('Processing inventory update in job', [
            'property_id' => $this->webhookData['property_id']
        ]);
    }
}
*/

// ========== 8. EXEMPLO DE USO COMPLETO ==========

/**
 * 8.1 - Controller que usa Job
 */
/*
public function handleWebhook(Request $request): JsonResponse
{
    try {
        $payload = $request->getContent();
        $signature = $request->header('X-Innochannel-Signature', '');
        $secret = config('services.innochannel.webhook_secret');

        $data = $this->webhookService->processPayload($payload, $signature, $secret);

        // Processar de forma assíncrona
        ProcessInnochannelWebhook::dispatch($data['data'], $data['event']);

        return response()->json(['success' => true]);

    } catch (\Exception $e) {
        Log::error('Webhook error', ['error' => $e->getMessage()]);
        return response()->json(['success' => false], 500);
    }
}
*/

echo "=== Exemplo de integração Laravel com Webhooks do Innochannel ===\n";
echo "Este arquivo contém exemplos completos de como integrar webhooks em Laravel.\n";
echo "Consulte os comentários no código para implementação detalhada.\n";

/**
 * RESUMO DA INTEGRAÇÃO LARAVEL:
 * 
 * 1. CONFIGURAÇÃO:
 *    - Configure as variáveis de ambiente
 *    - Registre o Service Provider
 *    - Publique as configurações
 * 
 * 2. ROTAS E CONTROLLERS:
 *    - Use os controllers fornecidos pelo SDK
 *    - Ou crie controllers personalizados
 *    - Implemente middleware de validação
 * 
 * 3. EVENTS E LISTENERS:
 *    - Use os eventos fornecidos pelo SDK
 *    - Crie listeners personalizados
 *    - Implemente processamento assíncrono com Jobs
 * 
 * 4. GERENCIAMENTO:
 *    - Crie commands para registrar/gerenciar webhooks
 *    - Use logs para monitoramento
 *    - Implemente retry logic para falhas
 * 
 * 5. SEGURANÇA:
 *    - Sempre valide assinaturas
 *    - Use middleware de autenticação
 *    - Monitore tentativas de acesso inválidas
 */