<?php

namespace Tests\Unit\Laravel;

/**
 * Mock response helper for testing Laravel components outside of Laravel framework
 */
class MockResponseHelper
{
    private static $responseMocked = false;

    public static function mockResponseFunction(): void
    {
        if (!self::$responseMocked && !function_exists('response')) {
            eval('
                function response() {
                    return new class {
                        public function json($data, $status = 200, array $headers = [], $options = 0) {
                            return new \Tests\Unit\Laravel\MockJsonResponse($data, $status, $headers);
                        }
                    };
                }
            ');
            self::$responseMocked = true;
        }
    }
}