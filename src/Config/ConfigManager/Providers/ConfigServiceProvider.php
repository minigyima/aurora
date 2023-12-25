<?php

namespace Minigyima\Aurora\Config\ConfigManager\Providers;

use Illuminate\Support\ServiceProvider;
use Minigyima\Aurora\Config\ConfigManager\Services\ConfigManager;

class ConfigServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(ConfigManager::class, fn () => new ConfigManager());
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
