<?php

declare(strict_types=1);

namespace Innochannel\Sdk\Tests\Unit\Services;

use Innochannel\Sdk\Client;
use Innochannel\Sdk\Services\BookingService;
use Innochannel\Sdk\Models\Booking;
use Innochannel\Sdk\Exceptions\ValidationException;
use Innochannel\Sdk\Exceptions\NotFoundException;
use PHPUnit\Framework\TestCase;
use Mockery;

class BookingServiceTest extends TestCase
{
    private Client $mockClient;
    private BookingService $bookingService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->mockClient = Mockery::mock(Client::class);
        $this->bookingService = new BookingService($this->mockClient);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function getMockBooking(): array
    {
        return [
            'id' => 'booking-123',
            'propertyId' => 'prop-123',
            'roomId' => 'room-123',
            'ratePlanId' => 'rate-123',
            'confirmationNumber' => 'CONF123456',
            'status' => 'confirmed',
            'guest' => [
                'firstName' => 'João',
                'lastName' => 'Silva',
                'email' => 'joao.silva@email.com',
                'phone' => '+55 11 99999-9999',
                'document' => [
                    'type' => 'cpf',
                    'number' => '123.456.789-00'
                ]
            ],
            'dates' => [
                'checkIn' => '2025-12-15',
                'checkOut' => '2025-12-18',
                'nights' => 3
            ],
            'occupancy' => [
                'adults' => 2,
                'children' => 1,
                'total' => 3
            ],
            'pricing' => [
                'baseAmount' => 450.00,
                'taxes' => 45.00,
                'fees' => 15.00,
                'totalAmount' => 510.00,
                'currency' => 'BRL'
            ],
            'payment' => [
                'method' => 'credit_card',
                'status' => 'paid',
                'transactionId' => 'txn-123'
            ],
            'source' => 'booking.com',
            'specialRequests' => 'Late check-in',
            'createdAt' => '2024-01-01T00:00:00Z',
            'updatedAt' => '2024-01-01T00:00:00Z'
        ];
    }

    private function getMockGuest(): array
    {
        return [
            'firstName' => 'João',
            'lastName' => 'Silva',
            'email' => 'joao.silva@email.com',
            'phone' => '+55 11 99999-9999',
            'document' => [
                'type' => 'cpf',
                'number' => '123.456.789-00'
            ],
            'address' => [
                'street' => 'Rua das Flores, 123',
                'city' => 'São Paulo',
                'state' => 'SP',
                'country' => 'BR',
                'zipCode' => '01234-567'
            ],
            'dateOfBirth' => '1985-05-15',
            'nationality' => 'BR'
        ];
    }

    // Booking Management Tests
    public function testCreateBookingSuccess(): void
    {
        $bookingData = [
            'property_id' => 'prop-123',
            'room_id' => 'room-123',
            'rate_plan_id' => 'rate-123',
            'guest' => $this->getMockGuest(),
            'check_in' => '2025-12-15',
            'check_out' => '2025-12-18',
            'adults' => 2,
            'children' => 1,
            'source' => 'booking.com',
            'special_requests' => 'Late check-in'
        ];

        $mockBooking = $this->getMockBooking();
        $mockResponse = ['data' => $mockBooking];

        $this->mockClient
            ->shouldReceive('post')
            ->once()
            ->with('/api/bookings', $bookingData)
            ->andReturn($mockResponse);

        $result = $this->bookingService->create($bookingData);

        $this->assertInstanceOf(Booking::class, $result);
        $this->assertEquals('booking-123', $result->getId());
    }

    public function testCreateBookingValidationFailure(): void
    {
        $invalidBookingData = [
            'property_id' => '',
            'room_id' => '',
            'rate_plan_id' => '',
            'guest' => [
                'firstName' => '',
                'lastName' => '',
                'email' => 'invalid-email',
                'phone' => 'invalid-phone',
                'document' => [
                    'type' => 'invalid',
                    'number' => ''
                ]
            ],
            'check_in' => '2025-12-18', // Check-in após check-out
            'check_out' => '2025-12-15',
            'adults' => 0,
            'children' => -1
        ];

        $this->expectException(ValidationException::class);

        $this->bookingService->create($invalidBookingData);
    }

