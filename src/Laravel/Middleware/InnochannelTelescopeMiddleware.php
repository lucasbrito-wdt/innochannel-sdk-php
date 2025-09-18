<?php

namespace Innochannel\Laravel\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Telescope\Telescope;
use Laravel\Telescope\IncomingEntry;

class InnochannelTelescopeMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $startTime = microtime(true);
        
        // Capturar informações da requisição
        $this->logIncomingRequest($request, $startTime);
        
        $response = $next($request);
        
        $endTime = microtime(true);
        $duration = ($endTime - $startTime) * 1000; // em millisegundos
        
        // Capturar informações da resposta
        $this->logOutgoingResponse($request, $response, $duration);
        
        return $response;
    }

    /**
     * Log da requisição de entrada
     */
    protected function logIncomingRequest(Request $request, float $startTime): void
    {
        if (!$this->shouldLogRequest($request)) {
            return;
        }

        $entry = [
            'type' => 'innochannel_request',
            'family_hash' => null,
            'content' => [
                'method' => $request->method(),
                'uri' => $request->getRequestUri(),
                'headers' => $this->formatHeaders($request->headers->all()),
                'payload' => $this->formatPayload($request),
                'session_id' => $request->session()->getId() ?? null,
                'user_id' => $request->user()->id ?? null,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now()->toISOString(),
                'start_time' => $startTime,
            ],
            'tags' => ['innochannel-request', 'http-incoming']
        ];

        $this->recordTelescopeEntry($entry);
        
        // Log adicional para debug
        Log::channel('innochannel')->info('Innochannel HTTP Request', [
            'method' => $request->method(),
            'uri' => $request->getRequestUri(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }

    /**
     * Log da resposta de saída
     */
    protected function logOutgoingResponse(Request $request, $response, float $duration): void
    {
        if (!$this->shouldLogRequest($request)) {
            return;
        }

        $entry = [
            'type' => 'innochannel_response',
            'family_hash' => null,
            'content' => [
                'method' => $request->method(),
                'uri' => $request->getRequestUri(),
                'status_code' => $response->getStatusCode(),
                'headers' => $this->formatHeaders($response->headers->all()),
                'response_size' => strlen($response->getContent()),
                'duration_ms' => round($duration, 2),
                'timestamp' => now()->toISOString(),
            ],
            'tags' => ['innochannel-response', 'http-outgoing']
        ];

        $this->recordTelescopeEntry($entry);
        
        // Log adicional para debug
        Log::channel('innochannel')->info('Innochannel HTTP Response', [
            'method' => $request->method(),
            'uri' => $request->getRequestUri(),
            'status' => $response->getStatusCode(),
            'duration_ms' => round($duration, 2),
        ]);
    }

    /**
     * Verificar se deve logar a requisição
     */
    protected function shouldLogRequest(Request $request): bool
    {
        $uri = $request->getRequestUri();
        
        // Logar apenas requisições relacionadas ao Innochannel
        return str_contains($uri, '/innochannel') || 
               str_contains($uri, '/api/innochannel') ||
               $request->header('X-Innochannel-SDK') ||
               $request->header('User-Agent') && str_contains($request->header('User-Agent'), 'Innochannel');
    }

    /**
     * Formatar headers para log
     */
    protected function formatHeaders(array $headers): array
    {
        $formatted = [];
        
        foreach ($headers as $key => $values) {
            // Ocultar headers sensíveis
            if (in_array(strtolower($key), ['authorization', 'x-api-key', 'x-api-secret'])) {
                $formatted[$key] = ['***HIDDEN***'];
            } else {
                $formatted[$key] = $values;
            }
        }
        
        return $formatted;
    }

    /**
     * Formatar payload da requisição
     */
    protected function formatPayload(Request $request): array
    {
        $payload = [];
        
        // Capturar dados do corpo da requisição
        if ($request->isJson()) {
            $payload['json'] = $request->json()->all();
        }
        
        // Capturar parâmetros da query string
        if ($request->query()) {
            $payload['query'] = $request->query();
        }
        
        // Capturar dados do formulário (exceto arquivos)
        if ($request->request->count() > 0) {
            $payload['form'] = $request->except(['password', 'password_confirmation', '_token']);
        }
        
        return $payload;
    }

    /**
     * Registrar entrada no Telescope
     */
    protected function recordTelescopeEntry(array $entry): void
    {
        if (class_exists(Telescope::class)) {
            Telescope::recordEntry(
                IncomingEntry::make($entry)
            );
        }
    }
}