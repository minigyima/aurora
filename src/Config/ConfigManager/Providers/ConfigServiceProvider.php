<?php

namespace Minigyima\Aurora\Config\ConfigManager\Providers;

use Illuminate\Support\ServiceProvider;
use Minigyima\Aurora\Config\ConfigManager\Services\ConfigManager;

/**
 * ConfigServiceProvider - Registers the ConfigManager service
 * @package Minigyima\Aurora\Config\ConfigManager\Providers
 */
class ConfigServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(ConfigManager::class, fn() => new ConfigManager());
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
