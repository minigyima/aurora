<?php

namespace Minigyima\Aurora\Handlers;

use Illuminate\Support\Facades\Log;
use Minigyima\Aurora\Config\Constants;
use Minigyima\Aurora\Models\EnvironmentFile;
use Nette\PhpGenerator\ClassType;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Process;

/**
 * Class PostInstallHandler - Handler for post-install script
 * @package Minigyima\Aurora\Handlers
 * @internal
 */
class PostInstallHandler
{
    /**
     * Handler for post-install script
     * @return void
     */
    public static function handle(): void
    {
        $channel = Log::channel('errorlog');
        $channel->info('Aurora - Running postinst script');
        self::writeMarker();
        $channel->info('Patching composer.json');
        $path = base_path('composer.json');
        $composer = json_decode(file_get_contents($path), true);

        $composer['autoload']['psr-4'][Constants::MARKER_NAMESPACE . '\\'] = Constants::AURORA_STORAGE_PATH;

        foreach (Constants::INJECTED_SCRIPTS as $key => $script) {
            $composer['scripts'][$key] = $script;
        }

        $process = Process::fromShellCommandline('php artisan octane:install --server swoole');
        $process->setTty(true);
        $process->start();
        $process->wait();

        $env = new EnvironmentFile();
        $env->set('OCTANE_SERVER', 'swoole');
        $env->set('AURORA_DEBUG', 'false');
        $env->write();

        $env_example = new EnvironmentFile(base_path('.env.example'));
        $env_example->set('OCTANE_SERVER', 'swoole');
        $env_example->set('AURORA_DEBUG', 'false');
        $env_example->write();

        file_put_contents($path, json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $channel->info('Patched composer.json');
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
     * @return LoggerInterface
     */
    private static function log(): LoggerInterface
    {
        return Log::channel('errorlog');
    }
}
