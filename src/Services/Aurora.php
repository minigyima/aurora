<?php

namespace Minigyima\Aurora\Services;

use Minigyima\Aurora\Concerns\Build\CreatesProdDockerfile;
use Minigyima\Aurora\Concerns\Build\GeneratesProductionEnvFile;
use Minigyima\Aurora\Concerns\Build\PreparesTempDirectory;
use Minigyima\Aurora\Concerns\Build\RunsPreFlightChecks;
use Minigyima\Aurora\Concerns\Docker\InteractsWithComposeFiles;
use Minigyima\Aurora\Concerns\Docker\InteractsWithDockerCommands;
use Minigyima\Aurora\Concerns\Docker\InteractsWithDockerImages;
use Minigyima\Aurora\Concerns\EnsuresAuroraStorageExists;
use Minigyima\Aurora\Concerns\TestsForDocker;
use Minigyima\Aurora\Concerns\VerifiesEnvironment;
use Minigyima\Aurora\Config\Constants;
use Minigyima\Aurora\Contracts\AbstractSingleton;
use Minigyima\Aurora\Errors\BuildCancelledException;
use Minigyima\Aurora\Errors\CommandNotApplicableException;
use Minigyima\Aurora\Errors\DockerBuildFailedException;
use Minigyima\Aurora\Errors\DockerExportException;
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
        InteractsWithDockerCommands,
        InteractsWithDockerImages;

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

        if (!self::testForDocker()) {
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

        if (!self::testForDocker()) {
            throw new NoDockerException('Docker could not be found on this machine.');
        }
        $command = $this->generateComposePrompt('build mercury')
            . ' && ' .
            $this->generateComposePrompt('build');

        return Process::fromShellCommandline($command)->setTimeout(null);
    }

    /**
     * Build the production environment
     * @param bool $export - Whether or not to export the image
     * @param string $export_dir - The directory to export the image to
     * @param bool $yes - Whether or not to bypass the confirmation prompts
     * @throws BuildCancelledException
     * @throws DockerBuildFailedException
     * @throws NoGitException
     * @throws DockerExportException
     */
    public function buildProduction(
        bool   $export = false,
        string $export_dir = Constants::AURORA_BUILD_PATH,
        bool   $yes = false,
        bool   $push = false,
    ): void {
        $this->preFlightChecks($yes);
        ConsoleLogger::log_info('Building production...');
        $this->prepareTempDirectory();
        $this->createProdDockerfile($yes);

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

        if ((!($yes && !$export)) && ($export || confirm('Would you like to export the image?'))) {
            $this->exportImage($this->docker_tag, $export_dir);
        }

        if ((!($yes && !$push)) && ($push || confirm('Would you like to push the image to the registry?'))) {
            $this->verifyDockerRegistry();
            $this->pushImage($this->docker_tag);
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

        $APP_NAME = config('app.name');

        $ps1 = "PS1='($APP_NAME) \h:\w# '";

        $command = $this->generateComposePrompt('exec -it mercury bash -c "' . $ps1 . ' bash"');
        return Process::fromShellCommandline($command)->setTimeout(null)->setTty(true);
    }
}
