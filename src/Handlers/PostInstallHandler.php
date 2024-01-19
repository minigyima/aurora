<?php

namespace Minigyima\Aurora\Handlers;

use Artisan;
use Illuminate\Cache\CacheServiceProvider;
use Illuminate\Filesystem\FilesystemServiceProvider;
use Illuminate\Foundation\Providers\ArtisanServiceProvider;
use Illuminate\Queue\QueueServiceProvider;
use Illuminate\Support\Facades\Log;
use Laravel\Octane\OctaneServiceProvider;
use Minigyima\Aurora\Config\Constants;
use Minigyima\Aurora\Models\EnvironmentFile;
use Nette\PhpGenerator\ClassType;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * PostInstallHandler - Handler for post-install script
 * @package Minigyima\Aurora\Handlers
 * @internal
 */
class PostInstallHandler
{
    /**
     * Handler for post-install script
     * - Called by AuroraServiceProvider in the constructor,
     *   only runs if the injected marker class hasn't yet
     *   been autoloaded by Composer.
     *  - Only runs if called by Laravel's auto-discovery mechanism
     * @param bool $force
     * @return void
     * @internal
     */
    public static function handle(bool $force = false): void
    {
        $channel = Log::channel('errorlog');
        $backtrace = debug_backtrace();
        $caller = $backtrace[2]['class'];
        if ($caller !== 'Illuminate\Foundation\ProviderRepository' && ! $force) {
            $channel->info(
                "Aurora (PostInstHandler) - Not called by Laravel's auto-discovery mechanism, skipping postinst script"
            );
            return;
        }

        $channel->info('Aurora - Running postinst script');
        self::writeMarker();
        $channel->info('Patching composer.json');
        $path = base_path('composer.json');
        $composer = json_decode(file_get_contents($path), true);

        $composer['autoload']['psr-4'][Constants::MARKER_NAMESPACE . '\\'] = Constants::AURORA_STORAGE_PATH;

        foreach (Constants::INJECTED_SCRIPTS as $key => $script) {
            $composer['scripts'][$key] = $script;
        }

        file_put_contents($path, json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $channel->info('Patched composer.json');

        $env = new EnvironmentFile();
        $env->set('OCTANE_SERVER', 'swoole');
        $env->set('AURORA_DEBUG', 'false');
        $env->write();

        $env_example = new EnvironmentFile(base_path('.env.example'));
        $env_example->set('OCTANE_SERVER', 'swoole');
        $env_example->set('AURORA_DEBUG', 'false');
        $env_example->write();

        $channel->info('Publishing Octane config');

        app()->register(QueueServiceProvider::class);
        app()->register(FilesystemServiceProvider::class);
        app()->register(CacheServiceProvider::class);
        app()->register(ArtisanServiceProvider::class);
        app()->register(OctaneServiceProvider::class);

        Artisan::call('list', [], new ConsoleOutput());

        dd('help');

        $channel->info('Published Octane config');

        $channel->info('Aurora - Finished running postinst script');
    }

    /**
     * Write marker file
     * Used to determine if the post-install script has been run
     * @return void
     */
    private static function writeMarker(): void
    {
        self::log()->info('Aurora - Writing marker file');
        $class = new ClassType(Constants::MARKER);
        $class
            ->setFinal()
            ->addComment("Marker class for Aurora's composer.json patching features");

        $class->addConstant('PATCHED', true)->setType('bool')->setPublic();

        if (! file_exists(base_path(Constants::AURORA_STORAGE_PATH))) {
            self::log()->info('Aurora - Creating aurora directory');
            mkdir(base_path('storage/aurora'), 0777, true);
        }
        $path = base_path(Constants::MARKER_PATH);
        file_put_contents($path, "<?php\n\n" . 'namespace ' . Constants::MARKER_NAMESPACE . ";\n\n" . $class);
        self::log()->info('Aurora - Wrote marker file');
    }

    /**
     * Get the logger
     * @return LoggerInterface
     */
    private static function log(): LoggerInterface
    {
        return Log::channel('errorlog');
    }
}
