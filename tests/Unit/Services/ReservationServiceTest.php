<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use Tests\TestCase;
use Mockery;
use Innochannel\Sdk\Client;
use Innochannel\Sdk\Services\ReservationService;
use Innochannel\Sdk\Models\Reservation;
use Innochannel\Sdk\Exceptions\ApiException;
use Innochannel\Sdk\Exceptions\ValidationException;
use Innochannel\Sdk\Exceptions\NotFoundException;

/**
 * Testes para ReservationService
 */
class ReservationServiceTest extends TestCase
{
    private Client $mockClient;
    private ReservationService $reservationService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->mockClient = Mockery::mock(Client::class);
        $this->reservationService = new ReservationService($this->mockClient);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function getMockReservation(): array
    {
        return [
            'id' => 'reservation-123',
            'property_id' => 'property-456',
            'room_id' => 'room-789',
            'rate_plan_id' => 'rate-plan-101',
            'guest_name' => 'John Doe',
            'guest_email' => 'john@example.com',
            'guest_phone' => '+1234567890',
            'check_in' => '2024-01-15',
            'check_out' => '2024-01-20',
            'adults' => 2,
            'children' => 1,
            'total_amount' => 500.00,
            'currency' => 'USD',
            'status' => 'confirmed',
            'source' => 'booking.com',
            'confirmation_code' => 'ABC123',
            'special_requests' => 'Late check-in',
            'created_at' => '2024-01-01 10:00:00',
            'updated_at' => '2024-01-01 10:00:00',
            'guest' => [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'phone' => '+1234567890',
                'address' => [
                    'street' => '123 Main St',
                    'city' => 'New York',
                    'state' => 'NY',
                    'zip' => '10001',
                    'country' => 'US'
                ]
            ],
            'payment' => [
                'method' => 'credit_card',
                'status' => 'paid',
                'amount' => 500.00,
                'currency' => 'USD'
            ]
        ];
    }

    // Reservation Management Tests
    public function testCreateReservationSuccess(): void
    {
        $reservationData = [
            'property_id' => 'property-456',
            'room_id' => 'room-789',
            'rate_plan_id' => 'rate-plan-101',
            'guest_name' => 'John Doe',
            'guest_email' => 'john@example.com',
            'guest_phone' => '+1234567890',
            'check_in' => '2024-01-15',
            'check_out' => '2024-01-20',
            'adults' => 2,
            'children' => 1,
            'source' => 'booking.com',
        ];

        $mockReservation = $this->getMockReservation();
        $mockResponse = ['data' => $mockReservation];

        $this->mockClient
            ->shouldReceive('post')
            ->once()
            ->with('/api/bookings', $reservationData)
            ->andReturn($mockResponse);

        $result = $this->reservationService->create($reservationData);

        $this->assertInstanceOf(Reservation::class, $result);
        $this->assertEquals('reservation-123', $result->getId());
    }

    public function testCreateReservationValidationFailure(): void
    {
        $invalidReservationData = [
            'property_id' => '', // Invalid: empty property_id
            'guest_name' => '', // Invalid: empty guest_name
            'check_in' => 'invalid-date', // Invalid: bad date format
            'check_out' => '2024-01-10', // Invalid: check_out before check_in
            'adults' => -1, // Invalid: negative adults
        ];

        $this->mockClient
            ->shouldReceive('post')
            ->once()
            ->with('/api/bookings', $invalidReservationData)
            ->andThrow(new ValidationException('Validation failed'));

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Validation failed');

        $this->reservationService->create($invalidReservationData);
    }

    public function testGetReservationSuccess(): void
    {
        $mockReservation = $this->getMockReservation();
        $mockResponse = ['data' => $mockReservation];

        $this->mockClient
            ->shouldReceive('get')
            ->once()
            ->with('/api/bookings/reservation-123')
            ->andReturn($mockResponse);

        $result = $this->reservationService->get('reservation-123');

        $this->assertInstanceOf(Reservation::class, $result);
        $this->assertEquals('reservation-123', $result->getId());
    }

    public function testGetReservationNotFound(): void
    {
        $this->mockClient
            ->shouldReceive('get')
            ->once()
            ->with('/api/bookings/non-existent')
            ->andThrow(new NotFoundException('Reservation not found'));

        $this->expectException(NotFoundException::class);

        $this->reservationService->get('non-existent');
    }

    public function testListReservationsWithDefaultFilters(): void
    {
        $mockResponse = [
            'data' => [$this->getMockReservation()],
            'meta' => [
                'total' => 1,
                'per_page' => 15,
                'current_page' => 1
            ]
        ];

        $this->mockClient
            ->shouldReceive('get')
            ->once()
            ->with('/api/bookings', [])
            ->andReturn($mockResponse);

        $result = $this->reservationService->list();

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertInstanceOf(Reservation::class, $result[0]);
    }

    public function testListReservationsWithCustomFilters(): void
    {
        $filters = [
            'property_id' => 'property-456',
            'status' => 'confirmed',
            'check_in_from' => '2024-01-01',
            'check_in_to' => '2024-01-31',
            'guest_name' => 'John',
            'page' => 1,
            'per_page' => 10
        ];

        $mockResponse = [
            'data' => [$this->getMockReservation()],
            'meta' => [
                'total' => 1,
                'per_page' => 10,
                'current_page' => 1
            ]
        ];

        $this->mockClient
            ->shouldReceive('get')
            ->once()
            ->with('/api/bookings', $filters)
            ->andReturn($mockResponse);

        $result = $this->reservationService->list($filters);

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
    }

