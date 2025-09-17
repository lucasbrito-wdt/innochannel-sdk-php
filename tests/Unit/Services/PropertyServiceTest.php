<?php

declare(strict_types=1);

namespace Innochannel\Sdk\Tests\Unit\Services;

use Innochannel\Sdk\Client;
use Innochannel\Sdk\Services\PropertyService;
use Innochannel\Sdk\Models\Property;
use Innochannel\Sdk\Models\Room;
use Innochannel\Sdk\Models\RatePlan;
use Innochannel\Sdk\Exceptions\ValidationException;
use Innochannel\Sdk\Exceptions\NotFoundException;
use PHPUnit\Framework\TestCase;
use Mockery;

class PropertyServiceTest extends TestCase
{
    private Client $mockClient;
    private PropertyService $propertyService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->mockClient = Mockery::mock(Client::class);
        $this->propertyService = new PropertyService($this->mockClient);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function getMockProperty(): array
    {
        return [
            'id' => 123,
            'name' => 'Test Hotel',
            'description' => 'A test hotel',
            'pms_type' => 'test_pms',
            'pms_credentials' => [],
            'address' => 'Test Street, 123',
            'city' => 'Test City',
            'state' => 'TS',
            'country' => 'BR',
            'postal_code' => '12345-678',
            'phone' => '+55 11 1234-5678',
            'email' => 'test@hotel.com',
            'website' => 'https://testhotel.com',
            'amenities' => ['wifi', 'parking'],
            'policies' => [],
            'is_active' => true,
            'created_at' => '2024-01-01T00:00:00Z',
            'updated_at' => '2024-01-01T00:00:00Z'
        ];
    }

    private function getMockRoom(): array
    {
        return [
            'id' => 'room-123',
            'property_id' => 'prop-123',
            'name' => 'Standard Room',
            'description' => 'A comfortable standard room',
            'room_type' => 'standard',
            'max_occupancy' => 3,
            'max_adults' => 2,
            'max_children' => 1,
            'size' => 25.0,
            'size_unit' => 'sqm',
            'amenities' => ['air_conditioning', 'tv'],
            'bed_types' => [
                ['type' => 'queen', 'quantity' => 1]
            ],
            'view_type' => 'garden',
            'is_active' => true,
            'created_at' => '2024-01-01T00:00:00Z',
            'updated_at' => '2024-01-01T00:00:00Z'
        ];
    }

    private function getMockRatePlan(): array
    {
        return [
            'id' => 'rate-123',
            'property_id' => 'prop-123',
            'room_id' => 'room-123',
            'name' => 'Standard Rate',
            'description' => 'Standard rate plan',
            'base_rate' => 150.00,
            'currency' => 'BRL',
            'rate_type' => 'per_night',
            'inclusions' => ['breakfast'],
            'restrictions' => [
                'min_stay' => 1,
                'max_stay' => 30,
                'min_advance_reservation' => 0,
                'max_advance_reservation' => 365
            ],
            'cancellation_policy' => [
                'type' => 'flexible',
                'deadline_hours' => 24,
                'penalty_type' => 'percentage',
                'penalty_value' => 0
            ],
            'is_active' => true,
            'created_at' => '2024-01-01T00:00:00Z',
            'updated_at' => '2024-01-01T00:00:00Z'
        ];
    }

    // Property Management Tests
    public function testCreatePropertySuccess(): void
    {
        $propertyData = [
            'name' => 'Test Hotel',
            'description' => 'A test hotel',
            'pms_type' => 'test_pms',
            'pms_credentials' => ['host' => 'test.com', 'username' => 'test'],
            'address' => 'Test Street, 123',
            'city' => 'Test City',
            'state' => 'TS',
            'country' => 'BR',
            'postal_code' => '12345-678',
            'phone' => '+55 11 1234-5678',
            'email' => 'test@hotel.com',
            'website' => 'https://testhotel.com',
            'amenities' => ['wifi', 'parking'],
            'policies' => []
        ];

        $mockProperty = $this->getMockProperty();
        $mockResponse = ['data' => $mockProperty];

        $this->mockClient
            ->shouldReceive('post')
            ->once()
            ->with('/api/pms/properties', $propertyData)
            ->andReturn($mockResponse);

        $result = $this->propertyService->create($propertyData);

        $this->assertInstanceOf(Property::class, $result);
        $this->assertEquals(123, $result->getId());
    }

