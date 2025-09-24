<?php
/**
 * Exemplo completo de uso de Webhooks no SDK PHP do Innochannel
 * 
 * Este arquivo demonstra como:
 * 1. Configurar e registrar webhooks
 * 2. Processar webhooks recebidos
 * 3. Validar assinaturas de segurança
 * 4. Gerenciar webhooks existentes
 */

require_once 'vendor/autoload.php';

use Innochannel\Sdk\Client;
use Innochannel\Sdk\Services\WebhookService;
use Innochannel\Sdk\Exceptions\ApiException;
use Innochannel\Sdk\Exceptions\ValidationException;

// ========== CONFIGURAÇÃO INICIAL ==========

// Inicializar o cliente
$client = new Client([
    'api_key' => 'sua_api_key_aqui',
    'base_url' => 'https://api.innochannel.com',
    'timeout' => 30
]);

// Instanciar o serviço de webhook
$webhookService = new WebhookService($client);

// ========== 1. REGISTRAR UM WEBHOOK ==========

try {
    echo "=== Registrando um novo webhook ===\n";
    
    // Dados do webhook
    $webhookData = [
        'url' => 'https://seu-site.com/webhook/innochannel',
        'events' => [
            'reservation.created',
            'reservation.updated',
            'reservation.cancelled',
            'inventory.updated'
        ],
        'secret' => 'seu_secret_super_seguro_aqui_123456', // Mínimo 16 caracteres
        'timeout' => 15, // Timeout em segundos (1-30)
        'retry_attempts' => 3, // Tentativas de reenvio (0-5)
        'active' => true
    ];
    
    // Criar webhook
    $webhook = $webhookService->create($webhookData);
    echo "Webhook criado com sucesso! ID: " . $webhook['id'] . "\n";
    
    // Método alternativo usando o Client diretamente (mais simples)
    $success = $client->registerWebhook(
        'https://seu-site.com/webhook/simple', 
        ['reservation.created', 'reservation.updated']
    );
    echo "Webhook simples registrado: " . ($success ? 'Sim' : 'Não') . "\n";
    
} catch (ValidationException $e) {
    echo "Erro de validação: " . $e->getMessage() . "\n";
    print_r($e->getErrors());
} catch (ApiException $e) {
    echo "Erro da API: " . $e->getMessage() . "\n";
}

// ========== 2. LISTAR WEBHOOKS EXISTENTES ==========

try {
    echo "\n=== Listando webhooks existentes ===\n";
    
    // Listar todos os webhooks
    $webhooks = $webhookService->list();
    echo "Total de webhooks: " . count($webhooks) . "\n";
    
    // Listar webhooks ativos apenas
    $activeWebhooks = $webhookService->list(['active' => true]);
    echo "Webhooks ativos: " . count($activeWebhooks) . "\n";
    
    // Método alternativo usando o Client
    $allWebhooks = $client->getWebhooks();
    
    foreach ($allWebhooks as $webhook) {
        echo "- ID: {$webhook['id']}, URL: {$webhook['url']}, Ativo: " . 
             ($webhook['active'] ? 'Sim' : 'Não') . "\n";
    }
    
} catch (ApiException $e) {
    echo "Erro ao listar webhooks: " . $e->getMessage() . "\n";
}

// ========== 3. PROCESSAR WEBHOOK RECEBIDO ==========

/**
 * Esta função deve ser chamada no endpoint que recebe os webhooks
 * Exemplo: https://seu-site.com/webhook/innochannel
 */