    public function testUpdateReservationSuccess(): void
    {
        $updateData = [
            'guest_name' => 'Jane Doe',
            'guest_email' => 'jane@example.com',
            'special_requests' => 'Early check-in'
        ];

        $updatedReservation = array_merge($this->getMockReservation(), $updateData);
        $mockResponse = ['data' => $updatedReservation];

        $this->mockClient
            ->shouldReceive('put')
            ->once()
            ->with('/api/bookings/reservation-123', $updateData)
            ->andReturn($mockResponse);

        $result = $this->reservationService->update('reservation-123', $updateData);

        $this->assertInstanceOf(Reservation::class, $result);
    }

    public function testUpdateReservationValidationFailure(): void
    {
        $invalidUpdateData = [
            'guest_email' => 'invalid-email', // Invalid email format
            'check_in' => 'invalid-date', // Invalid date format
            'adults' => 'not-a-number', // Invalid type
        ];

        $this->mockClient
            ->shouldReceive('put')
            ->once()
            ->with('/api/bookings/reservation-123', $invalidUpdateData)
            ->andThrow(new ValidationException('Validation failed'));

        $this->expectException(ValidationException::class);

        $this->reservationService->update('reservation-123', $invalidUpdateData);
    }

    public function testCancelReservationSuccess(): void
    {
        $cancelData = [
            'reason' => 'Guest requested cancellation',
            'refund_amount' => 250.00
        ];

        $mockResponse = ['success' => true];

        $this->mockClient
            ->shouldReceive('post')
            ->once()
            ->with('/api/bookings/reservation-123/cancel', $cancelData)
            ->andReturn($mockResponse);

        $result = $this->reservationService->cancel('reservation-123', $cancelData);

        $this->assertTrue($result);
    }

    public function testConfirmReservationSuccess(): void
    {
        $confirmData = [
            'confirmation_code' => 'CONF123',
            'notes' => 'Reservation confirmed by guest'
        ];

        $confirmedReservation = array_merge($this->getMockReservation(), [
            'status' => 'confirmed',
            'confirmation_code' => 'CONF123'
        ]);

        $mockResponse = ['data' => $confirmedReservation];

        $this->mockClient
            ->shouldReceive('post')
            ->once()
            ->with('/api/bookings/reservation-123/confirm', $confirmData)
            ->andReturn($mockResponse);

        $result = $this->reservationService->confirm('reservation-123', $confirmData);

        $this->assertInstanceOf(Reservation::class, $result);
        $this->assertEquals('confirmed', $result->getStatus());
    }

    public function testModifyReservationSuccess(): void
    {
        $modificationData = [
            'check_in' => '2024-01-16',
            'check_out' => '2024-01-21',
            'adults' => 3,
            'total_amount' => 600.00
        ];

        $modifiedReservation = array_merge($this->getMockReservation(), $modificationData);
        $mockResponse = ['data' => $modifiedReservation];

        $this->mockClient
            ->shouldReceive('post')
            ->once()
            ->with('/api/bookings/reservation-123/modify', $modificationData)
            ->andReturn($mockResponse);

        $result = $this->reservationService->modify('reservation-123', $modificationData);

        $this->assertInstanceOf(Reservation::class, $result);
    }

    public function testModifyReservationValidationFailure(): void
    {
        $invalidModificationData = [
            'check_in' => '2024-01-25', // Invalid: after check_out
            'check_out' => '2024-01-20',
            'adults' => 0, // Invalid: no adults
        ];

        $this->mockClient
            ->shouldReceive('post')
            ->once()
            ->with('/api/bookings/reservation-123/modify', $invalidModificationData)
            ->andThrow(new ValidationException('Invalid modification data'));

        $this->expectException(ValidationException::class);

        $this->reservationService->modify('reservation-123', $invalidModificationData);
    }

    // Reservation History Tests
    public function testGetReservationHistorySuccess(): void
    {
        $mockHistory = [
            [
                'id' => 'history-1',
                'reservation_id' => 'reservation-123',
                'action' => 'created',
                'changes' => [],
                'user_id' => 'user-456',
                'created_at' => '2024-01-01 10:00:00'
            ],
            [
                'id' => 'history-2',
                'reservation_id' => 'reservation-123',
                'action' => 'updated',
                'changes' => ['guest_name' => ['old' => 'John', 'new' => 'John Doe']],
                'user_id' => 'user-456',
                'created_at' => '2024-01-01 11:00:00'
            ]
        ];

        $this->mockClient
            ->shouldReceive('get')
            ->once()
            ->with('/api/bookings/reservation-123/history')
            ->andReturn(['data' => $mockHistory]);

        $result = $this->reservationService->getHistory('reservation-123');

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
    }

    public function testSyncReservationWithPmsSuccess(): void
    {
        $syncOptions = [
            'force' => true,
            'entities' => ['reservation', 'guest', 'payment']
        ];

        $mockSyncResult = [
            'success' => true,
            'synced_entities' => ['reservation', 'guest', 'payment'],
            'sync_id' => 'sync-789',
            'synced_at' => '2024-01-01 12:00:00'
        ];

        $this->mockClient
            ->shouldReceive('post')
            ->once()
            ->with('/api/bookings/reservation-123/sync-pms', $syncOptions)
            ->andReturn(['data' => $mockSyncResult]);

        $result = $this->reservationService->syncWithPms('reservation-123', $syncOptions);

        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertEquals('sync-789', $result['sync_id']);
    }
}