    public function testGetBookingSuccess(): void
    {
        $mockBooking = $this->getMockBooking();
        $mockResponse = ['data' => $mockBooking];

        $this->mockClient
            ->shouldReceive('get')
            ->once()
            ->with('/api/bookings/booking-123')
            ->andReturn($mockResponse);

        $result = $this->bookingService->get('booking-123');

        $this->assertInstanceOf(Booking::class, $result);
        $this->assertEquals('booking-123', $result->getId());
    }

    public function testGetBookingNotFound(): void
    {
        $this->mockClient
            ->shouldReceive('get')
            ->once()
            ->with('/api/bookings/non-existent')
            ->andThrow(new NotFoundException('Booking not found'));

        $this->expectException(NotFoundException::class);

        $this->bookingService->get('non-existent');
    }

    public function testListBookingsWithDefaultFilters(): void
    {
        $mockResponse = [
            'data' => [$this->getMockBooking()],
            'total' => 1,
            'page' => 1,
            'limit' => 10
        ];

        $this->mockClient
            ->shouldReceive('get')
            ->once()
            ->with('/api/bookings', [])
            ->andReturn($mockResponse);

        $result = $this->bookingService->list();

        $this->assertEquals($mockResponse, $result);
    }

    public function testListBookingsWithCustomFilters(): void
    {
        $filters = [
            'propertyId' => 'prop-123',
            'status' => 'confirmed',
            'checkInFrom' => '2024-03-01',
            'checkInTo' => '2024-03-31',
            'page' => 2,
            'limit' => 20
        ];

        $mockResponse = [
            'data' => [$this->getMockBooking()],
            'total' => 1,
            'page' => 2,
            'limit' => 20
        ];

        $this->mockClient
            ->shouldReceive('get')
            ->once()
            ->with('/api/bookings', $filters)
            ->andReturn($mockResponse);

        $result = $this->bookingService->list($filters);

        $this->assertEquals($mockResponse, $result);
    }

    public function testUpdateBookingSuccess(): void
    {
        $updateData = [
            'specialRequests' => 'Updated special requests',
            'guest' => [
                'phone' => '+55 11 88888-8888'
            ]
        ];

        $updatedBooking = array_merge($this->getMockBooking(), $updateData);

        $this->mockClient
            ->shouldReceive('put')
            ->once()
            ->with('/api/bookings/booking-123', $updateData)
            ->andReturn($updatedBooking);

        $result = $this->bookingService->update('booking-123', $updateData);

        $this->assertInstanceOf(Booking::class, $result);
    }

    public function testUpdateBookingValidationFailure(): void
    {
        $invalidUpdateData = [
            'guest' => [
                'email' => 'invalid-email',
                'phone' => 'invalid-phone'
            ],
            'dates' => [
                'checkIn' => '2025-12-18', // Check-in após check-out
                'checkOut' => '2025-12-15'
            ]
        ];

        $this->expectException(ValidationException::class);

        $this->bookingService->update('booking-123', $invalidUpdateData);
    }

    public function testCancelBookingSuccess(): void
    {
        $cancelData = [
            'reason' => 'Guest request',
            'refundAmount' => 255.00
        ];

        $cancelledBooking = array_merge($this->getMockBooking(), [
            'status' => 'cancelled',
            'cancellation' => [
                'reason' => 'Customer request',
                'cancelledAt' => '2024-01-01T00:00:00Z'
            ]
        ]);
        $mockResponse = ['data' => $cancelledBooking];

        $this->mockClient
            ->shouldReceive('post')
            ->once()
            ->with('/api/bookings/booking-123/cancel', $cancelData)
            ->andReturn($mockResponse);

        $result = $this->bookingService->cancel('booking-123', $cancelData);

        $this->assertInstanceOf(Booking::class, $result);
        $this->assertEquals('cancelled', $result->getStatus());
    }

