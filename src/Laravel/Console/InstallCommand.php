<?php

namespace Innochannel\Laravel\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'innochannel:install 
                            {--force : Overwrite existing files}
                            {--skip-migrations : Skip running migrations}
                            {--skip-config : Skip publishing config file}';

    /**
     * The console command description.
     */
    protected $description = 'Install the Innochannel Laravel package';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Installing Innochannel Laravel Package...');

        // Publish configuration file
        if (!$this->option('skip-config')) {
            $this->publishConfig();
        }

        // Run migrations
        if (!$this->option('skip-migrations')) {
            $this->runMigrations();
        }

        // Create storage directories
        $this->createStorageDirectories();

        // Display completion message
        $this->displayCompletionMessage();

        return self::SUCCESS;
    }

    /**
     * Publish the configuration file.
     */
    protected function publishConfig(): void
    {
        $configPath = config_path('innochannel.php');
        
        if (File::exists($configPath) && !$this->option('force')) {
            if (!$this->confirm('Configuration file already exists. Do you want to overwrite it?')) {
                $this->info('Skipping configuration file publication.');
                return;
            }
        }

        $this->call('vendor:publish', [
            '--provider' => 'Innochannel\Laravel\InnochannelServiceProvider',
            '--tag' => 'innochannel-config',
            '--force' => $this->option('force'),
        ]);

        $this->info('✓ Configuration file published');
    }

    /**
     * Run the package migrations.
     */
    protected function runMigrations(): void
    {
        if ($this->confirm('Do you want to run the migrations now?', true)) {
            $this->info('Running migrations...');
            
            $this->call('migrate', [
                '--path' => 'vendor/innochannel/sdk-php/database/migrations',
                '--force' => true,
            ]);

            $this->info('✓ Migrations completed');
        } else {
            $this->warn('Remember to run migrations later with: php artisan migrate --path=vendor/innochannel/sdk-php/database/migrations');
        }
    }

    /**
     * Create necessary storage directories.
     */
    protected function createStorageDirectories(): void
    {
        $directories = [
            storage_path('app/innochannel'),
            storage_path('app/innochannel/logs'),
            storage_path('app/innochannel/cache'),
            storage_path('app/innochannel/temp'),
        ];

        foreach ($directories as $directory) {
            if (!File::exists($directory)) {
                File::makeDirectory($directory, 0755, true);
                $this->info("✓ Created directory: {$directory}");
            }
        }
    }

    /**
     * Display completion message with next steps.
     */
    protected function displayCompletionMessage(): void
    {
        $this->info('');
        $this->info('🎉 Innochannel Laravel Package installed successfully!');
        $this->info('');
        $this->info('Next steps:');
        $this->info('1. Configure your API credentials in config/innochannel.php');
        $this->info('2. Set your environment variables in .env file:');
        $this->info('   INNOCHANNEL_API_KEY=your_api_key');
        $this->info('   INNOCHANNEL_API_SECRET=your_api_secret');
        $this->info('   INNOCHANNEL_BASE_URL=https://api.innochannel.com');
        $this->info('3. Test your configuration with: php artisan innochannel:test-connection');
        $this->info('');
        $this->info('For more information, visit: https://docs.innochannel.com/laravel');
    }
}