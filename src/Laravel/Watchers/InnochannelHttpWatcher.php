<?php

namespace Innochannel\Laravel\Watchers;

use Laravel\Telescope\Watchers\Watcher;
use Laravel\Telescope\IncomingEntry;
use Illuminate\Http\Client\Events\RequestSending;
use Illuminate\Http\Client\Events\ResponseReceived;
use Illuminate\Http\Client\Events\ConnectionFailed;
use Illuminate\Support\Facades\Event;

class InnochannelHttpWatcher extends Watcher
{
    /**
     * Store request start times
     *
     * @var array
     */
    protected static $requestStartTimes = [];

    /**
     * Register the watcher.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    public function register($app)
    {
        $app['events']->listen(RequestSending::class, [$this, 'recordRequest']);
        $app['events']->listen(ResponseReceived::class, [$this, 'recordResponse']);
        $app['events']->listen(ConnectionFailed::class, [$this, 'recordConnectionFailed']);
    }

    /**
     * Record an HTTP request being sent.
     *
     * @param  \Illuminate\Http\Client\Events\RequestSending  $event
     * @return void
     */
    public function recordRequest(RequestSending $event)
    {
        if (!$this->shouldRecord($event)) {
            return;
        }

        // Armazenar tempo de início da requisição
        $requestKey = $this->getRequestKey($event->request);
        static::$requestStartTimes[$requestKey] = microtime(true);

        $this->recordEntry([
            'type' => 'innochannel_http_client',
            'family_hash' => $this->familyHash($event->request),
            'content' => [
                'method' => $event->request->method(),
                'uri' => (string) $event->request->url(),
                'headers' => $this->formatHeaders($event->request->headers()),
                'body' => $this->formatBody($event->request->body()),
                'timestamp' => now()->toISOString(),
                'status' => 'sending',
            ],
            'tags' => $this->getTags($event->request),
        ]);
    }

    /**
     * Record an HTTP response being received.
     *
     * @param  \Illuminate\Http\Client\Events\ResponseReceived  $event
     * @return void
     */
    public function recordResponse(ResponseReceived $event)
    {
        if (!$this->shouldRecord($event)) {
            return;
        }

        $duration = $this->calculateDuration($event);

        $this->recordEntry([
            'type' => 'innochannel_http_client',
            'family_hash' => $this->familyHash($event->request),
            'content' => [
                'method' => $event->request->method(),
                'uri' => (string) $event->request->url(),
                'request_headers' => $this->formatHeaders($event->request->headers()),
                'request_body' => $this->formatBody($event->request->body()),
                'response_status' => $event->response->status(),
                'response_headers' => $this->formatHeaders($event->response->headers()),
                'response_body' => $this->formatResponseBody($event->response->body()),
                'response_size' => strlen($event->response->body()),
                'duration' => $duration,
                'timestamp' => now()->toISOString(),
                'status' => 'completed',
            ],
            'tags' => $this->getTags($event->request, $event->response),
        ]);
    }

    /**
     * Record a failed HTTP connection.
     *
     * @param  \Illuminate\Http\Client\Events\ConnectionFailed  $event
     * @return void
     */
    public function recordConnectionFailed(ConnectionFailed $event)
    {
        if (!$this->shouldRecord($event)) {
            return;
        }

        $this->recordEntry([
            'type' => 'innochannel_http_client',
            'family_hash' => $this->familyHash($event->request),
            'content' => [
                'method' => $event->request->method(),
                'uri' => (string) $event->request->url(),
                'headers' => $this->formatHeaders($event->request->headers()),
                'body' => $this->formatBody($event->request->body()),
                'error' => 'Connection failed',
                'timestamp' => now()->toISOString(),
                'status' => 'failed',
            ],
            'tags' => array_merge($this->getTags($event->request), ['error', 'connection-failed']),
        ]);
    }

    /**
     * Determine if the event should be recorded.
     *
     * @param  mixed  $event
     * @return bool
     */
    protected function shouldRecord($event): bool
    {
        if (!isset($event->request)) {
            return false;
        }

        $url = (string) $event->request->url();
        $headers = $event->request->headers();

        // Verificar se é uma requisição do Innochannel SDK
        return str_contains($url, 'innochannel.com') ||
               str_contains($url, 'api.innochannel') ||
               (isset($headers['User-Agent']) && str_contains($headers['User-Agent'][0] ?? '', 'Innochannel-SDK')) ||
               (isset($headers['X-Innochannel-SDK']) && $headers['X-Innochannel-SDK']);
    }

