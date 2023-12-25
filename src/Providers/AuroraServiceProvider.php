<?php

namespace Minigyima\Aurora\Providers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Minigyima\Aurora\Commands\AuroraShellCommand;
use Minigyima\Aurora\Commands\BuildAuroraCommand;
use Minigyima\Aurora\Commands\ConfigureDatabase;
use Minigyima\Aurora\Commands\MercuryBoot;
use Minigyima\Aurora\Commands\MercuryBootHorizon;
use Minigyima\Aurora\Commands\StartAuroraCommand;
use Minigyima\Aurora\Commands\StopAuroraCommand;
use Minigyima\Aurora\Config\ConfigManager\Providers\ConfigServiceProvider;
use Minigyima\Aurora\Handlers\PostInstallHandler;
use Minigyima\Aurora\Services\Aurora;
use Minigyima\Aurora\Services\Mercury;
use Minigyima\Aurora\Traits\VerifiesEnvironment;
use Minigyima\Aurora\Util\CheckForSwoole;
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
        require_once __DIR__ . '/../Util/rrmdir.php';
        $this->publishes([__DIR__ . '/../Stubs/docker' => base_path('docker')], 'aurora-docker');
        $this->loadCommands();
        $this->registerComponents();
    }

    private function loadCommands()
    {
        $this->commands([
            AuroraShellCommand::class,
            StartAuroraCommand::class,
            StopAuroraCommand::class,
            BuildAuroraCommand::class,
            ConfigureDatabase::class,
            MercuryBootHorizon::class,
            MercuryBoot::class
        ]);
    }

    private function registerComponents()
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
        $this->mergeConfigFrom(__DIR__ . '/../config/aurora.php', 'aurora');
        if ($this->app->runningInConsole() && ! CheckForSwoole::check()) {
            $this->app->singleton(Aurora::class, fn() => new Aurora());
            $this->app->singleton(Mercury::class, fn() => new Mercury());
        }
    }
}
