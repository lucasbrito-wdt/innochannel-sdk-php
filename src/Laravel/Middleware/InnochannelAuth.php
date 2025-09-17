<?php

namespace Innochannel\Laravel\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class InnochannelAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): SymfonyResponse
    {
        // Check if webhook signature verification is enabled
        if (config('innochannel.webhooks.verify_signature', true)) {
            if (!$this->verifyWebhookSignature($request)) {
                return response()->json([
                    'error' => 'Invalid webhook signature'
                ], Response::HTTP_UNAUTHORIZED);
            }
        }

        // Check API key for API routes
        if ($request->is('api/innochannel/*')) {
            if (!$this->verifyApiKey($request)) {
                return response()->json([
                    'error' => 'Invalid or missing API key'
                ], Response::HTTP_UNAUTHORIZED);
            }
        }

        return $next($request);
    }

    /**
     * Verify webhook signature.
     */
    protected function verifyWebhookSignature(Request $request): bool
    {
        $signature = $request->header('X-Innochannel-Signature');
        $secret = config('innochannel.webhooks.secret');

        if (!$signature || !$secret) {
            return false;
        }

        $payload = $request->getContent();
        $expectedSignature = 'sha256=' . hash_hmac('sha256', $payload, $secret);

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Verify API key.
     */
    protected function verifyApiKey(Request $request): bool
    {
        $apiKey = $request->header('X-API-Key') 
                 ?? $request->header('Authorization')
                 ?? $request->query('api_key');

        // Remove 'Bearer ' prefix if present
        if (str_starts_with($apiKey, 'Bearer ')) {
            $apiKey = substr($apiKey, 7);
        }

        $configApiKey = config('innochannel.api_key');

        return $apiKey && $configApiKey && hash_equals($configApiKey, $apiKey);
    }
}