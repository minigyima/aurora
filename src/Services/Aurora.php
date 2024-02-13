<?php

namespace Minigyima\Aurora\Services;

use Minigyima\Aurora\Concerns\InteractsWithComposeFiles;
use Minigyima\Aurora\Concerns\TestsForDocker;
use Minigyima\Aurora\Concerns\VerifiesEnvironment;
use Minigyima\Aurora\Config\Constants;
use Minigyima\Aurora\Contracts\AbstractSingleton;
use Minigyima\Aurora\Errors\CommandNotApplicableException;
use Minigyima\Aurora\Errors\NoDockerException;
use Minigyima\Aurora\Support\ConsoleLogger;
use Override;
use Symfony\Component\Process\Process;

/**
 * Aurora - The Aurora Runtime, used to manage the Aurora Docker environment
 * @package Minigyima\Aurora\Services
 * @internal
 */
class Aurora extends AbstractSingleton
{
    use VerifiesEnvironment, TestsForDocker, InteractsWithComposeFiles;

    /**
     * @var bool
     * Whether or not Aurora is running in Mercury
     */
    private bool $isMercury = false;

    /**
     * Aurora constructor.
     */
    public function __construct()
    {
        if (self::runningInMercury()) {
            $this->isMercury = true;
        } else {
            $this->isMercury = false;
        }

        $this->active = true;
        $this->ensureStorageExists();
    }

    /**
     * Ensure the storage directory exists
     * @return void
     */
    private function ensureStorageExists(): void
    {
        if (! file_exists(base_path(Constants::AURORA_DOCKER_STORAGE_PATH))) {
            mkdir(base_path(Constants::AURORA_DOCKER_STORAGE_PATH), 0777, true);
        }

        if (! file_exists(base_path(Constants::AURORA_DOCKER_STORAGE_PATH)) . '/.gitignore') {
            file_put_contents(base_path(Constants::AURORA_DOCKER_STORAGE_PATH) . '/.gitignore', '*');
        }

        if (! file_exists(base_path(Constants::AURORA_DOCKER_STORAGE_PATH) . '/logs/nginx')) {
            mkdir(base_path(Constants::AURORA_DOCKER_STORAGE_PATH) . '/logs/nginx', 0777, true);
        }
    }

    /**
     * Returns an instance of the Aurora singleton
     * @return static
     */
    #[Override]
    public static function use(): static
    {
        return app(static::class);
    }

    /**
     * Start Aurora
     * @throws CommandNotApplicableException
     * @throws NoDockerException
     */
    public function start(): Process
    {
        if ($this->isMercury) {
            throw new CommandNotApplicableException('This command is not applicable when running in Mercury.');
        }

        if (! self::testForDocker()) {
            throw new NoDockerException('Docker could not be found on this machine.');
        }

        $command = $this->generateComposePrompt('up');

        return Process::fromShellCommandline($command)->setTimeout(null);
    }

    /**
     * Generate the compose prompt for use with Docker Compose
     * @param string $command
     * @return string
     */
    private function generateComposePrompt(string $command): string
    {
        ConsoleLogger::log_info('Generating compose prompt for command: ' . $command);
        $files = [];
        $name = strtolower(config('app.name'));

        $files[] = self::getCurrentComposeFile();

        if (file_exists(base_path('docker-compose.override.yaml'))) {
            $files[] = base_path('docker-compose.override.yaml');
        }

        if (file_exists(base_path('docker-compose.override.yml'))) {
            $files[] = base_path('docker-compose.override.yml');
        }

        $profiles = [];
        if (config('aurora.sockets_enabled')) {
            $profiles[] = 'sockets';
        }

        if (config('aurora.redis_enabled')) {
            $profiles[] = 'redis';
        }

        if (config('aurora.database_enabled')) {
            $profiles[] = 'database';
        }

        if (config('aurora.queue_enabled')) {
            $profiles[] = 'queue';
        }

        if (config('aurora.scheduler_enabled')) {
            $profiles[] = 'scheduler';
        }

        $profile_str = '--profile ' . implode(' --profile ', $profiles);
        $file_str = '-f ' . implode(' -f ', $files);

        ConsoleLogger::log_trace('Using compose files: ' . implode(', ', $files));
        ConsoleLogger::log_trace('Using profiles: ' . implode(', ', $profiles));

        $command = "docker compose $file_str $profile_str -p $name $command";
        ConsoleLogger::log_trace('Using compose command: ' . $command);

        return trim($command);
    }

    /**
     * Stop Aurora
     * @throws CommandNotApplicableException
     */
    public function stop(): Process
    {
        if ($this->isMercury) {
            throw new CommandNotApplicableException('This command is not applicable when running in Mercury.');
        }

        $command = $this->generateComposePrompt('down -t 0 --volumes');
        return Process::fromShellCommandline($command)->setTimeout(null);
    }

    /**
     * Build Aurora
     * @throws CommandNotApplicableException
     * @throws NoDockerException
     */
    public function build(): Process
    {
        if ($this->isMercury) {
            throw new CommandNotApplicableException('This command is not applicable when running in Mercury.');
        }

        if (! self::testForDocker()) {
            throw new NoDockerException('Docker could not be found on this machine.');
        }
        $command = $this->generateComposePrompt('build mercury')
                   . ' && ' .
                   $this->generateComposePrompt('build');

        return Process::fromShellCommandline($command)->setTimeout(null);
    }

    /**
     * Opens a shell inside the Mercury container
     * @throws CommandNotApplicableException
     */
    public function shell(): Process
    {
        if ($this->isMercury) {
            throw new CommandNotApplicableException('This command is not applicable when running in Mercury.');
        }

        $command = $this->generateComposePrompt('exec -it mercury bash');
        return Process::fromShellCommandline($command)->setTimeout(null)->setTty(true);
    }
}