function processarWebhookRecebido()
{
    global $webhookService;
    
    try {
        // Obter dados da requisição
        $payload = file_get_contents('php://input');
        $signature = $_SERVER['HTTP_X_INNOCHANNEL_SIGNATURE'] ?? '';
        $secret = 'seu_secret_super_seguro_aqui_123456'; // Mesmo secret usado no registro
        
        echo "=== Processando webhook recebido ===\n";
        
        // Processar e validar o payload
        $data = $webhookService->processPayload($payload, $signature, $secret);
        
        echo "Evento recebido: " . $data['event'] . "\n";
        echo "Tipo de objeto: " . $data['object_type'] . "\n";
        
        // Processar diferentes tipos de eventos
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
                echo "Evento não tratado: " . $data['event'] . "\n";
        }
        
        // Retornar resposta de sucesso
        $response = $webhookService->createResponse(true, 'Webhook processado com sucesso');
        
        header('Content-Type: application/json');
        echo json_encode($response);
        
    } catch (ValidationException $e) {
        echo "Erro de validação do webhook: " . $e->getMessage() . "\n";
        
        // Retornar resposta de erro
        $response = $webhookService->createResponse(false, 'Assinatura inválida');
        header('Content-Type: application/json', true, 400);
        echo json_encode($response);
        
    } catch (Exception $e) {
        echo "Erro ao processar webhook: " . $e->getMessage() . "\n";
        
        $response = $webhookService->createResponse(false, 'Erro interno');
        header('Content-Type: application/json', true, 500);
        echo json_encode($response);
    }
}

// ========== 4. FUNÇÕES DE PROCESSAMENTO DE EVENTOS ==========

function processarNovaReserva($reservationData)
{
    echo "Nova reserva criada:\n";
    echo "- ID: " . $reservationData['id'] . "\n";
    echo "- Hóspede: " . $reservationData['guest_name'] . "\n";
    echo "- Check-in: " . $reservationData['check_in'] . "\n";
    echo "- Check-out: " . $reservationData['check_out'] . "\n";
    
    // Aqui você pode:
    // - Enviar email de confirmação
    // - Atualizar sistema interno
    // - Sincronizar com PMS
    // - Etc.
}

function processarReservaAtualizada($reservationData)
{
    echo "Reserva atualizada:\n";
    echo "- ID: " . $reservationData['id'] . "\n";
    echo "- Status: " . $reservationData['status'] . "\n";
    
    // Processar mudanças específicas
    if (isset($reservationData['changes'])) {
        echo "Campos alterados: " . implode(', ', array_keys($reservationData['changes'])) . "\n";
    }
}

function processarReservaCancelada($reservationData)
{
    echo "Reserva cancelada:\n";
    echo "- ID: " . $reservationData['id'] . "\n";
    echo "- Motivo: " . ($reservationData['cancellation_reason'] ?? 'Não informado') . "\n";
    
    // Processar cancelamento
    // - Liberar inventário
    // - Processar reembolso
    // - Notificar equipe
}

function processarInventarioAtualizado($inventoryData)
{
    echo "Inventário atualizado:\n";
    echo "- Propriedade: " . $inventoryData['property_id'] . "\n";
    echo "- Quarto: " . $inventoryData['room_type_id'] . "\n";
    echo "- Data: " . $inventoryData['date'] . "\n";
    echo "- Disponibilidade: " . $inventoryData['availability'] . "\n";
}

// ========== 5. GERENCIAR WEBHOOKS EXISTENTES ==========

