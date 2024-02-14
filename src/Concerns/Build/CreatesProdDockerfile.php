<?php

namespace Minigyima\Aurora\Concerns\Build;

use Minigyima\Aurora\Errors\BuildCancelledException;
use Minigyima\Aurora\Support\ConsoleLogger;
use function Laravel\Prompts\confirm;

/**
 * CreatesProdDockerfile - Trait for creating the production Dockerfile
 * @package Minigyima\Aurora\Concerns\Build
 * @internal
 */
trait CreatesProdDockerfile
{
    /**
     * Create the production Dockerfile
     * @param bool $yes
     * @return void
     * @throws BuildCancelledException
     * @internal
     */
    private function createProdDockerfile(bool $yes = false): void
    {
        $this->ensureDockerFileExists();

        if ($this->usePublishedDockerfile()) {
            copy(base_path('docker/prod/Dockerfile'), base_path('Dockerfile'));
            ConsoleLogger::log_warning('Using published Dockerfile', 'CreateProdDockerfile');
            return;
        }

        ConsoleLogger::log_info('Checking Dockerfile...', 'CreateProdDockerfile');
        if (hash_file('sha256', base_path('Dockerfile')) !==
            hash_file('sha256', __DIR__ . '/../../Stubs/docker/prod/Dockerfile')) {

            ConsoleLogger::log_warning('Dockerfile differs from the stub', 'CreateProdDockerfile');

            ConsoleLogger::log_trace('Checking modification date...', 'CreateProdDockerfile');
            if (filemtime(base_path('Dockerfile')) > filemtime(__DIR__ . '/../../Stubs/docker/prod/Dockerfile')) {
                ConsoleLogger::log_warning(
                    'Your Dockerfile is newer than the one shipped by Aurora.',
                    'CreateProdDockerfile'
                );
                $this->overwriteDockerfile($yes);


            } else {
                ConsoleLogger::log_trace('Making backup of old Dockerfile...', 'CreateProdDockerfile');
                copy(base_path('Dockerfile'), base_path('Dockerfile.aurora.bak'));
                ConsoleLogger::log_trace('Old Dockerfile detected, overwriting', 'CreateProdDockerfile');
                copy(__DIR__ . '/../../Stubs/docker/prod/Dockerfile', base_path('Dockerfile'));
            }
        }
        ConsoleLogger::log_success('Dockerfile up-to-date, moving on...', 'CreateProdDockerfile');

    }

    /**
     * Ensure that the Dockerfile exists
     * @return void
     */
    private function ensureDockerFileExists(): void
    {
        if ($this->usePublishedDockerfile()) {
            return;
        }

        if (! file_exists(base_path('Dockerfile'))) {
            ConsoleLogger::log_warning('Dockerfile not found, copying stub...', 'CreateProdDockerfile');
            copy(__DIR__ . '/../../Stubs/docker/prod/Dockerfile', base_path('Dockerfile'));
        }
    }

    /**
     * Check if the Dockerfile is published
     * @return void
     */
    private function usePublishedDockerfile(): bool
    {
        return file_exists(base_path('docker/prod/Dockerfile'));
    }

    /**
     * Prompt the user to overwrite the Dockerfile
     * @param bool $yes
     * @return void
     * @throws BuildCancelledException
     * @internal
     */
    private function overwriteDockerfile(bool $yes = false): void
    {
        $choice = $yes || confirm('Would you like to overwrite your Dockerfile?');

        if ($choice) {
            ConsoleLogger::log_info('Overwriting Dockerfile...', 'CreateProdDockerfile');
            ConsoleLogger::log_trace('Making backup of old Dockerfile...', 'CreateProdDockerfile');
            copy(base_path('Dockerfile'), base_path('Dockerfile.aurora.bak'));
            copy(__DIR__ . '/../../Stubs/docker/prod/Dockerfile', base_path('Dockerfile'));
            ConsoleLogger::log_success('Dockerfile overwritten', 'CreateProdDockerfile');
            ConsoleLogger::log_info(
                'Please review the new Dockerfile.
                If you want to customize the build process,
                you can do so by publishing the "aurora-docker" assets,
                and editing the Dockerfile inside the "docker/prod" directory.',
                'CreateProdDockerfile'
            );

            if (! ($yes || confirm('Shall we continue?'))) {
                ConsoleLogger::log_error('Build cancelled', 'CreateProdDockerfile');
                throw new BuildCancelledException('Build cancelled');
            }
        }
    }
}