    public function testConfirmBookingSuccess(): void
    {
        $confirmData = [
            'confirmationNumber' => 'CONF789012'
        ];

        $confirmedBooking = array_merge($this->getMockBooking(), [
            'status' => 'confirmed',
            'confirmation' => [
                'number' => 'CONF789012',
                'confirmedAt' => '2024-01-01T00:00:00Z'
            ]
        ]);
        $mockResponse = ['data' => $confirmedBooking];

        $this->mockClient
            ->shouldReceive('post')
            ->once()
            ->with('/api/bookings/booking-123/confirm', $confirmData)
            ->andReturn($mockResponse);

        $result = $this->bookingService->confirm('booking-123', $confirmData);

        $this->assertInstanceOf(Booking::class, $result);
        $this->assertEquals('confirmed', $result->getStatus());
    }

    public function testModifyBookingSuccess(): void
    {
        $modificationData = [
            'dates' => [
                'checkIn' => '2025-12-16',
                'checkOut' => '2025-12-19'
            ],
            'occupancy' => [
                'adults' => 3,
                'children' => 0,
                'total' => 3
            ]
        ];

        $modifiedBooking = array_merge($this->getMockBooking(), $modificationData);

        $this->mockClient
            ->shouldReceive('post')
            ->once()
            ->with('/api/bookings/booking-123/modify', $modificationData)
            ->andReturn($modifiedBooking);

        $result = $this->bookingService->modify('booking-123', $modificationData);

        $this->assertInstanceOf(Booking::class, $result);
    }

    public function testModifyBookingValidationFailure(): void
    {
        $invalidModificationData = [
            'dates' => [
                'checkIn' => '2025-12-19', // Check-in após check-out
                'checkOut' => '2025-12-16'
            ],
            'occupancy' => [
                'adults' => 0,
                'children' => -1,
                'total' => 0
            ]
        ];

        $this->expectException(ValidationException::class);

        $this->bookingService->modify('booking-123', $invalidModificationData);
    }

    // Booking History Tests
    public function testGetBookingHistorySuccess(): void
    {
        $mockHistory = [
            [
                'id' => 'hist-1',
                'bookingId' => 'booking-123',
                'action' => 'created',
                'changes' => [],
                'user' => 'system',
                'timestamp' => '2024-01-01T00:00:00Z'
            ],
            [
                'id' => 'hist-2',
                'bookingId' => 'booking-123',
                'action' => 'updated',
                'changes' => [
                    'field' => 'specialRequests',
                    'oldValue' => 'Late check-in',
                    'newValue' => 'Updated special requests'
                ],
                'user' => 'user-123',
                'timestamp' => '2024-01-02T00:00:00Z'
            ]
        ];

        $this->mockClient
            ->shouldReceive('get')
            ->once()
            ->with('/api/bookings/booking-123/history')
            ->andReturn($mockHistory);

        $result = $this->bookingService->getHistory('booking-123');

        $this->assertEquals($mockHistory, $result);
    }

    // Booking Notes Tests
    public function testAddNoteSuccess(): void
    {
        $noteData = [
            'content' => 'Guest requested early check-in',
            'type' => 'internal',
            'author' => 'user-123'
        ];

        $mockNote = [
            'id' => 'note-123',
            'bookingId' => 'booking-123',
            'content' => 'Guest requested early check-in',
            'type' => 'internal',
            'author' => 'user-123',
            'createdAt' => '2024-01-01T00:00:00Z'
        ];

        $this->mockClient
            ->shouldReceive('post')
            ->once()
            ->with('/api/bookings/booking-123/notes', $noteData)
            ->andReturn($mockNote);

        $result = $this->bookingService->addNote('booking-123', $noteData);

        $this->assertEquals($mockNote, $result);
    }

    public function testAddNoteValidationFailure(): void
    {
        $invalidNoteData = [
            'content' => '', // Conteúdo vazio
            'type' => 'invalid-type',
            'author' => ''
        ];

        // Mock the client to expect the API call and throw ValidationException
        $this->mockClient
            ->shouldReceive('post')
            ->with('/api/bookings/booking-123/notes', $invalidNoteData)
            ->once()
            ->andThrow(new ValidationException('Validation failed'));

        $this->expectException(ValidationException::class);

        $this->bookingService->addNote('booking-123', $invalidNoteData);
    }

