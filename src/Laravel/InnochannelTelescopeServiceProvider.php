<?php

namespace Innochannel\Laravel;

use Illuminate\Support\ServiceProvider;
use Laravel\Telescope\TelescopeServiceProvider;
use Laravel\Telescope\Telescope;
use Laravel\Telescope\Watchers\HttpClientWatcher;
use Laravel\Telescope\Watchers\RequestWatcher;
use Laravel\Telescope\Watchers\ExceptionWatcher;
use Laravel\Telescope\Watchers\LogWatcher;
use Innochannel\Laravel\Watchers\InnochannelHttpWatcher;

class InnochannelTelescopeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Registrar o Telescope apenas se estiver disponível
        if (class_exists(TelescopeServiceProvider::class)) {
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if (class_exists(Telescope::class)) {
            $this->configureTelescopeForInnochannel();
            $this->registerInnochannelWatchers();
        }
    }

    /**
     * Registrar watchers personalizados do Innochannel
     */
    protected function registerInnochannelWatchers(): void
    {
        // Registrar o watcher personalizado para HTTP
        $this->app->singleton(InnochannelHttpWatcher::class);
        $this->app->make(InnochannelHttpWatcher::class)->register($this->app);
    }

    /**
     * Configurar o Telescope especificamente para o Innochannel SDK
     */
    protected function configureTelescopeForInnochannel(): void
    {
        // Configurar watchers específicos para HTTP
        Telescope::filter(function ($entry) {
            // Filtrar apenas requisições relacionadas ao Innochannel
            if ($entry->type === 'request') {
                return $this->isInnochannelRequest($entry);
            }

            if ($entry->type === 'http_client') {
                return $this->isInnochannelHttpClient($entry);
            }

            // Incluir logs e exceções relacionadas ao Innochannel
            if ($entry->type === 'log' || $entry->type === 'exception') {
                return $this->isInnochannelRelated($entry);
            }

            return false;
        });

        // Configurar tags personalizadas
        Telescope::tag(function ($entry) {
            $tags = [];

            if ($this->isInnochannelRequest($entry)) {
                $tags[] = 'innochannel-request';
            }

            if ($this->isInnochannelHttpClient($entry)) {
                $tags[] = 'innochannel-http-client';
            }

            if ($this->isInnochannelRelated($entry)) {
                $tags[] = 'innochannel-sdk';
            }

            return $tags;
        });
    }

    /**
     * Verificar se a requisição é relacionada ao Innochannel
     */
    protected function isInnochannelRequest($entry): bool
    {
        if (!isset($entry->content['uri'])) {
            return false;
        }

        // Verificar se a URI contém rotas do Innochannel
        $uri = $entry->content['uri'];
        return str_contains($uri, '/innochannel') || 
               str_contains($uri, '/api/innochannel') ||
               str_contains($uri, 'innochannel');
    }

    /**
     * Verificar se o cliente HTTP é do Innochannel
     */
    protected function isInnochannelHttpClient($entry): bool
    {
        if (!isset($entry->content['uri'])) {
            return false;
        }

        $uri = $entry->content['uri'];
        
        // Verificar se é uma chamada para a API do Innochannel
        return str_contains($uri, 'innochannel.com') ||
               str_contains($uri, 'api.innochannel') ||
               (isset($entry->content['headers']['User-Agent']) && 
                str_contains($entry->content['headers']['User-Agent'], 'Innochannel-SDK'));
    }

    /**
     * Verificar se o log/exceção é relacionado ao Innochannel
     */
    protected function isInnochannelRelated($entry): bool
    {
        $content = json_encode($entry->content);
        
        return str_contains($content, 'Innochannel') ||
               str_contains($content, 'innochannel') ||
               str_contains($content, 'Innochannel\\Sdk') ||
               str_contains($content, 'Innochannel\\Laravel');
    }
}