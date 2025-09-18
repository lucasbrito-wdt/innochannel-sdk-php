<?php

namespace Tests\Unit\Laravel;

use Innochannel\Sdk\Exceptions\ValidationException;
use PHPUnit\Framework\TestCase;

class ValidationExceptionTest extends TestCase
{
    use TestableHandlesInnochannelExceptions;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock the response() helper function for testing
        MockResponseHelper::mockResponseFunction();
    }

    public function test_validation_exception_returns_422_with_errors()
    {
        // Arrange
        $errors = [
            'property_name' => ['Property name is required'],
            'email' => ['Invalid email format']
        ];
        $message = 'Property validation failed';
        $exception = new ValidationException($message, $errors);

        // Act
        $response = $this->handleValidationException($exception);

        // Assert
        $this->assertInstanceOf(MockJsonResponse::class, $response);
        $this->assertEquals(422, $response->getStatusCode());
        
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals($message, $responseData['message']);
        $this->assertEquals($errors, $responseData['errors']);
    }

    public function test_validation_exception_includes_formatted_errors()
    {
        // Arrange
        $errors = [
            'property_name' => ['Property name is required', 'Property name must be at least 2 characters'],
            'city' => ['City cannot be empty']
        ];
        $message = 'Property validation failed';
        $exception = new ValidationException($message, $errors);

        // Act
        $response = $this->handleValidationException($exception);

        // Assert
        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('formatted_errors', $responseData);
        
        // getFormattedErrors() returns a string, not an array
        $expectedFormatted = 'property_name: Property name is required; property_name: Property name must be at least 2 characters; city: City cannot be empty';
        $this->assertEquals($expectedFormatted, $responseData['formatted_errors']);
    }

    public function test_validation_exception_includes_context_when_available()
    {
        // Arrange
        $errors = ['name' => ['Name is required']];
        $message = 'Validation failed';
        $context = ['property_id' => '123', 'user_id' => '456'];
        
        $exception = new ValidationException($message, $errors, $context);

        // Act
        $response = $this->handleValidationException($exception);

        // Assert
        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('context', $responseData);
        $this->assertEquals($context, $responseData['context']);
    }

    public function test_nested_field_errors_are_handled_correctly()
    {
        // Arrange
        $errors = [
            'rooms.0.name' => ['Room name is required'],
            'rooms.0.rates.0.id' => ['Rate ID is required'],
            'rooms.1.name' => ['Room name must not exceed 255 characters']
        ];
        $message = 'Property validation failed with nested errors';
        $exception = new ValidationException($message, $errors);

        // Act
        $response = $this->handleValidationException($exception);

        // Assert
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals($errors, $responseData['errors']);
        
        // getFormattedErrors() returns a string, so we check if it contains the expected parts
        $formattedErrors = $responseData['formatted_errors'];
        $this->assertStringContainsString('rooms.0.name: Room name is required', $formattedErrors);
        $this->assertStringContainsString('rooms.0.rates.0.id: Rate ID is required', $formattedErrors);
        $this->assertStringContainsString('rooms.1.name: Room name must not exceed 255 characters', $formattedErrors);
    }

    public function test_empty_errors_array_is_handled()
    {
        // Arrange
        $errors = [];
        $message = 'Validation failed with no specific errors';
        $exception = new ValidationException($message, $errors);

        // Act
        $response = $this->handleValidationException($exception);

        // Assert
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals(422, $response->getStatusCode());
        $this->assertEquals($message, $responseData['message']);
        $this->assertEquals($errors, $responseData['errors']);
        $this->assertEmpty($responseData['formatted_errors']);
    }
}