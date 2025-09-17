<?php

declare(strict_types=1);

// Autoload das dependências
require_once __DIR__ . '/../vendor/autoload.php';

// Configurações de timezone
date_default_timezone_set('UTC');

// Configurações de erro para testes
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Configurações de memória para testes
ini_set('memory_limit', '512M');

// Configurações de mock para testes
if (!defined('INNOCHANNEL_TEST_MODE')) {
    define('INNOCHANNEL_TEST_MODE', true);
}

// Mock de variáveis de ambiente para testes
if (!getenv('INNOCHANNEL_API_URL')) {
    putenv('INNOCHANNEL_API_URL=https://api.innochannel.test');
}

if (!getenv('INNOCHANNEL_API_KEY')) {
    putenv('INNOCHANNEL_API_KEY=test-api-key-123');
}

if (!getenv('INNOCHANNEL_CLIENT_ID')) {
    putenv('INNOCHANNEL_CLIENT_ID=test-client-id');
}

if (!getenv('INNOCHANNEL_CLIENT_SECRET')) {
    putenv('INNOCHANNEL_CLIENT_SECRET=test-client-secret');
}

// Configurações de teste para HTTP
if (!defined('INNOCHANNEL_TEST_TIMEOUT')) {
    define('INNOCHANNEL_TEST_TIMEOUT', 30);
}

if (!defined('INNOCHANNEL_TEST_RETRY_ATTEMPTS')) {
    define('INNOCHANNEL_TEST_RETRY_ATTEMPTS', 3);
}

// Helpers para testes
class TestHelper
{
    /**
     * Gera dados de propriedade para testes
     */
    public static function createPropertyData(array $overrides = []): array
    {
        return array_merge([
            'id' => 'prop-' . uniqid(),
            'name' => 'Test Hotel ' . rand(1, 1000),
            'description' => 'A test hotel for unit testing',
            'address' => [
                'street' => 'Test Street, ' . rand(1, 999),
                'city' => 'Test City',
                'state' => 'TS',
                'country' => 'BR',
                'zipCode' => sprintf('%05d-%03d', rand(10000, 99999), rand(100, 999))
            ],
            'contact' => [
                'phone' => '+55 11 ' . rand(10000, 99999) . '-' . rand(1000, 9999),
                'email' => 'test' . rand(1, 1000) . '@hotel.com',
                'website' => 'https://testhotel' . rand(1, 1000) . '.com'
            ],
            'amenities' => ['wifi', 'parking', 'pool'],
            'checkInTime' => '15:00',
            'checkOutTime' => '12:00',
            'currency' => 'BRL',
            'timezone' => 'America/Sao_Paulo',
            'active' => true,
            'createdAt' => date('c'),
            'updatedAt' => date('c')
        ], $overrides);
    }

    /**
     * Gera dados de quarto para testes
     */
    public static function createRoomData(array $overrides = []): array
    {
        return array_merge([
            'id' => 'room-' . uniqid(),
            'propertyId' => 'prop-123',
            'name' => 'Test Room ' . rand(1, 100),
            'description' => 'A test room for unit testing',
            'type' => 'standard',
            'capacity' => [
                'adults' => rand(1, 4),
                'children' => rand(0, 2),
                'total' => rand(1, 6)
            ],
            'beds' => [
                ['type' => 'queen', 'quantity' => 1]
            ],
            'size' => rand(20, 50),
            'amenities' => ['air_conditioning', 'tv', 'minibar'],
            'images' => ['https://example.com/room' . rand(1, 10) . '.jpg'],
            'active' => true,
            'createdAt' => date('c'),
            'updatedAt' => date('c')
        ], $overrides);
    }

    /**
     * Gera dados de hóspede para testes
     */
    public static function createGuestData(array $overrides = []): array
    {
        $firstName = 'Guest' . rand(1, 1000);
        $lastName = 'Test' . rand(1, 1000);
        
        return array_merge([
            'firstName' => $firstName,
            'lastName' => $lastName,
            'email' => strtolower($firstName . '.' . $lastName) . '@email.com',
            'phone' => '+55 11 9' . rand(1000, 9999) . '-' . rand(1000, 9999),
            'document' => [
                'type' => 'cpf',
                'number' => sprintf('%03d.%03d.%03d-%02d', 
                    rand(100, 999), 
                    rand(100, 999), 
                    rand(100, 999), 
                    rand(10, 99)
                )
            ],
            'address' => [
                'street' => 'Test Street, ' . rand(1, 999),
                'city' => 'São Paulo',
                'state' => 'SP',
                'country' => 'BR',
                'zipCode' => sprintf('%05d-%03d', rand(10000, 99999), rand(100, 999))
            ],
            'dateOfBirth' => date('Y-m-d', strtotime('-' . rand(18, 80) . ' years')),
            'nationality' => 'BR'
        ], $overrides);
    }

