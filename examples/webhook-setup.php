<?php
/**
 * Script de Setup para Webhooks - SDK PHP Innochannel
 * 
 * Execute este script para configurar automaticamente os webhooks
 * na sua aplicação Innochannel.
 */

require_once '../vendor/autoload.php';

use Innochannel\Sdk\Client;
use Innochannel\Sdk\Services\WebhookService;

// ========== FUNÇÕES AUXILIARES ==========

function exibirTitulo(string $titulo): void
{
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "  " . strtoupper($titulo) . "\n";
    echo str_repeat("=", 60) . "\n\n";
}

function exibirSucesso(string $mensagem): void
{
    echo "✅ " . $mensagem . "\n";
}

function exibirErro(string $mensagem): void
{
    echo "❌ " . $mensagem . "\n";
}

function exibirAviso(string $mensagem): void
{
    echo "⚠️  " . $mensagem . "\n";
}

function exibirInfo(string $mensagem): void
{
    echo "ℹ️  " . $mensagem . "\n";
}

function perguntarUsuario(string $pergunta, string $padrao = ''): string
{
    echo $pergunta;
    if (!empty($padrao)) {
        echo " (padrão: $padrao)";
    }
    echo ": ";
    
    $resposta = trim(fgets(STDIN));
    return empty($resposta) ? $padrao : $resposta;
}

function confirmarAcao(string $pergunta): bool
{
    $resposta = strtolower(perguntarUsuario($pergunta . " (s/n)", "n"));
    return in_array($resposta, ['s', 'sim', 'y', 'yes']);
}

// ========== CLASSE DE SETUP ==========

class WebhookSetup
{
    private array $config;
    private Client $client;
    private WebhookService $webhookService;

    public function __construct()
    {
        $this->carregarConfiguracao();
        $this->inicializarCliente();
    }

    private function carregarConfiguracao(): void
    {
        // Tentar carregar configuração local primeiro
        $configLocal = __DIR__ . '/webhook-config-local.php';
        $configPadrao = __DIR__ . '/webhook-config.php';

        if (file_exists($configLocal)) {
            $this->config = require $configLocal;
            exibirInfo("Configuração carregada de: webhook-config-local.php");
        } elseif (file_exists($configPadrao)) {
            $this->config = require $configPadrao;
            exibirAviso("Usando configuração padrão. Recomendamos criar webhook-config-local.php");
        } else {
            throw new Exception("Arquivo de configuração não encontrado!");
        }
    }

    private function inicializarCliente(): void
    {
        try {
            $this->client = new Client([
                'api_key' => $this->config['api_key'],
                'base_url' => $this->config['base_url']
            ]);

            $this->webhookService = new WebhookService($this->client);
            exibirSucesso("Cliente SDK inicializado com sucesso");

        } catch (Exception $e) {
            exibirErro("Erro ao inicializar cliente: " . $e->getMessage());
            throw $e;
        }
    }

    public function executarSetup(): void
    {
        exibirTitulo("SETUP DE WEBHOOKS - INNOCHANNEL SDK");

        try {
            // Verificar configuração
            $this->verificarConfiguracao();

            // Mostrar menu
            $this->mostrarMenu();

        } catch (Exception $e) {
            exibirErro("Erro durante o setup: " . $e->getMessage());
            exit(1);
        }
    }

    private function verificarConfiguracao(): void
    {
        exibirTitulo("VERIFICANDO CONFIGURAÇÃO");

        // Verificar API Key
        if (empty($this->config['api_key']) || $this->config['api_key'] === 'sua_api_key_aqui') {
            exibirErro("API Key não configurada!");
            echo "Configure sua API Key no arquivo de configuração.\n";
            exit(1);
        }
        exibirSucesso("API Key configurada");

        // Verificar URL do webhook
        if (empty($this->config['webhook_url']) || $this->config['webhook_url'] === 'https://sua-aplicacao.com/webhook.php') {
            exibirAviso("URL do webhook não configurada ou usando valor padrão");
        } else {
            exibirSucesso("URL do webhook: " . $this->config['webhook_url']);
        }

        // Verificar secret
        if (empty($this->config['webhook_secret']) || $this->config['webhook_secret'] === 'meu_secret_super_seguro_123456') {
            exibirAviso("Secret do webhook não configurado ou usando valor padrão");
        } else {
            exibirSucesso("Secret do webhook configurado");
        }

        // Testar conexão com API
        try {
            // Tentar listar webhooks existentes para testar a conexão
            $webhooks = $this->webhookService->list();
            exibirSucesso("Conexão com API testada com sucesso");
            exibirInfo("Webhooks existentes: " . count($webhooks));

        } catch (Exception $e) {
            exibirErro("Erro ao conectar com a API: " . $e->getMessage());
            throw $e;
        }
    }

