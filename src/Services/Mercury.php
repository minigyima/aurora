<?php

namespace Minigyima\Aurora\Services;

use Artisan;
use Minigyima\Aurora\Config\Constants;
use Minigyima\Aurora\Contracts\AbstractSingleton;
use Minigyima\Aurora\Traits\VerifiesEnvironment;
use Minigyima\Aurora\Util\ConsoleLogger;
use Override;
use Spatie\Async\Pool;
use Spatie\Watcher\Watch;
use Symfony\Component\Process\Process;

class Mercury extends AbstractSingleton
{
    use VerifiesEnvironment;

    private Process $auroraThread;

    public function __construct()
    {
        if (self::runningInMercury()) {
            $this->active = true;
            $this->createPool();
        } else {
            $this->active = false;
        }
    }

    private function createPool()
    {
        $this->threadPool = Pool::create();
    }

    #[Override]
    public static function use(): static
    {
        return app(static::class);
    }

    public function boot()
    {
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

        $watcher->onFileUpdated(function (string $file) {
            ConsoleLogger::log_info("File updated: $file --> Restarting Aurora...", 'Mercury');
            $this->auroraThread->stop();
            $this->killAurora();
            Artisan::call('config:cache');
            $this->auroraThread = $this->createAuroraThread();
        });

        $watcher->start();
        $this->auroraThread->wait();
    }

    private function createAuroraThread(): Process
    {
        $thread = Process::fromShellCommandline($this->auroraThreadCommand());
        $thread->setTty(true);
        $thread->start();
        $thread->setTimeout(null);

        return $thread;
    }

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

    private function killAurora()
    {
        ConsoleLogger::log_info('Killing Aurora...', 'Mercury');
        $process = Process::fromShellCommandline('bash ' . Constants::KILL_SCRIPT_PATH);
        $process->setTty(true);
        $process->start();
        $process->wait();
    }

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