try {
    echo "\n=== Gerenciando webhooks ===\n";
    
    // Obter um webhook específico
    $webhookId = 'webhook_id_aqui';
    $webhook = $webhookService->get($webhookId);
    echo "Webhook encontrado: " . $webhook['url'] . "\n";
    
    // Atualizar webhook
    $updateData = [
        'events' => [
            'reservation.created',
            'reservation.updated',
            'reservation.cancelled',
            'inventory.updated',
            'rates.updated' // Adicionando novo evento
        ],
        'timeout' => 20 // Aumentando timeout
    ];
    
    $updatedWebhook = $webhookService->update($webhookId, $updateData);
    echo "Webhook atualizado com sucesso!\n";
    
    // Testar webhook
    $testResult = $webhookService->test($webhookId, [
        'event' => 'test.webhook',
        'data' => ['message' => 'Teste de webhook']
    ]);
    echo "Teste do webhook: " . ($testResult['success'] ? 'Sucesso' : 'Falha') . "\n";
    
    // Obter logs do webhook
    $logs = $webhookService->getLogs($webhookId, [
        'limit' => 10,
        'status' => 'failed' // Apenas logs de falha
    ]);
    echo "Logs de falha encontrados: " . count($logs) . "\n";
    
    // Reenviar webhook falhado
    if (!empty($logs)) {
        $logId = $logs[0]['id'];
        $retryResult = $webhookService->retry($webhookId, $logId);
        echo "Reenvio do webhook: " . ($retryResult['success'] ? 'Sucesso' : 'Falha') . "\n";
    }
    
    // Desativar webhook temporariamente
    $webhookService->setActive($webhookId, false);
    echo "Webhook desativado temporariamente\n";
    
    // Reativar webhook
    $webhookService->setActive($webhookId, true);
    echo "Webhook reativado\n";
    
} catch (ApiException $e) {
    echo "Erro ao gerenciar webhook: " . $e->getMessage() . "\n";
}

// ========== 6. EVENTOS DISPONÍVEIS ==========

try {
    echo "\n=== Eventos disponíveis ===\n";
    
    $availableEvents = $webhookService->getAvailableEvents();
    echo "Eventos disponíveis:\n";
    
    foreach ($availableEvents as $event) {
        echo "- {$event['name']}: {$event['description']}\n";
    }
    
} catch (ApiException $e) {
    echo "Erro ao obter eventos: " . $e->getMessage() . "\n";
}

// ========== 7. VALIDAÇÃO MANUAL DE ASSINATURA ==========

function validarAssinaturaManual($payload, $signature, $secret)
{
    global $webhookService;
    
    echo "\n=== Validação manual de assinatura ===\n";
    
    $isValid = $webhookService->validateSignature($payload, $signature, $secret);
    echo "Assinatura válida: " . ($isValid ? 'Sim' : 'Não') . "\n";
    
    return $isValid;
}

// ========== 8. LIMPEZA - REMOVER WEBHOOKS ==========

try {
    echo "\n=== Removendo webhooks de teste ===\n";
    
    // Remover webhook específico
    $success = $webhookService->delete($webhookId);
    echo "Webhook removido: " . ($success ? 'Sim' : 'Não') . "\n";
    
    // Método alternativo usando o Client
    $success = $client->unregisterWebhook('https://seu-site.com/webhook/simple');
    echo "Webhook simples removido: " . ($success ? 'Sim' : 'Não') . "\n";
    
} catch (ApiException $e) {
    echo "Erro ao remover webhook: " . $e->getMessage() . "\n";
}

echo "\n=== Exemplo de uso de webhooks concluído! ===\n";

/**
 * RESUMO DE USO:
 * 
 * 1. CONFIGURAÇÃO:
 *    - Instancie o Client com suas credenciais
 *    - Crie uma instância do WebhookService
 * 
 * 2. REGISTRO:
 *    - Use webhookService->create() para webhooks completos
 *    - Use client->registerWebhook() para registro simples
 * 
 * 3. PROCESSAMENTO:
 *    - Implemente um endpoint para receber webhooks
 *    - Use webhookService->processPayload() para validar e processar
 *    - Trate diferentes tipos de eventos conforme necessário
 * 
 * 4. GERENCIAMENTO:
 *    - Liste, atualize, teste e monitore seus webhooks
 *    - Use logs para debugar problemas
 *    - Reenvie webhooks falhados quando necessário
 * 
 * 5. SEGURANÇA:
 *    - SEMPRE valide assinaturas dos webhooks
 *    - Use secrets seguros (mínimo 16 caracteres)
 *    - Implemente rate limiting no seu endpoint
 * 
 * 6. BOAS PRÁTICAS:
 *    - Responda rapidamente (< 15 segundos)
 *    - Implemente idempotência para evitar processamento duplicado
 *    - Monitore logs de webhook regularmente
 *    - Use HTTPS para URLs de webhook
 */