    private function mostrarMenu(): void
    {
        while (true) {
            exibirTitulo("MENU PRINCIPAL");

            echo "1. Listar webhooks existentes\n";
            echo "2. Registrar novo webhook\n";
            echo "3. Testar webhook existente\n";
            echo "4. Atualizar webhook\n";
            echo "5. Remover webhook\n";
            echo "6. Verificar configuração\n";
            echo "7. Criar diretórios necessários\n";
            echo "0. Sair\n\n";

            $opcao = perguntarUsuario("Escolha uma opção");

            switch ($opcao) {
                case '1':
                    $this->listarWebhooks();
                    break;
                case '2':
                    $this->registrarWebhook();
                    break;
                case '3':
                    $this->testarWebhook();
                    break;
                case '4':
                    $this->atualizarWebhook();
                    break;
                case '5':
                    $this->removerWebhook();
                    break;
                case '6':
                    $this->verificarConfiguracao();
                    break;
                case '7':
                    $this->criarDiretorios();
                    break;
                case '0':
                    exibirInfo("Saindo...");
                    exit(0);
                default:
                    exibirErro("Opção inválida!");
            }

            echo "\nPressione Enter para continuar...";
            fgets(STDIN);
        }
    }

    private function listarWebhooks(): void
    {
        exibirTitulo("WEBHOOKS EXISTENTES");

        try {
            $webhooks = $this->webhookService->list();

            if (empty($webhooks)) {
                exibirInfo("Nenhum webhook encontrado");
                return;
            }

            foreach ($webhooks as $webhook) {
                echo "ID: " . $webhook['id'] . "\n";
                echo "URL: " . $webhook['url'] . "\n";
                echo "Status: " . ($webhook['active'] ? 'Ativo' : 'Inativo') . "\n";
                echo "Eventos: " . implode(', ', $webhook['events']) . "\n";
                echo "Criado em: " . $webhook['created_at'] . "\n";
                echo str_repeat("-", 50) . "\n";
            }

            exibirSucesso("Total de webhooks: " . count($webhooks));

        } catch (Exception $e) {
            exibirErro("Erro ao listar webhooks: " . $e->getMessage());
        }
    }

    private function registrarWebhook(): void
    {
        exibirTitulo("REGISTRAR NOVO WEBHOOK");

        try {
            // Confirmar dados
            echo "Dados do webhook a ser registrado:\n";
            echo "URL: " . $this->config['webhook_url'] . "\n";
            echo "Eventos: " . implode(', ', $this->config['webhook_events']) . "\n";
            echo "Timeout: " . $this->config['webhook_timeout'] . "s\n";
            echo "Tentativas: " . $this->config['webhook_retry_attempts'] . "\n\n";

            if (!confirmarAcao("Confirma o registro do webhook?")) {
                exibirInfo("Registro cancelado");
                return;
            }

            // Registrar webhook
            $webhook = $this->webhookService->create([
                'url' => $this->config['webhook_url'],
                'events' => $this->config['webhook_events'],
                'secret' => $this->config['webhook_secret'],
                'timeout' => $this->config['webhook_timeout'],
                'retry_attempts' => $this->config['webhook_retry_attempts'],
                'active' => true
            ]);

            exibirSucesso("Webhook registrado com sucesso!");
            echo "ID: " . $webhook['id'] . "\n";
            echo "URL: " . $webhook['url'] . "\n";

        } catch (Exception $e) {
            exibirErro("Erro ao registrar webhook: " . $e->getMessage());
        }
    }

