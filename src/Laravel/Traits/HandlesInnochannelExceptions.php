<?php

namespace Innochannel\Laravel\Traits;

use Illuminate\Http\JsonResponse;
use Innochannel\Sdk\Exceptions\ValidationException as InnochannelValidationException;
use Innochannel\Sdk\Exceptions\ApiException;
use Innochannel\Sdk\Exceptions\AuthenticationException;
use Innochannel\Sdk\Exceptions\NotFoundException;
use Innochannel\Sdk\Exceptions\RateLimitException;

trait HandlesInnochannelExceptions
{
    /**
     * Handle Innochannel SDK exceptions and return appropriate HTTP responses
     *
     * @param \Throwable $exception
     * @return JsonResponse|null
     */
    protected function handleInnochannelException(\Throwable $exception): ?JsonResponse
    {
        if ($exception instanceof InnochannelValidationException) {
            return $this->handleValidationException($exception);
        }

        if ($exception instanceof AuthenticationException) {
            return $this->handleAuthenticationException($exception);
        }

        if ($exception instanceof NotFoundException) {
            return $this->handleNotFoundException($exception);
        }

        if ($exception instanceof RateLimitException) {
            return $this->handleRateLimitException($exception);
        }

        if ($exception instanceof ApiException) {
            return $this->handleApiException($exception);
        }

        return null; // Let Laravel handle other exceptions
    }

    /**
     * Handle ValidationException from Innochannel SDK
     *
     * @param InnochannelValidationException $exception
     * @return JsonResponse
     */
    protected function handleValidationException(InnochannelValidationException $exception): JsonResponse
    {
        $response = [
            'message' => $exception->getMessage(),
            'errors' => $exception->getErrors(),
        ];

        // Add additional context if available
        $context = $exception->getContext();
        if (!empty($context)) {
            $response['context'] = $context;
        }

        // Add formatted errors for better readability
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
     * @return JsonResponse
     */
    protected function handleAuthenticationException(AuthenticationException $exception): JsonResponse
    {
        return response()->json([
            'message' => $exception->getMessage(),
            'error' => 'Authentication failed',
        ], 401);
    }

    /**
     * Handle NotFoundException from Innochannel SDK
     *
     * @param NotFoundException $exception
     * @return JsonResponse
     */
    protected function handleNotFoundException(NotFoundException $exception): JsonResponse
    {
        return response()->json([
            'message' => $exception->getMessage(),
            'error' => 'Resource not found',
        ], 404);
    }

    /**
     * Handle RateLimitException from Innochannel SDK
     *
     * @param RateLimitException $exception
     * @return JsonResponse
     */
    protected function handleRateLimitException(RateLimitException $exception): JsonResponse
    {
        return response()->json([
            'message' => $exception->getMessage(),
            'error' => 'Rate limit exceeded',
        ], 429);
    }

    /**
     * Handle generic ApiException from Innochannel SDK
     *
     * @param ApiException $exception
     * @return JsonResponse
     */
    protected function handleApiException(ApiException $exception): JsonResponse
    {
        $statusCode = $exception->getCode() ?: 500;
        
        // Ensure status code is valid HTTP status code
        if ($statusCode < 100 || $statusCode > 599) {
            $statusCode = 500;
        }

        $response = [
            'message' => $exception->getMessage(),
            'error' => 'API error occurred',
        ];

        // Add context if available
        $context = $exception->getContext();
        if (!empty($context)) {
            $response['context'] = $context;
        }

        return response()->json($response, $statusCode);
    }
}