    public function testCreatePropertyValidationFailure(): void
    {
        $invalidData = [
            'name' => '', // Nome vazio
            'description' => 'Test',
            'address' => [
                'street' => '',
                'city' => 'Test City',
                'state' => 'TS',
                'country' => 'BR',
                'zipCode' => '12345-678'
            ],
            'contact' => [
                'phone' => 'invalid-phone',
                'email' => 'invalid-email',
                'website' => 'invalid-url'
            ]
        ];

        $this->expectException(ValidationException::class);

        $this->propertyService->create($invalidData);
    }

    public function testGetPropertySuccess(): void
    {
        $mockProperty = $this->getMockProperty();
        $mockResponse = ['data' => $mockProperty];

        $this->mockClient
            ->shouldReceive('get')
            ->once()
            ->with('/api/pms/properties/property-123')
            ->andReturn($mockResponse);

        $result = $this->propertyService->get('property-123');

        $this->assertInstanceOf(Property::class, $result);
        $this->assertEquals(123, $result->getId());
    }

    public function testGetPropertyNotFound(): void
    {
        $this->mockClient
            ->shouldReceive('get')
            ->once()
            ->with('/api/pms/properties/non-existent')
            ->andThrow(new NotFoundException('Property not found'));

        $this->expectException(NotFoundException::class);

        $this->propertyService->get('non-existent');
    }

    public function testListPropertiesWithDefaultFilters(): void
    {
        $mockResponse = [
            'data' => [$this->getMockProperty()],
            'total' => 1,
            'page' => 1,
            'limit' => 10
        ];

        $this->mockClient
            ->shouldReceive('get')
            ->once()
            ->with('/api/pms/properties', [])
            ->andReturn($mockResponse);

        $result = $this->propertyService->list();

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertInstanceOf(Property::class, $result[0]);
        $this->assertEquals(123, $result[0]->getId());
    }

    public function testListPropertiesWithCustomFilters(): void
    {
        $filters = [
            'active' => true,
            'city' => 'São Paulo',
            'page' => 2,
            'limit' => 20
        ];

        $mockResponse = [
            'data' => [$this->getMockProperty()],
            'total' => 1,
            'page' => 2,
            'limit' => 20
        ];

        $this->mockClient
            ->shouldReceive('get')
            ->once()
            ->with('/api/pms/properties', $filters)
            ->andReturn($mockResponse);

        $result = $this->propertyService->list($filters);

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertInstanceOf(Property::class, $result[0]);
        $this->assertEquals(123, $result[0]->getId());
    }

    public function testUpdatePropertySuccess(): void
    {
        $updateData = [
            'name' => 'Updated Hotel Name',
            'description' => 'Updated description'
        ];

        $updatedProperty = array_merge($this->getMockProperty(), $updateData);
        $mockResponse = ['data' => $updatedProperty];

        $this->mockClient
            ->shouldReceive('put')
            ->once()
            ->with('/api/pms/properties/prop-123', $updateData)
            ->andReturn($mockResponse);

        $result = $this->propertyService->update('prop-123', $updateData);

        $this->assertInstanceOf(Property::class, $result);
        $this->assertEquals(123, $result->getId());
    }

    public function testUpdatePropertyValidationFailure(): void
    {
        $invalidUpdateData = [
            'name' => '', // Nome vazio
            'contact' => [
                'email' => 'invalid-email'
            ]
        ];

        $this->expectException(ValidationException::class);

        $this->propertyService->update('prop-123', $invalidUpdateData);
    }

    public function testDeletePropertySuccess(): void
    {
        $this->mockClient
            ->shouldReceive('delete')
            ->once()
            ->with('/api/pms/properties/prop-123')
            ->andReturn(true);

        $result = $this->propertyService->delete('prop-123');

        $this->assertTrue($result);
    }

    // Room Management Tests
    public function testCreateRoomSuccess(): void
    {
        $roomData = [
            'name' => 'Standard Room',
            'description' => 'A comfortable standard room',
            'room_type' => 'standard',
            'max_occupancy' => 3,
            'max_adults' => 2,
            'max_children' => 1,
            'size' => 25,
            'amenities' => ['air_conditioning', 'tv'],
            'bed_types' => [
                ['type' => 'queen', 'quantity' => 1]
            ]
        ];

        $mockRoom = $this->getMockRoom();
        $mockResponse = ['data' => $mockRoom];

        $this->mockClient
            ->shouldReceive('post')
            ->once()
            ->with('/api/pms/properties/prop-123/rooms', $roomData)
            ->andReturn($mockResponse);

        $result = $this->propertyService->createRoom('prop-123', $roomData);

        $this->assertInstanceOf(Room::class, $result);
        $this->assertEquals('room-123', $result->getId());
    }

