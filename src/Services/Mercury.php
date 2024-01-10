<?php

namespace Minigyima\Aurora\Services;

use Artisan;
use Minigyima\Aurora\Concerns\VerifiesEnvironment;
use Minigyima\Aurora\Config\Constants;
use Minigyima\Aurora\Contracts\AbstractSingleton;
use Minigyima\Aurora\Support\ConsoleLogger;
use Override;
use Spatie\Watcher\Exceptions\CouldNotStartWatcher;
use Spatie\Watcher\Watch;
use Symfony\Component\Process\Process;


/**
 * Mercury - The Aurora Runtime
 * @package Minigyima\Aurora\Services
 * @internal
 */
class Mercury extends AbstractSingleton
{
    use VerifiesEnvironment;

    /**
     * @var Process
     * The thread that currently runs Aurora
     *  - Either Swoole or FPM
     */
    private Process $auroraThread;

    /**
     * Mercury constructor.
     */
    public function __construct()
    {
        if (self::runningInMercury()) {
            $this->active = true;
        } else {
            $this->active = false;
        }
    }

    /**
     * Returns an instance of the Mercury singleton
     * @return static
     */
    #[Override]
    public static function use(): static
    {
        return app(static::class);
    }

    /**
     * Boot the Aurora runtime
     * @return int
     * @throws CouldNotStartWatcher
     */
    public function boot(): int
    {
        $this->killAurora();
        Artisan::call('config:cache');
        $this->requireActive();
        $name = config('app.name');
        $figlet = shell_exec('figlet -f slant Aurora');
        ConsoleLogger::log_info(PHP_EOL . $figlet, 'Mercury');
        ConsoleLogger::log_info("Booting Aurora application '$name'...", 'Mercury');
        ConsoleLogger::log_success('Runtime alive, handover to Aurora complete.', 'Mercury');

        $this->auroraThread = $this->createAuroraThread();

        ConsoleLogger::log_info('Starting File Watcher...', 'Aurora');
        $watcher = Watch::paths([base_path('.env')]);

        $watcher->onFileUpdated(function (string $file) use ($watcher) {
            ConsoleLogger::log_info("File updated: $file --> Restarting Aurora...", 'Mercury');
            Artisan::call('config:cache');
            $this->killAurora();
            $watcher->shouldContinue(fn() => false);
            $this->auroraThread->stop();
        });

        $watcher->start();
        $this->auroraThread->wait();

        return 1;
    }

    /**
     * Kill the Aurora runtime
     * - This will kill the Aurora thread, no matter if it is FPM or Swoole
     * @return void
     */
    private function killAurora()
    {
        ConsoleLogger::log_info('Killing Aurora...', 'Mercury');
        $process = Process::fromShellCommandline('bash ' . Constants::KILL_SCRIPT_PATH);
        $process->setTty(true);
        $process->start();
        $process->wait();
    }

    /**
     * Create the Aurora thread
     * - This will create a new thread for Aurora to run in, based on the config
     * @return Process
     * @see config/aurora.php
     */
    private function createAuroraThread(): Process
    {
        $thread = Process::fromShellCommandline($this->auroraThreadCommand());
        $thread->setTty(true);
        $thread->start();
        $thread->setTimeout(null);

        return $thread;
    }

    /**
     * Get the command to start the Aurora thread
     * - This will return the command to start the Aurora thread, based on the config
     * @return string
     * @see config/aurora.php
     */
    private function auroraThreadCommand(): string
    {
        $debug_enabled = config('aurora.debug_mode');
        if ($debug_enabled) {
            ConsoleLogger::log_warning(
                'Debug mode is enabled. This is not recommended for production environments.',
                'Aurora'
            );
            ConsoleLogger::log_info('Debug mode enabled. Starting server with FPM and XDebug support...', 'Aurora');
            return 'bash ' . Constants::DEBUG_SCRIPT_PATH;
        } else {
            ConsoleLogger::log_info('Debug mode disabled. Starting server with Swoole...', 'Aurora');
            return 'bash ' . Constants::SWOOLE_SCRIPT_PATH;
        }
    }

    /**
     * Boot the Horizon part of the Aurora runtime
     * - Also starts a file system watcher to restart Horizon when a Job is changed
     * @return void
     * @throws CouldNotStartWatcher
     */
    public function bootHorizon()
    {
        $this->requireActive();
        $thread = new Process(['php', 'artisan', 'horizon']);

        $thread->setTty(true);

        ConsoleLogger::log_info('Starting File Watcher...', 'Mercury');
        $watcher = Watch::paths([
            base_path('app/Jobs'),
            base_path('app/Events'),
            base_path('app/Mail'),
            base_path('.env'),
        ]);

        $watcher->onFileUpdated(function (string $file) use ($thread) {
            ConsoleLogger::log_info("File updated: $file --> Restarting Horizon...", 'Mercury');
            $thread->stop();
            $thread->start();
        });

        $watcher->onFileDeleted(function (string $file) use ($thread) {
            ConsoleLogger::log_info("File deleted: $file --> Restarting Horizon...", 'Mercury');
            $thread->stop();
            $thread->start();
        });

        $watcher->start();

        $thread->start();
        $thread->wait();
    }
}