    /**
     * Generate a family hash for grouping related requests.
     *
     * @param  \Illuminate\Http\Client\Request  $request
     * @return string
     */
    protected function familyHash($request): string
    {
        return md5($request->method() . ':' . parse_url($request->url(), PHP_URL_PATH));
    }

    /**
     * Format headers for logging.
     *
     * @param  array  $headers
     * @return array
     */
    protected function formatHeaders(array $headers): array
    {
        $formatted = [];
        
        foreach ($headers as $key => $values) {
            // Ocultar headers sensíveis
            if (in_array(strtolower($key), ['authorization', 'x-api-key', 'x-api-secret', 'x-innochannel-secret'])) {
                $formatted[$key] = ['***HIDDEN***'];
            } else {
                $formatted[$key] = $values;
            }
        }
        
        return $formatted;
    }

    /**
     * Format request body for logging.
     *
     * @param  string  $body
     * @return mixed
     */
    protected function formatBody(string $body)
    {
        if (empty($body)) {
            return null;
        }

        // Tentar decodificar JSON
        $decoded = json_decode($body, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            // Ocultar campos sensíveis
            return $this->hideSensitiveData($decoded);
        }

        // Se não for JSON, retornar como string (limitado)
        return strlen($body) > 1000 ? substr($body, 0, 1000) . '...' : $body;
    }

    /**
     * Format response body for logging.
     *
     * @param  string  $body
     * @return mixed
     */
    protected function formatResponseBody(string $body)
    {
        if (empty($body)) {
            return null;
        }

        // Tentar decodificar JSON
        $decoded = json_decode($body, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        // Se não for JSON, retornar como string (limitado)
        return strlen($body) > 2000 ? substr($body, 0, 2000) . '...' : $body;
    }

    /**
     * Hide sensitive data from arrays.
     *
     * @param  array  $data
     * @return array
     */
    protected function hideSensitiveData(array $data): array
    {
        $sensitiveKeys = ['password', 'api_key', 'api_secret', 'token', 'authorization', 'secret'];
        
        foreach ($data as $key => $value) {
            if (in_array(strtolower($key), $sensitiveKeys)) {
                $data[$key] = '***HIDDEN***';
            } elseif (is_array($value)) {
                $data[$key] = $this->hideSensitiveData($value);
            }
        }
        
        return $data;
    }

    /**
     * Get tags for the request.
     *
     * @param  \Illuminate\Http\Client\Request  $request
     * @param  \Illuminate\Http\Client\Response|null  $response
     * @return array
     */
    protected function getTags($request, $response = null): array
    {
        $tags = ['innochannel-http-client', 'http-client'];
        
        // Adicionar tag baseada no método HTTP
        $tags[] = strtolower($request->method());
        
        // Adicionar tag baseada no status da resposta
        if ($response) {
            $status = $response->status();
            if ($status >= 200 && $status < 300) {
                $tags[] = 'success';
            } elseif ($status >= 400 && $status < 500) {
                $tags[] = 'client-error';
            } elseif ($status >= 500) {
                $tags[] = 'server-error';
            }
        }
        
        // Adicionar tag baseada na URL
        $url = (string) $request->url();
        if (str_contains($url, '/api/')) {
            $tags[] = 'api';
        }
        
        return $tags;
    }

    /**
     * Get unique key for request.
     *
     * @param  \Illuminate\Http\Client\Request  $request
     * @return string
     */
    protected function getRequestKey($request): string
    {
        return spl_object_hash($request);
    }

    /**
     * Calculate request duration.
     *
     * @param  \Illuminate\Http\Client\Events\ResponseReceived  $event
     * @return float
     */
    protected function calculateDuration($event): float
    {
        $requestKey = $this->getRequestKey($event->request);
        
        if (isset(static::$requestStartTimes[$requestKey])) {
            $duration = (microtime(true) - static::$requestStartTimes[$requestKey]) * 1000;
            unset(static::$requestStartTimes[$requestKey]);
            return round($duration, 2);
        }
        
        // Se não temos o tempo de início, retornar 0
        return 0.0;
    }

    /**
     * Record an entry.
     *
     * @param  array  $entry
     * @return void
     */
    protected function recordEntry(array $entry): void
    {
        try {
            Event::dispatch('telescope.entry', [
                IncomingEntry::make($entry)
            ]);
        } catch (\Throwable $e) {
            // Silenciosamente ignorar erros de gravação do Telescope
            // para não quebrar a aplicação
            \Log::warning('Failed to record Telescope entry', [
                'error' => $e->getMessage(),
                'entry_type' => $entry['type'] ?? 'unknown'
            ]);
        }
    }
}