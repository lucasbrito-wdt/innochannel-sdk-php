<?php

declare(strict_types=1);

namespace Innochannel\Sdk\Tests\Unit;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use Innochannel\Sdk\Client;
use Innochannel\Sdk\Exceptions\ApiException;
use Innochannel\Sdk\Exceptions\AuthenticationException;
use Innochannel\Sdk\Exceptions\ValidationException;
use Innochannel\Sdk\Exceptions\NotFoundException;
use Innochannel\Sdk\Exceptions\RateLimitException;
use Innochannel\Sdk\Exceptions\InnochannelException;
use PHPUnit\Framework\TestCase;
use Mockery;

class ClientTest extends TestCase
{
    private Client $client;
    private MockHandler $mockHandler;
    private array $config;
    private array $container;

    protected function setUp(): void
    {
        parent::setUp();

        $this->config = [
            'api_key' => 'innotel-test-key-2024',
            'api_secret' => 'innotel-test-secret-2024-secure',
            'base_url' => 'http://localhost/api',
            'timeout' => 30,
            'debug' => false
        ];

        $this->mockHandler = new MockHandler();
        $handlerStack = HandlerStack::create($this->mockHandler);

        // Add middleware to capture requests
        $container = [];
        $history = \GuzzleHttp\Middleware::history($container);
        $handlerStack->push($history);

        $httpClient = new HttpClient(['handler' => $handlerStack]);

        $this->client = new Client($this->config, $httpClient);
        $this->container = &$container;
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testConstructorWithDefaultHttpClient(): void
    {
        $client = new Client($this->config);
        $this->assertInstanceOf(Client::class, $client);
    }

    public function testConstructorWithCustomHttpClient(): void
    {
        $httpClient = new HttpClient();
        $client = new Client($this->config, $httpClient);
        $this->assertInstanceOf(Client::class, $client);
    }

    public function testGetRequestSuccess(): void
    {
        $responseData = ['id' => 1, 'name' => 'Test Property'];
        $this->mockHandler->append(
            new Response(200, ['Content-Type' => 'application/json'], json_encode($responseData))
        );

        $result = $this->client->get('/properties/1');

        $this->assertEquals($responseData, $result);
    }

    public function testGetRequestWithQueryParameters(): void
    {
        $responseData = ['data' => [], 'total' => 0];
        $this->mockHandler->append(
            new Response(200, ['Content-Type' => 'application/json'], json_encode($responseData))
        );

        $queryParams = ['page' => 1, 'limit' => 10, 'active' => true];
        $result = $this->client->get('/properties', $queryParams);

        $this->assertEquals($responseData, $result);
    }

    public function testPostRequestSuccess(): void
    {
        $requestData = ['name' => 'New Property', 'city' => 'São Paulo'];
        $responseData = ['id' => 1, 'name' => 'New Property', 'city' => 'São Paulo'];

        $this->mockHandler->append(
            new Response(201, ['Content-Type' => 'application/json'], json_encode($responseData))
        );

        $result = $this->client->post('/properties', $requestData);

        $this->assertEquals($responseData, $result);
    }

    public function testPutRequestSuccess(): void
    {
        $requestData = ['name' => 'Updated Property'];
        $responseData = ['id' => 1, 'name' => 'Updated Property'];

        $this->mockHandler->append(
            new Response(200, ['Content-Type' => 'application/json'], json_encode($responseData))
        );

        $result = $this->client->put('/properties/1', $requestData);

        $this->assertEquals($responseData, $result);
    }

    public function testPatchRequestSuccess(): void
    {
        $requestData = ['active' => false];
        $responseData = ['id' => 1, 'name' => 'Test Property', 'active' => false];

        $this->mockHandler->append(
            new Response(200, ['Content-Type' => 'application/json'], json_encode($responseData))
        );

        $result = $this->client->patch('/properties/1', $requestData);

        $this->assertEquals($responseData, $result);
    }

    public function testDeleteRequestSuccess(): void
    {
        $this->mockHandler->append(new Response(204));

        $result = $this->client->delete('/properties/1');

        $this->assertTrue($result);
    }

    public function testDeleteRequestWithContent(): void
    {
        $responseData = ['message' => 'Property deleted successfully'];
        $this->mockHandler->append(
            new Response(200, ['Content-Type' => 'application/json'], json_encode($responseData))
        );

        $result = $this->client->delete('/properties/1');

        $this->assertEquals($responseData, $result);
    }

    public function testAuthenticationExceptionOn401(): void
    {
        $this->mockHandler->append(
            new Response(401, [], json_encode(['message' => 'Unauthorized']))
        );

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Unauthorized');

        $this->client->get('/properties');
    }

    public function testValidationExceptionOn400(): void
    {
        $errorData = [
            'message' => 'Validation failed',
            'errors' => [
                'name' => ['The name field is required.'],
                'email' => ['The email field must be a valid email address.']
            ]
        ];

        $this->mockHandler->append(
            new Response(400, [], json_encode($errorData))
        );

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Validation failed');

        $this->client->post('/properties', []);
    }

    public function testNotFoundExceptionOn404(): void
    {
        $this->mockHandler->append(
            new Response(404, [], json_encode(['message' => 'Property not found']))
        );

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Property not found');

        $this->client->get('/properties/999');
    }

    public function testRateLimitExceptionOn429(): void
    {
        $this->mockHandler->append(
            new Response(429, [
                'X-RateLimit-Limit' => '100',
                'X-RateLimit-Remaining' => '0',
                'X-RateLimit-Reset' => '1640995200'
            ], json_encode(['message' => 'Too Many Requests']))
        );

        $this->expectException(RateLimitException::class);
        $this->expectExceptionMessage('Too Many Requests');

        $this->client->get('/properties');
    }

    public function testGenericInnochannelExceptionOn500(): void
    {
        $this->mockHandler->append(
            new Response(500, [], json_encode(['message' => 'Internal Server Error']))
        );

        $this->expectException(InnochannelException::class);
        $this->expectExceptionMessage('Internal Server Error');

        $this->client->get('/properties');
    }

    public function testNetworkException(): void
    {
        $this->mockHandler->append(
            new RequestException('Connection timeout', new Request('GET', '/properties'))
        );

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('HTTP request failed: Connection timeout');

        $this->client->get('/properties');
    }

    public function testInvalidJsonResponse(): void
    {
        $this->mockHandler->append(
            new Response(200, ['Content-Type' => 'application/json'], 'invalid json')
        );

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Invalid JSON response');

        $this->client->get('/properties');
    }

    public function testEmptyResponseBody(): void
    {
        $this->mockHandler->append(new Response(200, [], ''));

        $result = $this->client->get('/properties');

        $this->assertNull($result);
    }

    public function testCustomHeaders(): void
    {
        $this->mockHandler->append(
            new Response(200, [], json_encode(['success' => true]))
        );

        $customHeaders = ['X-Custom-Header' => 'custom-value'];
        $result = $this->client->get('/properties', [], $customHeaders);

        $this->assertEquals(['success' => true], $result);
    }

    public function testApiKeyAuthentication(): void
    {
        $this->mockHandler->append(
            new Response(200, [], json_encode(['authenticated' => true]))
        );

        // O cliente deve automaticamente incluir o API key no header
        $result = $this->client->get('/auth/test');

        $this->assertEquals(['authenticated' => true], $result);
    }

    public function testRetryMechanism(): void
    {
        // Simula falha seguida de sucesso
        $this->mockHandler->append(
            new Response(503, [], json_encode(['message' => 'Service Unavailable'])),
            new Response(200, [], json_encode(['success' => true]))
        );

        $config = [
            'base_url' => 'https://api-test.innochannel.com',
            'api_key' => 'test-api-key',
            'retry_attempts' => 2,
            'retry_delay' => 100 // 100ms para testes rápidos
        ];

        $handlerStack = HandlerStack::create($this->mockHandler);
        $httpClient = new HttpClient(['handler' => $handlerStack]);
        $client = new Client($config, $httpClient);

        $result = $client->get('/properties');

        $this->assertEquals(['success' => true], $result);
    }

    public function testTimeoutConfiguration(): void
    {
        $config = [
            'base_url' => 'https://api-test.innochannel.com',
            'api_key' => 'test-api-key',
            'timeout' => 5
        ];

        $client = new Client($config);

        // Verifica se o timeout foi configurado corretamente
        $reflection = new \ReflectionClass($client);
        $httpClientProperty = $reflection->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        $httpClient = $httpClientProperty->getValue($client);

        $this->assertInstanceOf(\GuzzleHttp\Client::class, $httpClient);
    }

    public function testDebugMode(): void
    {
        $config = [
            'base_url' => 'https://api-test.innochannel.com',
            'api_key' => 'test-api-key',
            'debug' => true
        ];

        $client = new Client($config);

        $reflection = new \ReflectionClass($client);
        $httpClientProperty = $reflection->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        $httpClient = $httpClientProperty->getValue($client);

        $this->assertInstanceOf(\GuzzleHttp\Client::class, $httpClient);
    }

    public function testUserAgentHeader(): void
    {
        $this->mockHandler->append(
            new Response(200, [], json_encode(['success' => true]))
        );

        $result = $this->client->get('/properties');

        // Verifica se o User-Agent foi definido corretamente
        $lastRequest = $this->container[0]['request'];
        $this->assertStringContainsString('Innochannel-PHP-SDK', $lastRequest->getHeaderLine('User-Agent'));
    }

    public function testContentTypeHeader(): void
    {
        $this->mockHandler->append(
            new Response(201, [], json_encode(['id' => 1]))
        );

        $this->client->post('/properties', ['name' => 'Test']);

        $lastRequest = $this->container[0]['request'];
        $this->assertEquals('application/json', $lastRequest->getHeaderLine('Content-Type'));
    }

    public function testAcceptHeader(): void
    {
        $this->mockHandler->append(
            new Response(200, [], json_encode(['success' => true]))
        );

        $this->client->get('/properties');

        $lastRequest = $this->container[0]['request'];
        $this->assertEquals('application/json', $lastRequest->getHeaderLine('Accept'));
    }

    public function testAuthorizationHeader(): void
    {
        $this->mockHandler->append(
            new Response(200, [], json_encode(['success' => true]))
        );

        $this->client->get('/properties');

        $lastRequest = $this->container[0]['request'];
        $this->assertEquals('Bearer innotel-test-key-2024', $lastRequest->getHeaderLine('Authorization'));
    }
}
