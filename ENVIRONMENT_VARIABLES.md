# Variáveis de Ambiente - Innochannel SDK

Este documento descreve todas as variáveis de ambiente disponíveis para configurar o Innochannel SDK.

## 📋 Índice

- [Configuração Básica](#configuração-básica)
- [Credenciais da API](#credenciais-da-api)
- [Endpoints](#endpoints)
- [Configurações de Requisição](#configurações-de-requisição)
- [Sistema de Logs](#sistema-de-logs)
- [Cache](#cache)
- [Webhooks](#webhooks)
- [Rate Limiting](#rate-limiting)
- [Configuração de Serviços](#configuração-de-serviços)
- [Sistema de Eventos](#sistema-de-eventos)
- [Configurações de Desenvolvimento](#configurações-de-desenvolvimento)
- [Exemplos de Configuração](#exemplos-de-configuração)

## 🔧 Configuração Básica

### Credenciais da API

| Variável | Tipo | Padrão | Descrição |
|----------|------|--------|-----------|
| `INNOCHANNEL_API_KEY` | string | **obrigatório** | Chave da API fornecida pelo Innochannel |
| `INNOCHANNEL_API_SECRET` | string | **obrigatório** | Segredo da API fornecida pelo Innochannel |

### Endpoints

| Variável | Tipo | Padrão | Descrição |
|----------|------|--------|-----------|
| `INNOCHANNEL_BASE_URL` | string | `https://api.innochannel.com` | URL base da API |
| `INNOCHANNEL_WEBHOOK_URL` | string | - | URL para receber webhooks |

## 🌐 Configurações de Requisição

| Variável | Tipo | Padrão | Descrição |
|----------|------|--------|-----------|
| `INNOCHANNEL_TIMEOUT` | integer | `30` | Timeout das requisições em segundos |
| `INNOCHANNEL_RETRY_ATTEMPTS` | integer | `3` | Número de tentativas em caso de falha |
| `INNOCHANNEL_RETRY_DELAY` | integer | `1000` | Delay entre tentativas em milissegundos |

## 📝 Sistema de Logs

| Variável | Tipo | Padrão | Descrição |
|----------|------|--------|-----------|
| `INNOCHANNEL_LOGGING_ENABLED` | boolean | `true` | Habilita/desabilita logs |
| `INNOCHANNEL_LOG_CHANNEL` | string | `daily` | Canal de log do Laravel |
| `INNOCHANNEL_LOG_LEVEL` | string | `info` | Nível de log (debug, info, warning, error) |
| `INNOCHANNEL_LOG_REQUESTS` | boolean | `true` | Loga requisições HTTP |
| `INNOCHANNEL_LOG_RESPONSES` | boolean | `false` | Loga respostas HTTP |
| `INNOCHANNEL_LOG_ERRORS` | boolean | `true` | Loga erros |

## 💾 Cache

| Variável | Tipo | Padrão | Descrição |
|----------|------|--------|-----------|
| `INNOCHANNEL_CACHE_ENABLED` | boolean | `true` | Habilita/desabilita cache |
| `INNOCHANNEL_CACHE_STORE` | string | `redis` | Driver de cache |
| `INNOCHANNEL_CACHE_TTL` | integer | `3600` | TTL do cache em segundos |
| `INNOCHANNEL_CACHE_PREFIX` | string | `innochannel` | Prefixo das chaves de cache |

## 🔗 Webhooks

| Variável | Tipo | Padrão | Descrição |
|----------|------|--------|-----------|
| `INNOCHANNEL_WEBHOOKS_ENABLED` | boolean | `true` | Habilita/desabilita webhooks |
| `INNOCHANNEL_WEBHOOK_SECRET` | string | - | Segredo para validação de webhooks |
| `INNOCHANNEL_WEBHOOK_VERIFY_SIGNATURE` | boolean | `true` | Verifica assinatura dos webhooks |

## ⚡ Rate Limiting

| Variável | Tipo | Padrão | Descrição |
|----------|------|--------|-----------|
| `INNOCHANNEL_RATE_LIMITING_ENABLED` | boolean | `true` | Habilita rate limiting |
| `INNOCHANNEL_RATE_LIMIT_RPM` | integer | `60` | Requisições por minuto |
| `INNOCHANNEL_RATE_LIMIT_BURST` | integer | `10` | Burst de requisições |

## 🏨 Configuração de Serviços

### Reservation Service

| Variável | Tipo | Padrão | Descrição |
|----------|------|--------|-----------|
| `INNOCHANNEL_RESERVATION_AUTO_SYNC` | boolean | `true` | Sincronização automática de reservas |
| `INNOCHANNEL_RESERVATION_SYNC_DIRECTION` | string | `both` | Direção da sincronização (in, out, both) |
| `INNOCHANNEL_RESERVATION_VALIDATION_STRICT` | boolean | `true` | Validação rigorosa de dados |

### Property Service

| Variável | Tipo | Padrão | Descrição |
|----------|------|--------|-----------|
| `INNOCHANNEL_PROPERTY_AUTO_SYNC` | boolean | `true` | Sincronização automática de propriedades |
| `INNOCHANNEL_PROPERTY_CACHE_DURATION` | integer | `7200` | Duração do cache em segundos |

### Inventory Service

| Variável | Tipo | Padrão | Descrição |
|----------|------|--------|-----------|
| `INNOCHANNEL_INVENTORY_AUTO_SYNC` | boolean | `true` | Sincronização automática de inventário |
| `INNOCHANNEL_INVENTORY_SYNC_INTERVAL` | integer | `300` | Intervalo de sincronização em segundos |
| `INNOCHANNEL_INVENTORY_BATCH_SIZE` | integer | `100` | Tamanho do lote para sincronização |

## 🎯 Sistema de Eventos

### Configuração Geral

| Variável | Tipo | Padrão | Descrição |
|----------|------|--------|-----------|
| `INNOCHANNEL_EVENTS_ENABLED` | boolean | `true` | Habilita sistema de eventos |
| `INNOCHANNEL_EVENTS_ASYNC` | boolean | `true` | Execução assíncrona de eventos |
| `INNOCHANNEL_EVENTS_QUEUE` | string | `default` | Fila para eventos assíncronos |

### Eventos de Reservation

| Variável | Tipo | Padrão | Descrição |
|----------|------|--------|-----------|
| `INNOCHANNEL_RESERVATION_CREATED_ENABLED` | boolean | `true` | Evento de reserva criada |
| `INNOCHANNEL_RESERVATION_UPDATED_ENABLED` | boolean | `true` | Evento de reserva atualizada |
| `INNOCHANNEL_RESERVATION_CANCELLED_ENABLED` | boolean | `true` | Evento de reserva cancelada |
| `INNOCHANNEL_RESERVATION_CONFIRMED_ENABLED` | boolean | `true` | Evento de reserva confirmada |
| `INNOCHANNEL_RESERVATION_DELETED_ENABLED` | boolean | `true` | Evento de reserva deletada |

### Eventos de Property

| Variável | Tipo | Padrão | Descrição |
|----------|------|--------|-----------|
| `INNOCHANNEL_PROPERTY_CREATED_ENABLED` | boolean | `true` | Evento de propriedade criada |
| `INNOCHANNEL_PROPERTY_UPDATED_ENABLED` | boolean | `true` | Evento de propriedade atualizada |
| `INNOCHANNEL_PROPERTY_ACTIVATED_ENABLED` | boolean | `true` | Evento de propriedade ativada |
| `INNOCHANNEL_PROPERTY_DEACTIVATED_ENABLED` | boolean | `true` | Evento de propriedade desativada |
| `INNOCHANNEL_PROPERTY_DELETED_ENABLED` | boolean | `true` | Evento de propriedade deletada |
| `INNOCHANNEL_PROPERTY_PMS_CREDENTIALS_UPDATED_ENABLED` | boolean | `true` | Evento de credenciais PMS atualizadas |

### Eventos de Rate Plan

| Variável | Tipo | Padrão | Descrição |
|----------|------|--------|-----------|
| `INNOCHANNEL_RATE_PLAN_CREATED_ENABLED` | boolean | `true` | Evento de plano de tarifa criado |
| `INNOCHANNEL_RATE_PLAN_UPDATED_ENABLED` | boolean | `true` | Evento de plano de tarifa atualizado |
| `INNOCHANNEL_RATE_PLAN_ACTIVATED_ENABLED` | boolean | `true` | Evento de plano de tarifa ativado |
| `INNOCHANNEL_RATE_PLAN_DEACTIVATED_ENABLED` | boolean | `true` | Evento de plano de tarifa desativado |
| `INNOCHANNEL_RATE_PLAN_DELETED_ENABLED` | boolean | `true` | Evento de plano de tarifa deletado |
| `INNOCHANNEL_RATE_PLAN_RESTRICTIONS_UPDATED_ENABLED` | boolean | `true` | Evento de restrições atualizadas |
| `INNOCHANNEL_RATE_PLAN_CANCELLATION_POLICY_UPDATED_ENABLED` | boolean | `true` | Evento de política de cancelamento atualizada |

### Eventos de Room

| Variável | Tipo | Padrão | Descrição |
|----------|------|--------|-----------|
| `INNOCHANNEL_ROOM_CREATED_ENABLED` | boolean | `true` | Evento de quarto criado |
| `INNOCHANNEL_ROOM_UPDATED_ENABLED` | boolean | `true` | Evento de quarto atualizado |
| `INNOCHANNEL_ROOM_ACTIVATED_ENABLED` | boolean | `true` | Evento de quarto ativado |
| `INNOCHANNEL_ROOM_DEACTIVATED_ENABLED` | boolean | `true` | Evento de quarto desativado |
| `INNOCHANNEL_ROOM_DELETED_ENABLED` | boolean | `true` | Evento de quarto deletado |
| `INNOCHANNEL_ROOM_AMENITIES_UPDATED_ENABLED` | boolean | `true` | Evento de comodidades atualizadas |
| `INNOCHANNEL_ROOM_BED_TYPES_UPDATED_ENABLED` | boolean | `true` | Evento de tipos de cama atualizados |
| `INNOCHANNEL_ROOM_CAPACITY_UPDATED_ENABLED` | boolean | `true` | Evento de capacidade atualizada |

### Logs de Eventos

| Variável | Tipo | Padrão | Descrição |
|----------|------|--------|-----------|
| `INNOCHANNEL_EVENTS_LOGGING_ENABLED` | boolean | `false` | Habilita logs de eventos |
| `INNOCHANNEL_EVENTS_LOG_CHANNEL` | string | `default` | Canal de log para eventos |
| `INNOCHANNEL_EVENTS_LOG_LEVEL` | string | `info` | Nível de log para eventos |

### Performance de Eventos

| Variável | Tipo | Padrão | Descrição |
|----------|------|--------|-----------|
| `INNOCHANNEL_MAX_LISTENERS_PER_EVENT` | integer | `0` | Máximo de listeners por evento (0 = ilimitado) |
| `INNOCHANNEL_LISTENER_TIMEOUT` | integer | `0` | Timeout para listeners em segundos (0 = sem timeout) |
| `INNOCHANNEL_STOP_ON_FAILURE` | boolean | `false` | Para execução quando um listener falha |

### Debug de Eventos

| Variável | Tipo | Padrão | Descrição |
|----------|------|--------|-----------|
| `INNOCHANNEL_EVENTS_DEBUG` | boolean | `false` | Habilita debug de eventos |
| `INNOCHANNEL_EVENTS_COLLECT_STATS` | boolean | `false` | Coleta estatísticas de performance |

## 🔧 Configurações de Desenvolvimento

| Variável | Tipo | Padrão | Descrição |
|----------|------|--------|-----------|
| `INNOCHANNEL_DEBUG` | boolean | `false` | Modo debug |
| `INNOCHANNEL_MOCK_RESPONSES` | boolean | `false` | Usar respostas mockadas |
| `INNOCHANNEL_TEST_MODE` | boolean | `false` | Modo de teste |
| `INNOCHANNEL_DB_CONNECTION` | string | `default` | Conexão de banco de dados |

## 🔧 Development & Debug

```bash
# Debug mode
INNOCHANNEL_DEBUG=false

# Mock responses for testing
INNOCHANNEL_MOCK_RESPONSES=false

# Test mode
INNOCHANNEL_TEST_MODE=false
```

## 🗄️ Database Configuration

```bash
# Database connection for Innochannel tables
INNOCHANNEL_DB_CONNECTION=default
```

## 📋 Exemplos de Configuração

### Configuração Mínima (.env)

```env
# Credenciais obrigatórias
INNOCHANNEL_API_KEY=your_api_key_here
INNOCHANNEL_API_SECRET=your_api_secret_here

# URL base (opcional, usa padrão se não definido)
INNOCHANNEL_BASE_URL=https://api.innochannel.com
```

### Configuração de Produção (.env)

```env
# Credenciais
INNOCHANNEL_API_KEY=prod_api_key
INNOCHANNEL_API_SECRET=prod_api_secret

# Endpoints
INNOCHANNEL_BASE_URL=https://api.innochannel.com
INNOCHANNEL_WEBHOOK_URL=https://yoursite.com/innochannel/webhooks

# Logs
INNOCHANNEL_LOGGING_ENABLED=true
INNOCHANNEL_LOG_CHANNEL=daily
INNOCHANNEL_LOG_LEVEL=warning
INNOCHANNEL_LOG_REQUESTS=false
INNOCHANNEL_LOG_RESPONSES=false
INNOCHANNEL_LOG_ERRORS=true

# Cache
INNOCHANNEL_CACHE_ENABLED=true
INNOCHANNEL_CACHE_STORE=redis
INNOCHANNEL_CACHE_TTL=7200

# Webhooks
INNOCHANNEL_WEBHOOKS_ENABLED=true
INNOCHANNEL_WEBHOOK_SECRET=your_webhook_secret
INNOCHANNEL_WEBHOOK_VERIFY_SIGNATURE=true

# Rate Limiting
INNOCHANNEL_RATE_LIMITING_ENABLED=true
INNOCHANNEL_RATE_LIMIT_RPM=120

# Eventos
INNOCHANNEL_EVENTS_ENABLED=true
INNOCHANNEL_EVENTS_ASYNC=true
INNOCHANNEL_EVENTS_QUEUE=innochannel

# Debug desabilitado
INNOCHANNEL_DEBUG=false
INNOCHANNEL_TEST_MODE=false
```

### Configuração de Desenvolvimento (.env)

```env
# Credenciais de teste
INNOCHANNEL_API_KEY=test_api_key
INNOCHANNEL_API_SECRET=test_api_secret

# Endpoints de teste
INNOCHANNEL_BASE_URL=https://api-staging.innochannel.com
INNOCHANNEL_WEBHOOK_URL=https://localhost:8000/innochannel/webhooks

# Logs detalhados
INNOCHANNEL_LOGGING_ENABLED=true
INNOCHANNEL_LOG_CHANNEL=single
INNOCHANNEL_LOG_LEVEL=debug
INNOCHANNEL_LOG_REQUESTS=true
INNOCHANNEL_LOG_RESPONSES=true
INNOCHANNEL_LOG_ERRORS=true

# Cache desabilitado para desenvolvimento
INNOCHANNEL_CACHE_ENABLED=false

# Rate limiting relaxado
INNOCHANNEL_RATE_LIMITING_ENABLED=false

# Debug habilitado
INNOCHANNEL_DEBUG=true
INNOCHANNEL_MOCK_RESPONSES=true
INNOCHANNEL_TEST_MODE=true

# Eventos com debug
INNOCHANNEL_EVENTS_DEBUG=true
INNOCHANNEL_EVENTS_COLLECT_STATS=true
```

## 🔍 Validação de Configuração

Para verificar se suas variáveis estão configuradas corretamente, use o comando:

```bash
php artisan innochannel:test-connection --detailed
```

Este comando verificará:

- ✅ Presença das credenciais obrigatórias
- ✅ Conectividade com a API
- ✅ Configuração de endpoints
- ✅ Configuração de cache e logs

## 🚨 Variáveis Obrigatórias

As seguintes variáveis são **obrigatórias** para o funcionamento do SDK:

1. `INNOCHANNEL_API_KEY`
2. `INNOCHANNEL_API_SECRET`

Todas as outras variáveis possuem valores padrão e são opcionais.

## 🔒 Segurança

⚠️ **Importante**: Nunca commite arquivos `.env` com credenciais reais no controle de versão. Use sempre o arquivo `.env.example` como template.

### Variáveis Sensíveis

As seguintes variáveis contêm informações sensíveis e devem ser protegidas:

- `INNOCHANNEL_API_KEY`
- `INNOCHANNEL_API_SECRET`
- `INNOCHANNEL_WEBHOOK_SECRET`

## 📚 Recursos Adicionais

- [Guia de Integração](INTEGRATION_GUIDE.md)
- [Guia de Publicação](PUBLISHING_GUIDE.md)
- [Documentação da API](https://docs.innochannel.com)