    public function testGetNotesSuccess(): void
    {
        $mockNotes = [
            [
                'id' => 'note-123',
                'bookingId' => 'booking-123',
                'content' => 'Guest requested early check-in',
                'type' => 'internal',
                'author' => 'user-123',
                'createdAt' => '2024-01-01T00:00:00Z'
            ]
        ];

        $this->mockClient
            ->shouldReceive('get')
            ->once()
            ->with('/api/bookings/booking-123/notes')
            ->andReturn($mockNotes);

        $result = $this->bookingService->getNotes('booking-123');

        $this->assertEquals($mockNotes, $result);
    }

    public function testUpdateNoteSuccess(): void
    {
        $updateData = [
            'content' => 'Updated note content'
        ];

        $updatedNote = [
            'id' => 'note-123',
            'bookingId' => 'booking-123',
            'content' => 'Updated note content',
            'type' => 'internal',
            'author' => 'user-123',
            'createdAt' => '2024-01-01T00:00:00Z',
            'updatedAt' => '2024-01-02T00:00:00Z'
        ];

        $this->mockClient
            ->shouldReceive('put')
            ->once()
            ->with('/api/bookings/booking-123/notes/note-123', $updateData)
            ->andReturn($updatedNote);

        $result = $this->bookingService->updateNote('booking-123', 'note-123', $updateData);

        $this->assertEquals($updatedNote, $result);
    }

    public function testDeleteNoteSuccess(): void
    {
        $this->mockClient
            ->shouldReceive('delete')
            ->once()
            ->with('/api/bookings/booking-123/notes/note-123')
            ->andReturn(true);

        $result = $this->bookingService->deleteNote('booking-123', 'note-123');

        $this->assertTrue($result);
    }

    // Payment Management Tests
    public function testProcessPaymentSuccess(): void
    {
        $paymentData = [
            'method' => 'credit_card',
            'amount' => 510.00,
            'currency' => 'BRL',
            'cardToken' => 'card-token-123',
            'installments' => 1
        ];

        $mockPayment = [
            'id' => 'payment-123',
            'bookingId' => 'booking-123',
            'method' => 'credit_card',
            'amount' => 510.00,
            'currency' => 'BRL',
            'status' => 'completed',
            'transactionId' => 'txn-456',
            'processedAt' => '2024-01-01T00:00:00Z'
        ];

        $this->mockClient
            ->shouldReceive('post')
            ->once()
            ->with('/api/bookings/booking-123/payments', $paymentData)
            ->andReturn($mockPayment);

        $result = $this->bookingService->processPayment('booking-123', $paymentData);

        $this->assertEquals($mockPayment, $result);
    }

    public function testProcessPaymentValidationFailure(): void
    {
        $invalidPaymentData = [
            'method' => 'invalid-method',
            'amount' => -100, // Valor negativo
            'currency' => 'INVALID',
            'cardToken' => '',
            'installments' => 0
        ];

        // Mock the client to expect the API call and throw ValidationException
        $this->mockClient
            ->shouldReceive('post')
            ->with('/api/bookings/booking-123/payments', $invalidPaymentData)
            ->once()
            ->andThrow(new ValidationException('Validation failed'));

        $this->expectException(ValidationException::class);

        $this->bookingService->processPayment('booking-123', $invalidPaymentData);
    }

    public function testGetPaymentsSuccess(): void
    {
        $mockPayments = [
            [
                'id' => 'payment-123',
                'bookingId' => 'booking-123',
                'method' => 'credit_card',
                'amount' => 510.00,
                'currency' => 'BRL',
                'status' => 'completed',
                'transactionId' => 'txn-456',
                'processedAt' => '2024-01-01T00:00:00Z'
            ]
        ];

        $this->mockClient
            ->shouldReceive('get')
            ->once()
            ->with('/api/bookings/booking-123/payments')
            ->andReturn($mockPayments);

        $result = $this->bookingService->getPayments('booking-123');

        $this->assertEquals($mockPayments, $result);
    }

