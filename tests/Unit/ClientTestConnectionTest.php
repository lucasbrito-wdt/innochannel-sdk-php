<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Innochannel\Sdk\Client;
use Innochannel\Sdk\Services\PropertyService;
use Mockery;

class ClientTestConnectionTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testTestConnectionMethodExists()
    {
        $config = [
            'api_key' => 'test_key',
            'api_secret' => 'test_secret',
            'base_url' => 'https://api.innochannel.com'
        ];

        $client = new Client($config);
        
        $this->assertTrue(method_exists($client, 'testConnection'));
    }

    public function testTestConnectionCallsPropertyService()
    {
        $config = [
            'api_key' => 'test_key',
            'api_secret' => 'test_secret',
            'base_url' => 'https://api.innochannel.com'
        ];

        // Mock do PropertyService
        $propertyServiceMock = Mockery::mock(PropertyService::class);
        $propertyServiceMock->shouldReceive('testPmsConnection')
            ->once()
            ->with(['test' => 'data'])
            ->andReturn(['status' => 'success', 'message' => 'Connection successful']);

        // Mock do Client para injetar o PropertyService mockado
        $clientMock = Mockery::mock(Client::class, [$config])->makePartial();
        $clientMock->shouldReceive('properties')
            ->once()
            ->andReturn($propertyServiceMock);

        $result = $clientMock->testConnection(['test' => 'data']);

        $this->assertEquals(['status' => 'success', 'message' => 'Connection successful'], $result);
    }

    public function testTestConnectionWithoutData()
    {
        $config = [
            'api_key' => 'test_key',
            'api_secret' => 'test_secret',
            'base_url' => 'https://api.innochannel.com'
        ];

        // Mock do PropertyService
        $propertyServiceMock = Mockery::mock(PropertyService::class);
        $propertyServiceMock->shouldReceive('testPmsConnection')
            ->once()
            ->with([])
            ->andReturn(['status' => 'success', 'message' => 'Connection test completed']);

        // Mock do Client para injetar o PropertyService mockado
        $clientMock = Mockery::mock(Client::class, [$config])->makePartial();
        $clientMock->shouldReceive('properties')
            ->once()
            ->andReturn($propertyServiceMock);

        $result = $clientMock->testConnection();

        $this->assertEquals(['status' => 'success', 'message' => 'Connection test completed'], $result);
    }
}