    /**
     * Gera dados de reserva para testes
     */
    public static function createReservationData(array $overrides = []): array
    {
        $checkIn = date('Y-m-d', strtotime('+' . rand(1, 30) . ' days'));
        $checkOut = date('Y-m-d', strtotime($checkIn . ' +' . rand(1, 7) . ' days'));
        $nights = (strtotime($checkOut) - strtotime($checkIn)) / (60 * 60 * 24);
        
        return array_merge([
            'id' => 'reservation-' . uniqid(),
            'propertyId' => 'prop-123',
            'roomId' => 'room-123',
            'ratePlanId' => 'rate-123',
            'confirmationNumber' => 'CONF' . strtoupper(uniqid()),
            'status' => 'confirmed',
            'guest' => self::createGuestData(),
            'dates' => [
                'checkIn' => $checkIn,
                'checkOut' => $checkOut,
                'nights' => $nights
            ],
            'occupancy' => [
                'adults' => rand(1, 3),
                'children' => rand(0, 2),
                'total' => rand(1, 5)
            ],
            'pricing' => [
                'baseAmount' => rand(100, 500) * $nights,
                'taxes' => rand(10, 50),
                'fees' => rand(5, 25),
                'totalAmount' => rand(150, 600) * $nights,
                'currency' => 'BRL'
            ],
            'payment' => [
                'method' => 'credit_card',
                'status' => 'paid',
                'transactionId' => 'txn-' . uniqid()
            ],
            'source' => 'booking.com',
            'specialRequests' => 'Test special request',
            'createdAt' => date('c'),
            'updatedAt' => date('c')
        ], $overrides);
    }

    /**
     * Gera dados de plano de tarifa para testes
     */
    public static function createRatePlanData(array $overrides = []): array
    {
        return array_merge([
            'id' => 'rate-' . uniqid(),
            'propertyId' => 'prop-123',
            'roomId' => 'room-123',
            'name' => 'Test Rate Plan ' . rand(1, 100),
            'description' => 'A test rate plan for unit testing',
            'baseRate' => rand(100, 500),
            'currency' => 'BRL',
            'inclusions' => ['breakfast', 'wifi'],
            'restrictions' => [
                'minStay' => rand(1, 3),
                'maxStay' => rand(7, 30),
                'minAdvanceReservation' => 0,
                'maxAdvanceReservation' => 365
            ],
            'cancellationPolicy' => [
                'type' => 'flexible',
                'deadlineHours' => 24,
                'penaltyType' => 'percentage',
                'penaltyValue' => 0
            ],
            'active' => true,
            'createdAt' => date('c'),
            'updatedAt' => date('c')
        ], $overrides);
    }

    /**
     * Gera resposta de API paginada para testes
     */
    public static function createPaginatedResponse(array $data, int $page = 1, int $limit = 10): array
    {
        return [
            'data' => $data,
            'total' => count($data),
            'page' => $page,
            'limit' => $limit,
            'totalPages' => ceil(count($data) / $limit)
        ];
    }

    /**
     * Gera headers HTTP para testes
     */
    public static function createHttpHeaders(array $additional = []): array
    {
        return array_merge([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'User-Agent' => 'Innochannel-PHP-SDK/1.0.0',
            'X-Request-ID' => uniqid()
        ], $additional);
    }

    /**
     * Valida formato de email
     */
    public static function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Valida formato de telefone brasileiro
     */
    public static function isValidBrazilianPhone(string $phone): bool
    {
        return preg_match('/^\+55\s\d{2}\s\d{4,5}-\d{4}$/', $phone) === 1;
    }

    /**
     * Valida formato de CPF
     */
    public static function isValidCPF(string $cpf): bool
    {
        return preg_match('/^\d{3}\.\d{3}\.\d{3}-\d{2}$/', $cpf) === 1;
    }

    /**
     * Valida formato de data ISO 8601
     */
    public static function isValidISODate(string $date): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    /**
     * Valida formato de datetime ISO 8601
     */
    public static function isValidISODateTime(string $datetime): bool
    {
        $d = \DateTime::createFromFormat(\DateTime::ATOM, $datetime);
        return $d !== false;
    }
}

// Matchers personalizados para PHPUnit (se necessário)
if (class_exists('PHPUnit\Framework\Assert')) {
    class CustomAssertions extends \PHPUnit\Framework\Assert
    {
        public static function assertValidEmail(string $email, string $message = ''): void
        {
            self::assertTrue(
                TestHelper::isValidEmail($email),
                $message ?: "Failed asserting that '$email' is a valid email address."
            );
        }

        public static function assertValidBrazilianPhone(string $phone, string $message = ''): void
        {
            self::assertTrue(
                TestHelper::isValidBrazilianPhone($phone),
                $message ?: "Failed asserting that '$phone' is a valid Brazilian phone number."
            );
        }

        public static function assertValidCPF(string $cpf, string $message = ''): void
        {
            self::assertTrue(
                TestHelper::isValidCPF($cpf),
                $message ?: "Failed asserting that '$cpf' is a valid CPF."
            );
        }

        public static function assertValidISODate(string $date, string $message = ''): void
        {
            self::assertTrue(
                TestHelper::isValidISODate($date),
                $message ?: "Failed asserting that '$date' is a valid ISO date."
            );
        }

        public static function assertValidISODateTime(string $datetime, string $message = ''): void
        {
            self::assertTrue(
                TestHelper::isValidISODateTime($datetime),
                $message ?: "Failed asserting that '$datetime' is a valid ISO datetime."
            );
        }
    }
}

echo "Innochannel SDK PHP Test Bootstrap loaded successfully.\n";