    public function testCreateRoomValidationFailure(): void
    {
        $invalidRoomData = [
            'name' => '', // Nome vazio
            'description' => 'Test',
            'type' => 'standard',
            'capacity' => [
                'adults' => 0, // Capacidade inválida
                'children' => 0,
                'total' => 0
            ],
            'beds' => [],
            'size' => -1, // Tamanho inválido
            'amenities' => [],
            'images' => []
        ];

        $this->expectException(ValidationException::class);

        $this->propertyService->createRoom('prop-123', $invalidRoomData);
    }

    public function testListRoomsSuccess(): void
    {
        $mockResponse = [
            'data' => [$this->getMockRoom()],
            'total' => 1
        ];

        $this->mockClient
            ->shouldReceive('get')
            ->once()
            ->with('/api/pms/properties/prop-123/rooms', [])
            ->andReturn($mockResponse);

        $result = $this->propertyService->listRooms('prop-123');

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertInstanceOf(Room::class, $result[0]);
        $this->assertEquals('room-123', $result[0]->getId());
    }

    public function testListRoomsWithFilters(): void
    {
        $filters = ['active' => true, 'type' => 'suite'];
        $mockResponse = [
            'data' => [$this->getMockRoom()],
            'total' => 1
        ];

        $this->mockClient
            ->shouldReceive('get')
            ->once()
            ->with('/api/pms/properties/prop-123/rooms', $filters)
            ->andReturn($mockResponse);

        $result = $this->propertyService->listRooms('prop-123', $filters);

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertInstanceOf(Room::class, $result[0]);
        $this->assertEquals('room-123', $result[0]->getId());
    }

    public function testGetRoomSuccess(): void
    {
        $mockRoom = $this->getMockRoom();
        $mockResponse = ['data' => $mockRoom];

        $this->mockClient
            ->shouldReceive('get')
            ->once()
            ->with('/api/pms/properties/prop-123/rooms/room-123')
            ->andReturn($mockResponse);

        $result = $this->propertyService->getRoom('prop-123', 'room-123');

        $this->assertInstanceOf(Room::class, $result);
        $this->assertEquals('room-123', $result->getId());
    }

    public function testUpdateRoomSuccess(): void
    {
        $updateData = [
            'name' => 'Updated Room Name',
            'size' => 30
        ];

        $updatedRoom = array_merge($this->getMockRoom(), $updateData);
        $mockResponse = ['data' => $updatedRoom];

        $this->mockClient
            ->shouldReceive('put')
            ->once()
            ->with('/api/pms/properties/prop-123/rooms/room-123', $updateData)
            ->andReturn($mockResponse);

        $result = $this->propertyService->updateRoom('prop-123', 'room-123', $updateData);

        $this->assertInstanceOf(Room::class, $result);
        $this->assertEquals('room-123', $result->getId());
    }

    public function testDeleteRoomSuccess(): void
    {
        $this->mockClient
            ->shouldReceive('delete')
            ->once()
            ->with('/api/pms/properties/prop-123/rooms/room-123')
            ->andReturn(true);

        $result = $this->propertyService->deleteRoom('prop-123', 'room-123');

        $this->assertTrue($result);
    }

    // Rate Plan Management Tests
    public function testCreateRatePlanSuccess(): void
    {
        $ratePlanData = [
            'roomId' => 'room-123',
            'name' => 'Standard Rate',
            'description' => 'Standard rate plan',
            'baseRate' => 150.00,
            'currency' => 'BRL',
            'inclusions' => ['breakfast'],
            'restrictions' => [
                'minStay' => 1,
                'maxStay' => 30,
                'minAdvanceReservation' => 0,
                'maxAdvanceReservation' => 365
            ],
            'cancellationPolicy' => [
                'type' => 'flexible',
                'deadlineHours' => 24,
                'penaltyType' => 'percentage',
                'penaltyValue' => 0
            ]
        ];

        $mockRatePlan = $this->getMockRatePlan();
        $mockResponse = ['data' => $mockRatePlan];

        $this->mockClient
            ->shouldReceive('post')
            ->once()
            ->with('/api/pms/properties/prop-123/rate-plans', $ratePlanData)
            ->andReturn($mockResponse);

        $result = $this->propertyService->createRatePlan('prop-123', $ratePlanData);

        $this->assertInstanceOf(RatePlan::class, $result);
        $this->assertEquals('rate-123', $result->getId());
        $this->assertEquals('Standard Rate', $result->getName());
    }

