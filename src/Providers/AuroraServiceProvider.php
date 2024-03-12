<?php

namespace Minigyima\Aurora\Providers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;
use Minigyima\Aurora\Cache\AuroraCache;
use Minigyima\Aurora\Commands\AuroraShellCommand;
use Minigyima\Aurora\Commands\BuildAuroraCommand;
use Minigyima\Aurora\Commands\BuildProductionCommand;
use Minigyima\Aurora\Commands\ConfigureDatabase;
use Minigyima\Aurora\Commands\ConfigureSoketiCommand;
use Minigyima\Aurora\Commands\MercuryBoot;
use Minigyima\Aurora\Commands\MercuryBootHorizon;
use Minigyima\Aurora\Commands\PrepareProductionCommand;
use Minigyima\Aurora\Commands\StartAuroraCommand;
use Minigyima\Aurora\Commands\StopAuroraCommand;
use Minigyima\Aurora\Commands\UpdateAuroraCommand;
use Minigyima\Aurora\Concerns\VerifiesEnvironment;
use Minigyima\Aurora\Config\ConfigManager\Providers\ConfigServiceProvider;
use Minigyima\Aurora\Config\Constants;
use Minigyima\Aurora\Handlers\PostInstallHandler;
use Minigyima\Aurora\Services\Aurora;
use Minigyima\Aurora\Services\Mercury;
use Minigyima\Aurora\Support\AuroraExceptionHandler;
use Minigyima\Aurora\Support\CheckForSwoole;
use Minigyima\Warden\Providers\WardenServiceProvider;

class AuroraServiceProvider extends ServiceProvider
{
    use VerifiesEnvironment;

    /**
     * Create a new service provider instance.
     *
     * @param Application $app
     * @return void
     */
    public function __construct($app)
    {
        $this->app = $app;

        if (self::isFirstRun()) {
            PostInstallHandler::handle();
        }
    }

    public function boot()
    {
        require_once __DIR__ . '/../Support/helpers.php';

        $this->publish();
        $this->loadMigrationsFrom(__DIR__ . '/../Database/migrations');

        $this->loadCommands();
        $this->registerComponents();
        AboutCommand::add(
            'Aurora - A Laravel Docker Development Environment',
            fn() => [
                'Version' => Constants::AURORA_VERSION,
                'Debug Mode' => config('aurora.debug_mode') ? 'Enabled' : 'Disabled',
                'Mercury Runtime Version' => Constants::MERCURY_VERSION,
                'Warden' => config('aurora.warden_enabled') ? 'Enabled' : 'Disabled',
                'Config Manager' => config('aurora.config_manager_enabled') ? 'Enabled' : 'Disabled',
            ]
        );

        Cache::extend('aurora', function ($app) {
            return Cache::repository(new \Minigyima\Aurora\Services\AuroraCache());
        });
    }

    private function publish(): void
    {
        $this->publishes([__DIR__ . '/../Stubs/docker' => base_path('docker')], 'aurora-docker');
        $this->publishes([__DIR__ . '/../Stubs/docker-compose.stub.yml' => base_path('docker-compose.yml')],
            'aurora-docker');
        $this->publishes(
            [
                __DIR__ . '/../Stubs//docker-compose-override.yml.example' => base_path(
                    'docker-compose.override.yml.example'
                )
            ],
            'aurora-docker'
        );

        $this->publishes([__DIR__ . '/../Config/aurora.php' => config_path('aurora.php')], 'aurora-config');
    }

    private function loadCommands(): void
    {
        $this->commands([
            AuroraShellCommand::class,
            StartAuroraCommand::class,
            StopAuroraCommand::class,
            BuildAuroraCommand::class,
            ConfigureDatabase::class,
            MercuryBootHorizon::class,
            MercuryBoot::class,
            ConfigureSoketiCommand::class,
            BuildProductionCommand::class,
            UpdateAuroraCommand::class,
            PrepareProductionCommand::class,
        ]);
    }

    private function registerComponents(): void
    {
        if (config('aurora.config_manager_enabled')) {
            $this->app->register(ConfigServiceProvider::class);
        }

        if (config('aurora.warden_enabled')) {
            $this->app->register(WardenServiceProvider::class);
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/aurora.php', 'aurora');
        if ($this->app->runningInConsole() && ! CheckForSwoole::check()) {
            $this->app->singleton(Aurora::class, fn() => new Aurora());
            $this->app->singleton(Mercury::class, fn() => new Mercury());
        }

        if (config('aurora.automatically_register_exception_handler')) {
            $this->app->singleton(Illuminate\Contracts\Debug\ExceptionHandler::class, AuroraExceptionHandler::class);
        }
    }
}
