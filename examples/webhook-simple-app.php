<?php
/**
 * Exemplo Simples de Aplicação com Webhooks
 * SDK PHP Innochannel
 * 
 * Este é um exemplo prático de como implementar webhooks
 * em uma aplicação PHP simples para gerenciar reservas de hotel.
 */

require_once '../vendor/autoload.php';

use Innochannel\Sdk\Client;
use Innochannel\Sdk\Services\WebhookService;
use Innochannel\Sdk\Exceptions\ValidationException;

// ========== CONFIGURAÇÃO ==========

$config = [
    'api_key' => 'sua_api_key_aqui',
    'base_url' => 'https://api.innochannel.com',
    'webhook_secret' => 'meu_secret_super_seguro_123456',
    'webhook_url' => 'https://sua-aplicacao.com/webhook.php'
];

// ========== CLASSE PRINCIPAL DA APLICAÇÃO ==========

class SimpleHotelApp
{
    private Client $client;
    private WebhookService $webhookService;
    private string $secret;
    private string $dataFile;

    public function __construct(array $config)
    {
        $this->client = new Client([
            'api_key' => $config['api_key'],
            'base_url' => $config['base_url']
        ]);
        
        $this->webhookService = new WebhookService($this->client);
        $this->secret = $config['webhook_secret'];
        $this->dataFile = __DIR__ . '/reservas.json';
        
        // Criar arquivo de dados se não existir
        if (!file_exists($this->dataFile)) {
            file_put_contents($this->dataFile, json_encode([]));
        }
    }

