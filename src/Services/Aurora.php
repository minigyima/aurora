<?php

namespace Minigyima\Aurora\Services;

use Minigyima\Aurora\Config\Constants;
use Minigyima\Aurora\Contracts\AbstractSingleton;
use Minigyima\Aurora\Errors\CommandNotApplicableException;
use Minigyima\Aurora\Errors\NoDockerException;
use Minigyima\Aurora\Traits\InteractsWIthComposeFiles;
use Minigyima\Aurora\Traits\TestsForDocker;
use Minigyima\Aurora\Traits\VerifiesEnvironment;
use Override;
use Symfony\Component\Process\Process;

class Aurora extends AbstractSingleton
{
    use VerifiesEnvironment, TestsForDocker, InteractsWIthComposeFiles;

    private bool $isMercury = false;

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

    private function generateComposePrompt(string $command): string
    {
        $files = [];
        $name = strtolower(config('app.name'));

        $files[] = self::getCurrentComposeFile();

        if (file_exists(base_path('docker-compose.override.yaml'))) {
            $files[] = base_path('docker-compose.override.yaml');
        }

        if (file_exists(base_path('docker-compose.override.yml'))) {
            $files[] = base_path('docker-compose.override.yml');
        }

        return 'docker compose -f ' . implode(' -f ', $files) . ' -p ' . $name . ' ' . $command;
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
