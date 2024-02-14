<?php

namespace Minigyima\Aurora\Concerns\Build;

use Minigyima\Aurora\Concerns\VerifiesEnvironment;
use Minigyima\Aurora\Errors\BuildCancelledException;
use Minigyima\Aurora\Errors\NoGitException;
use Minigyima\Aurora\Support\ConsoleLogger;
use Minigyima\Aurora\Support\GitHelper;
use function Laravel\Prompts\confirm;

/**
 * RunsPreFlightChecks - Trait for running pre-flight checks for the build process
 * @package Minigyima\Aurora\Concerns\Build
 */
trait RunsPreFlightChecks
{
    use GeneratesProductionEnvFile, VerifiesEnvironment;

    /**
     * Run checks before building the production environment
     * @param bool $yes
     * @return void
     * @throws NoGitException
     * @throws BuildCancelledException
     * @internal
     */
    private function preFlightChecks(bool $yes = false): void
    {
        if (! self::testForGit()) {
            ConsoleLogger::log_error('Git is not installed on your system', 'PreFlightChecks');
            throw new NoGitException('Git is not installed on your system');
        }

        if (! file_exists(base_path('.env.production'))) {
            ConsoleLogger::log_warning(
                'Production environment file not found, initializing prod env...'
            );
            $this->generateProductionEnvFile();
        }

        if (! GitHelper::isRepo(base_path())) {
            ConsoleLogger::log_warning('This project is not a git repository', 'PreFlightChecks');
            $choice = $yes || confirm('Would you like to initialize a git repository?');
            if ($choice) {
                ConsoleLogger::log_info('Initializing git repository...', 'PreFlightChecks');
                GitHelper::init(base_path());
                ConsoleLogger::log_success('Git repository initialized', 'PreFlightChecks');
            } else {
                ConsoleLogger::log_error('Git repository not initialized', 'PreFlightChecks');
                throw new BuildCancelledException('Git repository not initialized');
            }
        }

        if (GitHelper::isDirty(base_path())) {
            ConsoleLogger::log_warning('There are uncommitted changes in the repository', 'PreFlightChecks');
            $choice = $yes || confirm('Would you like to continue?');
            if (! $choice) {
                ConsoleLogger::log_error(
                    'Build cancelled. Please commit any uncommited changes.',
                    'PreFlightChecks'
                );
                throw new BuildCancelledException('Build cancelled. Please commit any uncommited changes.');
            }
        }

        ConsoleLogger::log_success('Pre-build checks passed!');
    }

}
