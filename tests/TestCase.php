<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * Base TestCase class for Innochannel SDK tests
 * 
 * This class provides common functionality and setup for all SDK tests.
 * It extends PHPUnit's TestCase and can be extended by specific test classes.
 * 
 * @package Tests
 * @author Innochannel SDK
 * @version 1.0.0
 */
abstract class TestCase extends BaseTestCase
{
    /**
     * Setup method called before each test
     * 
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Common setup for all tests can be added here
        // For example: setting up mock objects, test data, etc.
    }

    /**
     * Teardown method called after each test
     * 
     * @return void
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        
        // Common cleanup for all tests can be added here
        // For example: clearing mock objects, resetting state, etc.
    }

    /**
     * Helper method to create mock data for testing
     * 
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function createMockData(array $data = []): array
    {
        return array_merge([
            'id' => 'test-id-' . uniqid(),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ], $data);
    }

    /**
     * Helper method to assert that an array contains specific keys
     * 
     * @param array<string> $expectedKeys
     * @param array<string, mixed> $array
     * @return void
     */
    protected function assertArrayHasKeys(array $expectedKeys, array $array): void
    {
        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $array, "Array should contain key: {$key}");
        }
    }

    /**
     * Helper method to assert that a value is a valid date string
     * 
     * @param mixed $value
     * @param string $message
     * @return void
     */
    protected function assertIsValidDate($value, string $message = ''): void
    {
        $this->assertIsString($value, $message ?: 'Value should be a string');
        $this->assertNotFalse(
            strtotime($value), 
            $message ?: 'Value should be a valid date string'
        );
    }

    /**
     * Helper method to assert that a value is a valid UUID
     * 
     * @param mixed $value
     * @param string $message
     * @return void
     */
    protected function assertIsValidUuid($value, string $message = ''): void
    {
        $this->assertIsString($value, $message ?: 'UUID should be a string');
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $value,
            $message ?: 'Value should be a valid UUID'
        );
    }

    /**
     * Helper method to create a mock HTTP response
     * 
     * @param int $statusCode
     * @param array<string, mixed> $data
     * @param array<string, string> $headers
     * @return array<string, mixed>
     */
    protected function createMockHttpResponse(
        int $statusCode = 200, 
        array $data = [], 
        array $headers = []
    ): array {
        return [
            'status_code' => $statusCode,
            'headers' => array_merge([
                'Content-Type' => 'application/json',
                'X-Request-ID' => 'test-request-' . uniqid(),
            ], $headers),
            'body' => json_encode($data),
            'data' => $data,
        ];
    }

    /**
     * Helper method to assert that an exception is thrown with specific message
     * 
     * @param string $expectedExceptionClass
     * @param string $expectedMessage
     * @param callable $callback
     * @return void
     */
    protected function assertExceptionThrown(
        string $expectedExceptionClass, 
        string $expectedMessage, 
        callable $callback
    ): void {
        $this->expectException($expectedExceptionClass);
        $this->expectExceptionMessage($expectedMessage);
        $callback();
    }
}