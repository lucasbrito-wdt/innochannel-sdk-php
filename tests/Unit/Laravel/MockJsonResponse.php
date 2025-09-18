<?php

namespace Tests\Unit\Laravel;

/**
 * Mock JsonResponse class for testing without Laravel framework
 */
class MockJsonResponse
{
    private $data;
    private $status;
    private $headers;

    public function __construct($data, $status = 200, array $headers = [])
    {
        $this->data = $data;
        $this->status = $status;
        $this->headers = $headers;
    }

    public function getStatusCode(): int
    {
        return $this->status;
    }

    public function getContent(): string
    {
        return json_encode($this->data);
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }
}