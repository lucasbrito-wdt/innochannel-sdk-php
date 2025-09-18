<?php

namespace Innochannel\Laravel\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Innochannel\Sdk\Exceptions\ValidationException as InnochannelValidationException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * Render an exception into an HTTP response.
     *
     * @param Request $request
     * @param Throwable $e
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws Throwable
     */
    public function render($request, Throwable $e)
    {
        // Handle Innochannel SDK ValidationException
        if ($e instanceof InnochannelValidationException) {
            return $this->handleInnochannelValidationException($request, $e);
        }

        return parent::render($request, $e);
    }

    /**
     * Handle Innochannel ValidationException and return 422 response
     *
     * @param Request $request
     * @param InnochannelValidationException $exception
     * @return JsonResponse
     */
    protected function handleInnochannelValidationException(Request $request, InnochannelValidationException $exception): JsonResponse
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
}