    private function testarWebhook(): void
    {
        exibirTitulo("TESTAR WEBHOOK");

        try {
            $webhookId = perguntarUsuario("ID do webhook para testar");

            if (empty($webhookId)) {
                exibirErro("ID do webhook é obrigatório");
                return;
            }

            $resultado = $this->webhookService->test($webhookId);

            if ($resultado['success']) {
                exibirSucesso("Teste do webhook realizado com sucesso!");
                echo "Resposta: " . $resultado['response'] . "\n";
            } else {
                exibirErro("Falha no teste do webhook");
                echo "Erro: " . $resultado['error'] . "\n";
            }

        } catch (Exception $e) {
            exibirErro("Erro ao testar webhook: " . $e->getMessage());
        }
    }

    private function atualizarWebhook(): void
    {
        exibirTitulo("ATUALIZAR WEBHOOK");

        try {
            $webhookId = perguntarUsuario("ID do webhook para atualizar");

            if (empty($webhookId)) {
                exibirErro("ID do webhook é obrigatório");
                return;
            }

            // Obter webhook atual
            $webhookAtual = $this->webhookService->get($webhookId);

            echo "Webhook atual:\n";
            echo "URL: " . $webhookAtual['url'] . "\n";
            echo "Status: " . ($webhookAtual['active'] ? 'Ativo' : 'Inativo') . "\n\n";

            // Perguntar novos dados
            $novaUrl = perguntarUsuario("Nova URL", $webhookAtual['url']);
            $ativo = confirmarAcao("Webhook ativo?");

            $dadosAtualizacao = [
                'url' => $novaUrl,
                'active' => $ativo
            ];

            $webhookAtualizado = $this->webhookService->update($webhookId, $dadosAtualizacao);

            exibirSucesso("Webhook atualizado com sucesso!");
            echo "Nova URL: " . $webhookAtualizado['url'] . "\n";
            echo "Status: " . ($webhookAtualizado['active'] ? 'Ativo' : 'Inativo') . "\n";

        } catch (Exception $e) {
            exibirErro("Erro ao atualizar webhook: " . $e->getMessage());
        }
    }

    private function removerWebhook(): void
    {
        exibirTitulo("REMOVER WEBHOOK");

        try {
            $webhookId = perguntarUsuario("ID do webhook para remover");

            if (empty($webhookId)) {
                exibirErro("ID do webhook é obrigatório");
                return;
            }

            // Obter dados do webhook
            $webhook = $this->webhookService->get($webhookId);

            echo "Webhook a ser removido:\n";
            echo "ID: " . $webhook['id'] . "\n";
            echo "URL: " . $webhook['url'] . "\n\n";

            if (!confirmarAcao("Confirma a remoção do webhook?")) {
                exibirInfo("Remoção cancelada");
                return;
            }

            $this->webhookService->delete($webhookId);

            exibirSucesso("Webhook removido com sucesso!");

        } catch (Exception $e) {
            exibirErro("Erro ao remover webhook: " . $e->getMessage());
        }
    }

    private function criarDiretorios(): void
    {
        exibirTitulo("CRIAR DIRETÓRIOS NECESSÁRIOS");

        $diretorios = [
            $this->config['log_directory'] ?? __DIR__ . '/logs',
            $this->config['data_directory'] ?? __DIR__ . '/data'
        ];

        foreach ($diretorios as $diretorio) {
            if (!is_dir($diretorio)) {
                if (mkdir($diretorio, 0755, true)) {
                    exibirSucesso("Diretório criado: $diretorio");
                } else {
                    exibirErro("Erro ao criar diretório: $diretorio");
                }
            } else {
                exibirInfo("Diretório já existe: $diretorio");
            }
        }

        // Criar arquivo .gitignore se não existir
        $gitignore = __DIR__ . '/.gitignore';
        if (!file_exists($gitignore)) {
            $conteudo = "webhook-config-local.php\nlogs/\ndata/\n*.log\n";
            file_put_contents($gitignore, $conteudo);
            exibirSucesso("Arquivo .gitignore criado");
        }
    }
}

// ========== EXECUÇÃO ==========

try {
    $setup = new WebhookSetup();
    $setup->executarSetup();

} catch (Exception $e) {
    exibirErro("Erro fatal: " . $e->getMessage());
    exit(1);
}