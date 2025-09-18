<?php

namespace Tests\Unit\Laravel;

use Innochannel\Sdk\Exceptions\ValidationException;
use Innochannel\Sdk\Exceptions\AuthenticationException;
use Innochannel\Sdk\Exceptions\NotFoundException;
use Innochannel\Sdk\Exceptions\RateLimitException;
use Innochannel\Sdk\Exceptions\ApiException;

/**
 * Testable version of HandlesInnochannelExceptions trait
 * This version doesn't have strict type hints to allow testing with mocks
 */
trait TestableHandlesInnochannelExceptions
{
    /**
     * Handle ValidationException from Innochannel SDK
     *
     * @param ValidationException $exception
     * @return mixed
     */
    protected function handleValidationException(ValidationException $exception)
    {
        $response = [
            'message' => $exception->getMessage(),
            'errors' => $exception->getErrors(),
        ];

        // Add context if available
        $context = $exception->getContext();
        if (!empty($context)) {
            $response['context'] = $context;
        }

        // Add formatted errors for better readability (returns string, not array)
        $formattedErrors = $exception->getFormattedErrors();
        if (!empty($formattedErrors)) {
            $response['formatted_errors'] = $formattedErrors;
        }

        return response()->json($response, 422);
    }

    /**
     * Handle AuthenticationException from Innochannel SDK
     *
     * @param AuthenticationException $exception
     * @return mixed
     */
    protected function handleAuthenticationException(AuthenticationException $exception)
    {
        return response()->json([
            'message' => $exception->getMessage(),
            'error' => 'Authentication failed',
            'code' => $exception->getCode(),
        ], 401);
    }

    /**
     * Handle NotFoundException from Innochannel SDK
     *
     * @param NotFoundException $exception
     * @return mixed
     */
    protected function handleNotFoundException(NotFoundException $exception)
    {
        return response()->json([
            'message' => $exception->getMessage(),
            'error' => 'Resource not found',
            'code' => $exception->getCode(),
        ], 404);
    }

    /**
     * Handle RateLimitException from Innochannel SDK
     *
     * @param RateLimitException $exception
     * @return mixed
     */
    protected function handleRateLimitException(RateLimitException $exception)
    {
        $response = [
            'message' => $exception->getMessage(),
            'error' => 'Rate limit exceeded',
            'code' => $exception->getCode(),
        ];

        // Add retry information if available
        if (method_exists($exception, 'getRetryAfter')) {
            $response['retry_after'] = $exception->getRetryAfter();
        }

        return response()->json($response, 429);
    }

    /**
     * Handle generic ApiException from Innochannel SDK
     *
     * @param ApiException $exception
     * @return mixed
     */
    protected function handleApiException(ApiException $exception)
    {
        $statusCode = $exception->getCode() ?: 500;
        
        return response()->json([
            'message' => $exception->getMessage(),
            'error' => 'API error occurred',
            'code' => $exception->getCode(),
        ], $statusCode);
    }
}