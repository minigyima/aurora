<?php

namespace Minigyima\Aurora\Services;

use Minigyima\Aurora\Concerns\Build\CreatesProdDockerfile;
use Minigyima\Aurora\Concerns\Build\GeneratesProductionEnvFile;
use Minigyima\Aurora\Concerns\Build\PreparesTempDirectory;
use Minigyima\Aurora\Concerns\Build\RunsPreFlightChecks;
use Minigyima\Aurora\Concerns\Docker\InteractsWithComposeFiles;
use Minigyima\Aurora\Concerns\Docker\InteractsWithDockerCommands;
use Minigyima\Aurora\Concerns\EnsuresAuroraStorageExists;
use Minigyima\Aurora\Concerns\TestsForDocker;
use Minigyima\Aurora\Concerns\VerifiesEnvironment;
use Minigyima\Aurora\Config\Constants;
use Minigyima\Aurora\Contracts\AbstractSingleton;
use Minigyima\Aurora\Errors\BuildCancelledException;
use Minigyima\Aurora\Errors\CommandNotApplicableException;
use Minigyima\Aurora\Errors\DockerBuildFailedException;
use Minigyima\Aurora\Errors\NoDockerException;
use Minigyima\Aurora\Errors\NoGitException;
use Minigyima\Aurora\Support\ConsoleLogger;
use Minigyima\Aurora\Support\StrClean;
use Override;
use Symfony\Component\Process\Process;
use function Laravel\Prompts\confirm;

/**
 * Aurora - The Aurora Runtime, used to manage the Aurora Docker environment
 * @package Minigyima\Aurora\Services
 * @internal
 */
class Aurora extends AbstractSingleton
{
    use VerifiesEnvironment,
        TestsForDocker,
        InteractsWithComposeFiles,
        GeneratesProductionEnvFile,
        RunsPreFlightChecks,
        EnsuresAuroraStorageExists,
        PreparesTempDirectory,
        CreatesProdDockerfile,
        InteractsWithDockerCommands;

    /**
     * @var bool
     * Whether or not Aurora is running in Mercury
     */
    private bool $isMercury = false;

    /**
     * @var string
     * The Docker tag for the production build
     */
    private string $docker_tag;

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
     * @throws NoGitException
     * @throws BuildCancelledException
     */
    public function buildProduction(): void
    {
        $this->preFlightChecks();
        ConsoleLogger::log_info('Building production...');
        $this->prepareTempDirectory();
        $this->createProdDockerfile();

        $this->docker_tag = str_replace([' ', '-'], ['_', '_'], StrClean::clean(strtolower(config('app.name')))) .
                            ':' .
                            date('Y-m-d_H-i-s');

        ConsoleLogger::log_info('Building Docker image...');
        $command = self::generateDockerBuildCommand($this->docker_tag, '.');

        $process = Process::fromShellCommandline($command);
        $process->setTimeout(0);

        $process->run(function ($type, $buffer) {
            ConsoleLogger::log_info($buffer, 'docker');
        });

        $process->wait();

        if ($process->isSuccessful() === false) {
            ConsoleLogger::log_error(
                'The Docker build process ended with a non-zero exit code. Check the logs for more information.'
            );
            throw new DockerBuildFailedException(
                'The Docker build process ended with a non-zero exit code. Check the logs for more information.'
            );

        }
        $this->rmTemp();

        ConsoleLogger::log_success('Image built successfully. Tag: ' . $this->docker_tag);

        if (confirm('Would you like to export the image?')) {
            ConsoleLogger::log_info('Exporting image...');

            if (! file_exists(Constants::AURORA_BUILD_PATH)) {
                ConsoleLogger::log_trace('Creating build directory...');
                mkdir(Constants::AURORA_BUILD_PATH, 0777, true);
            }

            $path = Constants::AURORA_BUILD_PATH . '/' . $this->docker_tag . '.docker';
            $command = self::generateDockerSaveCommand($this->docker_tag, $path);
            ConsoleLogger::log_trace('Creating tarball @ ' . $path);

            $process = Process::fromShellCommandline($command);
            $process->setTimeout(0);
            $process->run(function ($type, $buffer) {
                ConsoleLogger::log_info($buffer, 'docker');
            });

            $process->wait();

            ConsoleLogger::log_success('Image exported successfully. Path: ' . base_path($path));
        }
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