    /**
     * Registrar webhook (executar apenas uma vez)
     */
    public function registrarWebhook(string $url): array
    {
        try {
            $webhook = $this->webhookService->create([
                'url' => $url,
                'events' => [
                    'reservation.created',
                    'reservation.updated',
                    'reservation.cancelled',
                    'reservation.confirmed'
                ],
                'secret' => $this->secret,
                'timeout' => 15,
                'retry_attempts' => 3,
                'active' => true
            ]);

            $this->log("Webhook registrado com sucesso", [
                'id' => $webhook['id'],
                'url' => $webhook['url']
            ]);

            return $webhook;

        } catch (Exception $e) {
            $this->log("Erro ao registrar webhook", ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Processar webhook recebido
     */
    public function processarWebhook(): void
    {
        try {
            // Obter dados da requisição
            $payload = file_get_contents('php://input');
            $signature = $_SERVER['HTTP_X_INNOCHANNEL_SIGNATURE'] ?? '';

            if (empty($payload)) {
                throw new Exception('Payload vazio');
            }

            if (empty($signature)) {
                throw new Exception('Assinatura não encontrada');
            }

            // Validar e processar
            $data = $this->webhookService->processPayload($payload, $signature, $this->secret);

            $this->log("Webhook recebido", [
                'event' => $data['event'],
                'id' => $data['data']['id'] ?? null
            ]);

            // Processar evento
            $this->processarEvento($data);

            // Responder com sucesso
            $this->responderSucesso('Webhook processado com sucesso');

        } catch (ValidationException $e) {
            $this->log("Webhook inválido", ['error' => $e->getMessage()]);
            $this->responderErro('Assinatura inválida', 400);

        } catch (Exception $e) {
            $this->log("Erro no webhook", ['error' => $e->getMessage()]);
            $this->responderErro('Erro interno', 500);
        }
    }

    /**
     * Processar diferentes tipos de eventos
     */
    private function processarEvento(array $data): void
    {
        $evento = $data['event'];
        $dadosReserva = $data['data'];

        switch ($evento) {
            case 'reservation.created':
                $this->criarReserva($dadosReserva);
                break;

            case 'reservation.updated':
                $this->atualizarReserva($dadosReserva);
                break;

            case 'reservation.cancelled':
                $this->cancelarReserva($dadosReserva);
                break;

            case 'reservation.confirmed':
                $this->confirmarReserva($dadosReserva);
                break;

            default:
                $this->log("Evento não tratado", ['event' => $evento]);
        }
    }

    /**
     * Criar nova reserva
     */
    private function criarReserva(array $reserva): void
    {
        $reservas = $this->carregarReservas();

        $novaReserva = [
            'id' => $reserva['id'],
            'guest_name' => $reserva['guest_name'] ?? 'N/A',
            'guest_email' => $reserva['guest_email'] ?? '',
            'check_in' => $reserva['check_in'] ?? '',
            'check_out' => $reserva['check_out'] ?? '',
            'status' => $reserva['status'] ?? 'pending',
            'total_amount' => $reserva['total_amount'] ?? 0,
            'property_id' => $reserva['property_id'] ?? '',
            'room_type' => $reserva['room_type'] ?? '',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $reservas[$reserva['id']] = $novaReserva;
        $this->salvarReservas($reservas);

        $this->log("Nova reserva criada", [
            'id' => $reserva['id'],
            'guest' => $novaReserva['guest_name']
        ]);

        // Enviar email de confirmação
        $this->enviarEmailConfirmacao($novaReserva);
    }

    /**
     * Atualizar reserva existente
     */
    private function atualizarReserva(array $reserva): void
    {
        $reservas = $this->carregarReservas();
        $id = $reserva['id'];

        if (!isset($reservas[$id])) {
            $this->log("Reserva não encontrada para atualização", ['id' => $id]);
            return;
        }

        // Atualizar campos
        $reservas[$id]['guest_name'] = $reserva['guest_name'] ?? $reservas[$id]['guest_name'];
        $reservas[$id]['guest_email'] = $reserva['guest_email'] ?? $reservas[$id]['guest_email'];
        $reservas[$id]['check_in'] = $reserva['check_in'] ?? $reservas[$id]['check_in'];
        $reservas[$id]['check_out'] = $reserva['check_out'] ?? $reservas[$id]['check_out'];
        $reservas[$id]['status'] = $reserva['status'] ?? $reservas[$id]['status'];
        $reservas[$id]['total_amount'] = $reserva['total_amount'] ?? $reservas[$id]['total_amount'];
        $reservas[$id]['updated_at'] = date('Y-m-d H:i:s');

        $this->salvarReservas($reservas);

        $this->log("Reserva atualizada", [
            'id' => $id,
            'status' => $reservas[$id]['status']
        ]);
    }

    /**
     * Cancelar reserva
     */
    private function cancelarReserva(array $reserva): void
    {
        $reservas = $this->carregarReservas();
        $id = $reserva['id'];

        if (!isset($reservas[$id])) {
            $this->log("Reserva não encontrada para cancelamento", ['id' => $id]);
            return;
        }

        $reservas[$id]['status'] = 'cancelled';
        $reservas[$id]['cancelled_at'] = date('Y-m-d H:i:s');
        $reservas[$id]['cancellation_reason'] = $reserva['cancellation_reason'] ?? 'Não informado';
        $reservas[$id]['updated_at'] = date('Y-m-d H:i:s');

        $this->salvarReservas($reservas);

        $this->log("Reserva cancelada", [
            'id' => $id,
            'reason' => $reservas[$id]['cancellation_reason']
        ]);

        // Processar reembolso se necessário
        $this->processarReembolso($reservas[$id]);
    }

    /**
     * Confirmar reserva
     */
    private function confirmarReserva(array $reserva): void
    {
        $reservas = $this->carregarReservas();
        $id = $reserva['id'];

        if (!isset($reservas[$id])) {
            $this->log("Reserva não encontrada para confirmação", ['id' => $id]);
            return;
        }

        $reservas[$id]['status'] = 'confirmed';
        $reservas[$id]['confirmed_at'] = date('Y-m-d H:i:s');
        $reservas[$id]['updated_at'] = date('Y-m-d H:i:s');

        $this->salvarReservas($reservas);

        $this->log("Reserva confirmada", ['id' => $id]);

        // Enviar email de confirmação
        $this->enviarEmailConfirmacao($reservas[$id]);
    }

    /**
     * Carregar reservas do arquivo
     */
    private function carregarReservas(): array
    {
        $content = file_get_contents($this->dataFile);
        return json_decode($content, true) ?: [];
    }

    /**
     * Salvar reservas no arquivo
     */
    private function salvarReservas(array $reservas): void
    {
        file_put_contents($this->dataFile, json_encode($reservas, JSON_PRETTY_PRINT));
    }

    /**
     * Enviar email de confirmação (simulado)
     */
    private function enviarEmailConfirmacao(array $reserva): void
    {
        if (empty($reserva['guest_email'])) {
            return;
        }

        // Simular envio de email
        $assunto = "Confirmação de Reserva #{$reserva['id']}";
        $mensagem = "
            Olá {$reserva['guest_name']},
            
            Sua reserva foi confirmada!
            
            Detalhes:
            - ID: {$reserva['id']}
            - Check-in: {$reserva['check_in']}
            - Check-out: {$reserva['check_out']}
            - Status: {$reserva['status']}
            
            Obrigado por escolher nosso hotel!
        ";

        // Em produção, usar PHPMailer, SendGrid, etc.
        // mail($reserva['guest_email'], $assunto, $mensagem);

        $this->log("Email enviado", [
            'to' => $reserva['guest_email'],
            'subject' => $assunto
        ]);
    }

    /**
     * Processar reembolso (simulado)
     */
    private function processarReembolso(array $reserva): void
    {
        if ($reserva['total_amount'] <= 0) {
            return;
        }

        // Simular processamento de reembolso
        $this->log("Reembolso processado", [
            'reservation_id' => $reserva['id'],
            'amount' => $reserva['total_amount']
        ]);

        // Em produção, integrar com gateway de pagamento
        // $gateway->refund($reserva['payment_id'], $reserva['total_amount']);
    }

    /**
     * Responder com sucesso
     */
    private function responderSucesso(string $message): void
    {
        $response = $this->webhookService->createResponse(true, $message);
        
        header('Content-Type: application/json');
        http_response_code(200);
        echo json_encode($response);
    }

    /**
     * Responder com erro
     */
    private function responderErro(string $message, int $code = 500): void
    {
        $response = $this->webhookService->createResponse(false, $message);
        
        header('Content-Type: application/json');
        http_response_code($code);
        echo json_encode($response);
    }

    /**
     * Log de eventos
     */
    private function log(string $message, array $context = []): void
    {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'message' => $message,
            'context' => $context
        ];

        $logFile = __DIR__ . '/webhook.log';
        file_put_contents($logFile, json_encode($logEntry) . "\n", FILE_APPEND);
    }

    /**
     * Listar reservas (para debug)
     */
    public function listarReservas(): array
    {
        return $this->carregarReservas();
    }

    /**
     * Obter estatísticas
     */
    public function obterEstatisticas(): array
    {
        $reservas = $this->carregarReservas();
        
        $stats = [
            'total' => count($reservas),
            'confirmed' => 0,
            'cancelled' => 0,
            'pending' => 0
        ];

        foreach ($reservas as $reserva) {
            $status = $reserva['status'] ?? 'pending';
            if (isset($stats[$status])) {
                $stats[$status]++;
            }
        }

        return $stats;
    }
}

// ========== USO DA APLICAÇÃO ==========

try {
    $app = new SimpleHotelApp($config);

    // Verificar ação solicitada
    $action = $_GET['action'] ?? 'webhook';

    switch ($action) {
        case 'register':
            // Registrar webhook
            echo "<h2>Registrando Webhook</h2>";
            $webhook = $app->registrarWebhook($config['webhook_url']);
            echo "<p>Webhook registrado com sucesso!</p>";
            echo "<p>ID: {$webhook['id']}</p>";
            echo "<p>URL: {$webhook['url']}</p>";
            break;

        case 'list':
            // Listar reservas
            echo "<h2>Reservas</h2>";
            $reservas = $app->listarReservas();
            
            if (empty($reservas)) {
                echo "<p>Nenhuma reserva encontrada.</p>";
            } else {
                echo "<table border='1'>";
                echo "<tr><th>ID</th><th>Hóspede</th><th>Check-in</th><th>Check-out</th><th>Status</th></tr>";
                
                foreach ($reservas as $reserva) {
                    echo "<tr>";
                    echo "<td>{$reserva['id']}</td>";
                    echo "<td>{$reserva['guest_name']}</td>";
                    echo "<td>{$reserva['check_in']}</td>";
                    echo "<td>{$reserva['check_out']}</td>";
                    echo "<td>{$reserva['status']}</td>";
                    echo "</tr>";
                }
                
                echo "</table>";
            }
            break;

        case 'stats':
            // Mostrar estatísticas
            echo "<h2>Estatísticas</h2>";
            $stats = $app->obterEstatisticas();
            
            echo "<ul>";
            echo "<li>Total de reservas: {$stats['total']}</li>";
            echo "<li>Confirmadas: {$stats['confirmed']}</li>";
            echo "<li>Canceladas: {$stats['cancelled']}</li>";
            echo "<li>Pendentes: {$stats['pending']}</li>";
            echo "</ul>";
            break;

        case 'webhook':
        default:
            // Processar webhook
            $app->processarWebhook();
            break;
    }

} catch (Exception $e) {
    error_log("Erro na aplicação: " . $e->getMessage());
    
    if ($_GET['action'] ?? 'webhook' === 'webhook') {
        // Resposta de erro para webhook
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Erro interno da aplicação'
        ]);
    } else {
        // Mostrar erro na página
        echo "<h2>Erro</h2>";
        echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Hotel App - Webhooks Innochannel</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .nav { margin-bottom: 20px; }
        .nav a { margin-right: 10px; padding: 5px 10px; background: #007cba; color: white; text-decoration: none; }
        .nav a:hover { background: #005a87; }
    </style>
</head>
<body>
    <h1>Hotel App - Webhooks Innochannel</h1>
    
    <div class="nav">
        <a href="?action=register">Registrar Webhook</a>
        <a href="?action=list">Listar Reservas</a>
        <a href="?action=stats">Estatísticas</a>
    </div>

    <h3>Como usar:</h3>
    <ol>
        <li><strong>Configure</strong> suas credenciais no início do arquivo</li>
        <li><strong>Registre</strong> o webhook clicando em "Registrar Webhook"</li>
        <li><strong>Configure</strong> sua URL de webhook para apontar para este arquivo</li>
        <li><strong>Teste</strong> enviando eventos do Innochannel</li>
        <li><strong>Monitore</strong> as reservas e logs</li>
    </ol>

    <h3>Arquivos gerados:</h3>
    <ul>
        <li><code>reservas.json</code> - Dados das reservas</li>
        <li><code>webhook.log</code> - Logs de eventos</li>
    </ul>

    <h3>URLs de teste:</h3>
    <ul>
        <li><code>webhook.php</code> - Endpoint do webhook</li>
        <li><code>webhook.php?action=register</code> - Registrar webhook</li>
        <li><code>webhook.php?action=list</code> - Listar reservas</li>
        <li><code>webhook.php?action=stats</code> - Ver estatísticas</li>
    </ul>
</body>
</html>