    public function testCreateRatePlanValidationFailure(): void
    {
        $invalidRatePlanData = [
            'roomId' => '',
            'name' => '', // Empty name should trigger validation
            'description' => 'Test',
            'baseRate' => -10, // Preço negativo
            'currency' => '', // Empty currency should trigger validation
            'inclusions' => [],
            'restrictions' => [
                'minStay' => 0, // Estadia mínima inválida
                'maxStay' => -1,
                'minAdvanceReservation' => -1,
                'maxAdvanceReservation' => -1
            ],
            'cancellationPolicy' => [
                'type' => 'invalid',
                'deadlineHours' => -1,
                'penaltyType' => 'invalid',
                'penaltyValue' => -1
            ]
        ];

        $this->expectException(ValidationException::class);

        $this->propertyService->createRatePlan('prop-123', $invalidRatePlanData);
    }

    public function testListRatePlansSuccess(): void
    {
        $mockResponse = [
            'data' => [$this->getMockRatePlan()],
            'total' => 1
        ];

        $this->mockClient
            ->shouldReceive('get')
            ->once()
            ->with('/api/pms/properties/prop-123/rate-plans', [])
            ->andReturn($mockResponse);

        $result = $this->propertyService->listRatePlans('prop-123');

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertInstanceOf(RatePlan::class, $result[0]);
        $this->assertEquals('rate-123', $result[0]->getId());
    }

    public function testGetRatePlanSuccess(): void
    {
        $mockRatePlan = $this->getMockRatePlan();
        $mockResponse = ['data' => $mockRatePlan];

        $this->mockClient
            ->shouldReceive('get')
            ->once()
            ->with('/api/pms/properties/prop-123/rate-plans/rate-123')
            ->andReturn($mockResponse);

        $result = $this->propertyService->getRatePlan('prop-123', 'rate-123');

        $this->assertInstanceOf(RatePlan::class, $result);
        $this->assertEquals('rate-123', $result->getId());
        $this->assertEquals('prop-123', $result->getPropertyId());
    }

    public function testUpdateRatePlanSuccess(): void
    {
        $updateData = [
            'name' => 'Updated Rate Plan',
            'currency' => 'USD',
            'base_rate' => 200.00
        ];

        $updatedRatePlan = array_merge($this->getMockRatePlan(), $updateData);
        $mockResponse = ['data' => $updatedRatePlan];

        $this->mockClient
            ->shouldReceive('put')
            ->once()
            ->with('/api/pms/properties/prop-123/rate-plans/rate-123', $updateData)
            ->andReturn($mockResponse);

        $result = $this->propertyService->updateRatePlan('prop-123', 'rate-123', $updateData);

        $this->assertInstanceOf(RatePlan::class, $result);
        $this->assertEquals('Updated Rate Plan', $result->getName());
        $this->assertEquals('USD', $result->getCurrency());
    }

    public function testDeleteRatePlanSuccess(): void
    {
        $this->mockClient
            ->shouldReceive('delete')
            ->once()
            ->with('/api/pms/properties/prop-123/rate-plans/rate-123')
            ->andReturn(true);

        $result = $this->propertyService->deleteRatePlan('prop-123', 'rate-123');

        $this->assertTrue($result);
    }

    // PMS Integration Tests
    public function testTestPmsConnectionSuccess(): void
    {
        $connectionData = [
            'host' => 'pms.example.com',
            'username' => 'test_user',
            'password' => 'test_pass',
            'port' => 443
        ];

        $mockResponse = [
            'status' => 'connected',
            'latency' => 150,
            'version' => '1.0.0'
        ];

        $this->mockClient
            ->shouldReceive('post')
            ->once()
            ->with('/api/pms/test-connection', $connectionData)
            ->andReturn($mockResponse);

        $result = $this->propertyService->testPmsConnection($connectionData);

        $this->assertEquals($mockResponse, $result);
    }

    public function testSyncWithPmsSuccess(): void
    {
        $syncOptions = [
            'direction' => 'bidirectional',
            'entities' => ['rooms', 'rates', 'inventory']
        ];

        $mockResponse = [
            'success' => true,
            'syncId' => 'sync-123',
            'status' => 'completed'
        ];

        $this->mockClient
            ->shouldReceive('post')
            ->once()
            ->with('/api/pms/properties/prop-123/sync', $syncOptions)
            ->andReturn($mockResponse);

        $result = $this->propertyService->syncWithPms('prop-123', $syncOptions);

        $this->assertEquals($mockResponse, $result);
    }
}