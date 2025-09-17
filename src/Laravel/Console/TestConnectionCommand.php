<?php

namespace Innochannel\Laravel\Console;

use Illuminate\Console\Command;
use Innochannel\Laravel\Facades\Innochannel;
use Exception;

class TestConnectionCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'innochannel:test-connection 
                            {--timeout=30 : Connection timeout in seconds}
                            {--verbose : Show detailed connection information}';

    /**
     * The console command description.
     */
    protected $description = 'Test the connection to Innochannel API';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Testing Innochannel API connection...');
        $this->info('');

        // Check configuration
        if (!$this->checkConfiguration()) {
            return self::FAILURE;
        }

        // Test API connection
        if (!$this->testApiConnection()) {
            return self::FAILURE;
        }

        // Test specific endpoints
        $this->testEndpoints();

        $this->info('');
        $this->info('🎉 All tests passed! Your Innochannel integration is ready to use.');

        return self::SUCCESS;
    }

    /**
     * Check if the configuration is properly set.
     */
    protected function checkConfiguration(): bool
    {
        $this->info('Checking configuration...');

        $requiredConfigs = [
            'innochannel.api_key' => 'API Key',
            'innochannel.api_secret' => 'API Secret',
            'innochannel.base_url' => 'Base URL',
        ];

        $missingConfigs = [];

        foreach ($requiredConfigs as $config => $name) {
            $value = config($config);
            
            if (empty($value)) {
                $missingConfigs[] = $name;
                $this->error("✗ {$name} is not configured");
            } else {
                if ($this->option('verbose')) {
                    $maskedValue = $this->maskSensitiveValue($config, $value);
                    $this->info("✓ {$name}: {$maskedValue}");
                } else {
                    $this->info("✓ {$name} is configured");
                }
            }
        }

        if (!empty($missingConfigs)) {
            $this->error('');
            $this->error('Please configure the missing values in your .env file or config/innochannel.php');
            return false;
        }

        $this->info('✓ Configuration check passed');
        $this->info('');

        return true;
    }

    /**
     * Test the basic API connection.
     */
    protected function testApiConnection(): bool
    {
        $this->info('Testing API connection...');

        try {
            // Try to get properties as a basic connectivity test
            $properties = Innochannel::getProperties(['limit' => 1]);
            
            $this->info('✓ API connection successful');
            
            if ($this->option('verbose')) {
                $this->info("  Response received with " . count($properties) . " properties");
            }

            return true;

        } catch (Exception $e) {
            $this->error('✗ API connection failed');
            $this->error("  Error: {$e->getMessage()}");
            
            if ($this->option('verbose')) {
                $this->error("  Exception: " . get_class($e));
                $this->error("  File: {$e->getFile()}:{$e->getLine()}");
            }

            return false;
        }
    }

    /**
     * Test specific API endpoints.
     */
    protected function testEndpoints(): void
    {
        $this->info('Testing API endpoints...');

        $endpoints = [
            'Properties' => function () {
                return Innochannel::getProperties(['limit' => 1]);
            },
            'Bookings' => function () {
                return Innochannel::getBookings(['limit' => 1]);
            },
        ];

        foreach ($endpoints as $name => $test) {
            try {
                $result = $test();
                $this->info("✓ {$name} endpoint working");
                
                if ($this->option('verbose')) {
                    $count = is_array($result) ? count($result) : 'N/A';
                    $this->info("  Returned {$count} items");
                }
            } catch (Exception $e) {
                $this->warn("⚠ {$name} endpoint failed: {$e->getMessage()}");
            }
        }
    }

    /**
     * Mask sensitive configuration values for display.
     */
    protected function maskSensitiveValue(string $config, string $value): string
    {
        if (str_contains($config, 'key') || str_contains($config, 'secret')) {
            return substr($value, 0, 4) . str_repeat('*', max(0, strlen($value) - 8)) . substr($value, -4);
        }

        return $value;
    }
}