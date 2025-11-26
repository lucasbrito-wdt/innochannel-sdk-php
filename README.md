# Innochannel Laravel SDK

[![Latest Version](https://img.shields.io/github/v/release/lucasbrito-wdt/innochannel-sdk-php?label=version)](https://github.com/lucasbrito-wdt/innochannel-sdk-php/releases)
[![PHP Version](https://img.shields.io/packagist/php-v/lucasbrito-wdt/innochannel-sdk)](https://packagist.org/packages/lucasbrito-wdt/innochannel-sdk)
[![License](https://img.shields.io/github/license/lucasbrito-wdt/innochannel-sdk-php)](LICENSE)
[![Total Downloads](https://img.shields.io/packagist/dt/lucasbrito-wdt/innochannel-sdk)](https://packagist.org/packages/lucasbrito-wdt/innochannel-sdk)

A comprehensive Laravel package for integrating with the Innochannel API, providing seamless connectivity between your Laravel application and the Innochannel platform for property management, reservation synchronization, and inventory management.

## Features

- **Complete Laravel Integration**: Service providers, facades, Artisan commands, and middleware
- **Webhook Management**: Automatic webhook handling with events and listeners
- **PMS Synchronization**: Bidirectional sync with Property Management Systems
- **Caching System**: Built-in caching for improved performance
- **Database Migrations**: Ready-to-use database tables for webhooks and sync status
- **Event System**: Laravel events for reservation, property, inventory, and general webhooks
- **Artisan Commands**: Installation, testing, and synchronization commands
- **Configuration Management**: Flexible configuration system

## Requirements

- PHP 8.1 or higher
- Laravel 10.0 or higher
- MySQL 5.7+ or PostgreSQL 10+

## Installation

### 1. Install via Composer

```bash
composer require innochannel/laravel-sdk
```

### 2. Run the Installation Command

```bash
php artisan innochannel:install
```

This command will:

- Publish configuration files
- Run database migrations
- Create necessary storage directories
- Set up initial configuration

### 3. Configure Your Environment

Add the following variables to your `.env` file:

```env
# Innochannel API Configuration
INNOCHANNEL_API_URL=https://api.innochannel.com
INNOCHANNEL_API_KEY=your_api_key_here
INNOCHANNEL_API_SECRET=your_api_secret_here
INNOCHANNEL_ENVIRONMENT=production

# PMS Configuration
INNOCHANNEL_PMS_SYSTEM=your_pms_system
INNOCHANNEL_PMS_URL=https://your-pms-url.com
INNOCHANNEL_PMS_USERNAME=your_pms_username
INNOCHANNEL_PMS_PASSWORD=your_pms_password

# Webhook Configuration
INNOCHANNEL_WEBHOOK_SECRET=your_webhook_secret
INNOCHANNEL_WEBHOOK_VERIFY_SIGNATURE=true

# Cache Configuration
INNOCHANNEL_CACHE_DRIVER=redis
INNOCHANNEL_CACHE_TTL=3600
```

### 4. Test Your Connection

```bash
php artisan innochannel:test-connection
```

## Configuration

The package publishes a configuration file at `config/innochannel.php`. You can customize:

- API endpoints and credentials
- PMS system settings
- Webhook configuration
- Cache settings
- Sync options

## Usage

### Using the Facade

```php
use Innochannel;

// Get property information
$property = Innochannel::getProperty('property-id');

// Create a reservation
$reservation = Innochannel::createReservation([
    'property_id' => 'property-id',
    'guest' => [
        'name' => 'John Doe',
        'email' => 'john@example.com'
    ],
    'check_in' => '2024-06-01',
    'check_out' => '2024-06-05',
    'rooms' => 1
]);

// Update inventory
Innochannel::updateInventory('property-id', [
    'date' => '2024-06-01',
    'room_type' => 'standard',
    'availability' => 10,
    'rate' => 150.00
]);
```

### Using Dependency Injection

```php
use Innochannel\Core\InnochannelClient;

class ReservationController extends Controller
{
    public function __construct(
        private InnochannelClient $innochannel
    ) {}

    public function createReservation(Request $request)
    {
        $reservation = $this->innochannel->createReservation($request->validated());

        return response()->json($reservation);
    }
}
```

## 📚 Documentação

### Configuração do Cliente

```php
use Innotel\ChannelManager\Client;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// Configuração básica
$client = new Client([
    'api_key' => 'sua-api-key'
]);

// Configuração avançada
$logger = new Logger('channel-manager');
$logger->pushHandler(new StreamHandler('logs/api.log', Logger::DEBUG));

$client = new Client([
    'api_key' => 'sua-api-key',
    'base_url' => 'https://api.innotel.com.br',
    'timeout' => 60,
    'connect_timeout' => 15,
    'logger' => $logger
]);
```

### Gerenciamento de Propriedades

```php
// Criar propriedade
$property = $client->properties()->create([
    'name' => 'Meu Hotel',
    'pms_type' => 'opera',
    'pms_credentials' => [
        'username' => 'user',
        'password' => 'pass',
        'endpoint' => 'https://pms.exemplo.com'
    ]
]);

// Listar propriedades
$properties = $client->properties()->list();

// Obter propriedade específica
$property = $client->properties()->get(123);

// Atualizar propriedade
$property = $client->properties()->update(123, [
    'name' => 'Novo Nome do Hotel'
]);

// Testar conexão PMS
$result = $client->properties()->testConnection([
    'pms_type' => 'opera',
    'credentials' => [
        'username' => 'user',
        'password' => 'pass',
        'endpoint' => 'https://pms.exemplo.com'
    ]
]);
```

### Gerenciamento de Quartos

```php
// Criar quarto
$room = $client->properties()->createRoom(123, [
    'name' => 'Quarto Standard',
    'room_type' => 'standard',
    'max_occupancy' => 2,
    'max_adults' => 2,
    'max_children' => 1,
    'size' => 25.5,
    'size_unit' => 'm²',
    'amenities' => ['wifi', 'tv', 'ar_condicionado'],
    'bed_types' => ['queen'],
    'view_type' => 'city'
]);

// Listar quartos
$rooms = $client->properties()->getRooms(123);

// Atualizar quarto
$room = $client->properties()->updateRoom(123, 456, [
    'name' => 'Quarto Standard Renovado'
]);
```

### Gerenciamento de Planos de Tarifas

```php
// Criar plano de tarifas
$ratePlan = $client->properties()->createRatePlan(123, [
    'name' => 'Tarifa Padrão',
    'currency' => 'BRL',
    'rate_type' => 'per_night',
    'restrictions' => [
        'min_stay' => 1,
        'max_stay' => 30,
        'min_advance_reservation' => 0,
        'max_advance_reservation' => 365
    ],
    'cancellation_policy' => [
        'free_cancellation_until' => 1,
        'penalty_percentage' => 100
    ],
    'is_refundable' => true
]);

// Listar planos de tarifas
$ratePlans = $client->properties()->getRatePlans(123);
```

### Gerenciamento de Inventário

```php
// Atualizar disponibilidade
$client->inventory()->updateAvailability(123, [
    'room_id' => 456,
    'date_from' => '2024-01-01',
    'date_to' => '2024-01-31',
    'availability' => 10
]);

// Atualizar tarifas
$client->inventory()->updateRates(123, [
    'room_id' => 456,
    'rate_plan_id' => 789,
    'date_from' => '2024-01-01',
    'date_to' => '2024-01-31',
    'rate' => 150.00
]);

// Consultar disponibilidade
$availability = $client->inventory()->getAvailability(123, [
    'date_from' => '2024-01-01',
    'date_to' => '2024-01-31',
    'room_id' => 456
]);
```

### Gerenciamento de Reservas

```php
// Listar reservas
$reservations = $client->reservations()->list([
    'property_id' => 123,
    'date_from' => '2024-01-01',
    'date_to' => '2024-01-31'
]);

// Obter reserva específica
$reservation = $client->reservations()->get('RES123456');

// Atualizar status da reserva
$reservation = $client->reservations()->updateStatus('RES123456', 'confirmed');
```

## 🔧 Tratamento de Erros

```php
use Innotel\ChannelManager\Exceptions\ApiException;
use Innotel\ChannelManager\Exceptions\AuthenticationException;
use Innotel\ChannelManager\Exceptions\ValidationException;

try {
    $property = $client->properties()->create([
        'name' => 'Meu Hotel'
        // dados incompletos
    ]);
} catch (ValidationException $e) {
    echo "Erro de validação: " . $e->getMessage() . "\n";
    echo "Erros específicos: " . $e->getFormattedErrors() . "\n";
} catch (AuthenticationException $e) {
    echo "Erro de autenticação: " . $e->getMessage() . "\n";
} catch (ApiException $e) {
    echo "Erro da API: " . $e->getMessage() . "\n";
    echo "Código HTTP: " . $e->getCode() . "\n";

    if ($e->isServerError()) {
        echo "Erro do servidor - tente novamente mais tarde\n";
    }
}
```

## 🧪 Testes

```bash
# Executar todos os testes
composer test

# Executar testes com cobertura
composer test-coverage

# Análise estática
composer phpstan

# Verificar padrões de código
composer cs-check

# Corrigir padrões de código
composer cs-fix

# Executar todas as verificações de qualidade
composer quality
```

## 📝 Logging

O SDK suporta qualquer logger compatível com PSR-3:

```php
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;

$logger = new Logger('channel-manager');
$logger->pushHandler(new StreamHandler('php://stdout', Logger::INFO));
$logger->pushHandler(new RotatingFileHandler('logs/api.log', 0, Logger::DEBUG));

$client = new Client([
    'api_key' => 'sua-api-key',
    'logger' => $logger
]);
```

## 🔒 Segurança

- Nunca exponha sua API key em código público
- Use variáveis de ambiente para credenciais
- Implemente rate limiting em sua aplicação
- Monitore logs para detectar uso anômalo

```php
// Exemplo usando variáveis de ambiente
$client = new Client([
    'api_key' => $_ENV['INNOTEL_API_KEY'] ?? getenv('INNOTEL_API_KEY')
]);
```

## 🤝 Contribuindo

1. Fork o projeto
2. Crie uma branch para sua feature (`git checkout -b feature/nova-feature`)
3. Commit suas mudanças (`git commit -am 'Adiciona nova feature'`)
4. Push para a branch (`git push origin feature/nova-feature`)
5. Abra um Pull Request

## 📄 Licença

Este projeto está licenciado sob a Licença MIT - veja o arquivo [LICENSE](LICENSE) para detalhes.

## 🆘 Suporte

- 📧 Email: <dev@innotel.com.br>
- 📚 Documentação: <https://docs.innotel.com.br>
- 🐛 Issues: <https://github.com/innotel/channel-manager-sdk-php/issues>

## 📦 Versionamento

Este projeto segue o [Semantic Versioning](https://semver.org/lang/pt-BR/).

Para ver o histórico completo de mudanças, consulte o [CHANGELOG.md](CHANGELOG.md).

### Versão Atual: v1.0.0

Para criar uma nova versão, consulte o guia em [RELEASE.md](RELEASE.md).

## 📈 Changelog

Veja o histórico completo de mudanças em [CHANGELOG.md](CHANGELOG.md).

### v1.0.0 - 2025-11-26

- ✨ Lançamento inicial
- 🚀 Suporte completo à API do Innochannel
- 🔧 Sistema de webhooks e eventos
- 📊 Integração com Laravel
- 🐛 Correções de bugs críticos
- Gerenciamento de propriedades, quartos e tarifas
- Controle de inventário e reservas
- Tratamento robusto de erros
- Logging integrado