    public function testRefundPaymentSuccess(): void
    {
        $refundData = [
            'amount' => 255.00,
            'reason' => 'Guest cancellation'
        ];

        $mockRefund = [
            'id' => 'refund-123',
            'paymentId' => 'payment-123',
            'bookingId' => 'booking-123',
            'amount' => 255.00,
            'reason' => 'Guest cancellation',
            'status' => 'completed',
            'processedAt' => '2024-01-02T00:00:00Z'
        ];

        $this->mockClient
            ->shouldReceive('post')
            ->once()
            ->with('/api/bookings/booking-123/payments/payment-123/refund', $refundData)
            ->andReturn($mockRefund);

        $result = $this->bookingService->refundPayment('booking-123', 'payment-123', $refundData);

        $this->assertEquals($mockRefund, $result);
    }

    // PMS Integration Tests
    public function testSyncBookingWithPmsSuccess(): void
    {
        $syncData = [
            'direction' => 'push',
            'entities' => ['booking', 'guest', 'payment']
        ];

        // The actual data sent will have the default entities merged
        $expectedSyncData = [
            'direction' => 'push',
            'entities' => ['booking', 'guest', 'payment']
        ];

        $mockResponse = [
            'success' => true,
            'syncId' => 'sync-456',
            'status' => 'completed',
            'syncedAt' => '2024-01-01T00:00:00Z'
        ];

        $this->mockClient
            ->shouldReceive('post')
            ->once()
            ->with('/api/bookings/booking-123/sync', $expectedSyncData)
            ->andReturn($mockResponse);

        $result = $this->bookingService->syncWithPms('booking-123', $syncData);

        $this->assertEquals($mockResponse, $result);
    }

    // Validation Methods Tests
    public function testValidateBookingDataSuccess(): void
    {
        $validBookingData = [
            'property_id' => 'prop-123',
            'room_id' => 'room-123',
            'rate_plan_id' => 'rate-123',
            'guest' => $this->getMockGuest(),
            'check_in' => '2025-12-15',
            'check_out' => '2025-12-18',
            'adults' => 2,
            'children' => 1
        ];

        // Este método não deve lançar exceção para dados válidos
        // Use reflection to call the protected method
        $reflection = new \ReflectionClass($this->bookingService);
        $method = $reflection->getMethod('validateBookingData');
        $method->setAccessible(true);
        $method->invoke($this->bookingService, $validBookingData);
        
        // Se chegou até aqui, a validação passou
        $this->assertTrue(true);
    }

    public function testValidateBookingDataFailure(): void
    {
        $invalidBookingData = [
            'property_id' => '',
            'room_id' => '',
            'guest' => [
                'firstName' => '',
                'email' => 'invalid-email'
            ],
            'check_in' => '2025-12-18',
            'check_out' => '2025-12-15', // Check-out antes do check-in
            'adults' => 0
        ];

        $this->expectException(ValidationException::class);

        // Use reflection to call the protected method
        $reflection = new \ReflectionClass($this->bookingService);
        $method = $reflection->getMethod('validateBookingData');
        $method->setAccessible(true);
        $method->invoke($this->bookingService, $invalidBookingData);
    }

    public function testValidateGuestDataSuccess(): void
    {
        $validGuestData = $this->getMockGuest();
        $errors = [];

        // Este método não deve lançar exceção para dados válidos
        // Use reflection to call the protected method
        $reflection = new \ReflectionClass($this->bookingService);
        $method = $reflection->getMethod('validateGuestData');
        $method->setAccessible(true);
        $method->invokeArgs($this->bookingService, [$validGuestData, &$errors]);
        
        // Se chegou até aqui, a validação passou
        $this->assertEmpty($errors);
    }

    public function testValidateGuestDataFailure(): void
    {
        $invalidGuestData = [
            'first_name' => '', // Nome vazio
            'last_name' => '', // Sobrenome vazio
            'email' => 'invalid-email-format', // Email inválido
            'phone' => 'invalid-phone-123!@#',
            'document' => [
                'type' => 'invalid',
                'number' => ''
            ]
        ];

        $errors = [];
        // Use reflection to call the protected method
        $reflection = new \ReflectionClass($this->bookingService);
        $method = $reflection->getMethod('validateGuestData');
        $method->setAccessible(true);
        $method->invokeArgs($this->bookingService, [$invalidGuestData, &$errors]);
        
        $this->assertNotEmpty($errors);
    }
}