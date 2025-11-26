# Changelog

Todas as mudanças notáveis neste projeto serão documentadas neste arquivo.

O formato é baseado em [Keep a Changelog](https://keepachangelog.com/pt-BR/1.0.0/),
e este projeto adere ao [Semantic Versioning](https://semver.org/lang/pt-BR/).

## [1.0.0] - 2025-11-26

### Adicionado

- Cliente SDK completo para integração com Innochannel API
- Suporte para gerenciamento de propriedades (criar, listar, atualizar, deletar)
- Suporte para gerenciamento de quartos e planos de tarifa
- Suporte para gerenciamento de reservas (criar, atualizar, cancelar, confirmar)
- Suporte para inventário (disponibilidade e tarifas)
- Suporte para conexões OTA (criar, atualizar, deletar, testar, sincronizar)
- Sistema de webhooks completo
- Sistema de eventos para webhooks
- Integração com Laravel (Service Providers, Facades, Middleware)
- Comandos Artisan para instalação, testes e sincronização
- Sistema de cache para melhor performance
- Suporte para sincronização bidirecional com PMS
- Sistema de monitoramento e métricas
- Tratamento de erros robusto com exceções específicas
- Sistema de retry automático para requisições
- Sistema de autenticação com API Key
- Suporte para Laravel 10.x, 11.x e 12.x
- Documentação completa em português

### Corrigido

- Tratamento de exceções genéricas no método `request()` do Client
- Prevenção de retry indefinido em exceções não relacionadas a HTTP

### Segurança

- Implementação de autenticação segura via API Key e Secret
- Sanitização de logs para remover dados sensíveis
- Validação de entrada em todos os métodos públicos

## [Unreleased]

### Corrigido

- Correção do erro "Undefined array key 'duration'" no InnochannelHttpWatcher
- Padronização da chave de duração de 'duration_ms' para 'duration' em todos os registros do Telescope
- Implementação correta do cálculo de duração de requisições HTTP usando `spl_object_hash()`
- Adicionado tratamento de exceções nos métodos `recordEntry()` e `recordTelescopeEntry()` para prevenir falhas na aplicação quando o Telescope falha ao registrar entradas
- Adicionado armazenamento de tempos de início de requisições no `InnochannelHttpWatcher` para cálculo preciso de duração

### Planejado

- Suporte para mais tipos de PMS
- Melhorias no sistema de cache
- Suporte para webhooks assíncronos
- Dashboard de monitoramento
- Relatórios avançados

[1.0.0]: https://github.com/lucasbrito-wdt/innochannel-sdk-php/releases/tag/v1.0.0
