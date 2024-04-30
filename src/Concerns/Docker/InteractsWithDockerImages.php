<?php

namespace Minigyima\Aurora\Concerns\Docker;

use Minigyima\Aurora\Config\Constants;
use Minigyima\Aurora\Errors\DockerExportException;
use Minigyima\Aurora\Support\ConsoleLogger;
use Symfony\Component\Process\Process;
use function Minigyima\Aurora\Support\path_resolve;

/**
 * Trait for interacting with Docker images
 * @package Minigyima\Aurora\Concerns
 */
trait InteractsWithDockerImages
{
    use InteractsWithDockerCommands;

    /**
     * Export a Docker image to a tarball
     * @param string $image_tag The tag of the image to export
     * @param string $export_dir The directory to export the image to
     * @throws DockerExportException
     */
    private function exportImage(string $image_tag, string $export_dir): void
    {
        ConsoleLogger::log_info('Exporting image...', 'InteractsWithDockerImages');

        if (! file_exists(Constants::AURORA_BUILD_PATH)) {
            mkdir(Constants::AURORA_BUILD_PATH, 0777, true);
        }

        $export_dir = path_resolve($export_dir);

        if (! file_exists($export_dir)) {
            ConsoleLogger::log_error(
                'The export directory does not exist. Please create it and try again.',
                'InteractsWithDockerImages'
            );
            throw new DockerExportException(
                'The export directory does not exist. Please create it and try again.'
            );
        }

        if (! is_dir($export_dir)) {
            ConsoleLogger::log_error(
                'The export directory is not a directory. Please create it and try again.',
                'InteractsWithDockerImages'
            );
            throw new DockerExportException(
                'The export directory is not a directory. Please create it and try again.'
            );
        }

        if (! is_writable($export_dir)) {
            ConsoleLogger::log_error(
                'The export directory is not writable. Please check the permissions and try again.',
                'InteractsWithDockerImages'
            );
            throw new DockerExportException(
                'The export directory is not writable. Please check the permissions and try again.'
            );
        }

        $path = path_resolve($export_dir, $image_tag . '.docker');
        $path = str_replace(':', '_', $path);
        $command = $this->generateDockerSaveCommand($image_tag, $path);
        ConsoleLogger::log_trace('Creating tarball @ ' . $path, 'InteractsWithDockerImages');

        $process = Process::fromShellCommandline($command);
        $process->setTimeout(0);
        $process->run(function ($type, $buffer) {
            ConsoleLogger::log_info($buffer, 'docker');
        });

        $process->wait();

        if ($process->isSuccessful() === false) {
            ConsoleLogger::log_error(
                'The Docker export process ended with a non-zero exit code. Check the logs for more information.',
                'InteractsWithDockerImages'
            );
            throw new DockerExportException(
                'The Docker export process ended with a non-zero exit code. Check the logs for more information.'
            );
        }

        ConsoleLogger::log_success('Image exported successfully. Path: ' . base_path($path));
    }

    /**
     * Push an image to the registry
     * @param string $image_tag The tag of the image to push
     * @throws DockerExportException
     */
    private function pushImage(string $image_tag): void
    {
        ConsoleLogger::log_info('Pushing image to registry...', 'InteractsWithDockerImages');
        $this->tagImage($image_tag);
        $this->login();

        ConsoleLogger::log_trace('Pushing image...', 'InteractsWithDockerImages');

        $command = $this->generateDockerPushCommand($image_tag);
        $process = Process::fromShellCommandline($command);
        $process->setTimeout(0);
        $process->run(function ($type, $buffer) {
            ConsoleLogger::log_info($buffer, 'docker');
        });

        $process->wait();

        if ($process->isSuccessful() === false) {
            ConsoleLogger::log_error(
                'The Docker push process ended with a non-zero exit code. Check the logs for more information.',
                'InteractsWithDockerImages'
            );
            throw new DockerExportException(
                'The Docker push process ended with a non-zero exit code. Check the logs for more information.'
            );
        }

        ConsoleLogger::log_success('Image pushed successfully.');
    }

    // function to push image to registry

    /**
     * Tag an image with the registry's tags
     * @param string $image_tag The tag of the image to push
     * @throws DockerExportException
     */
    private function tagImage(string $image_tag): void
    {
        ConsoleLogger::log_trace('Tagging image...', 'InteractsWithDockerImages');
        $command = $this->generateDockerTagCommand($image_tag);

        $process = Process::fromShellCommandline($command);
        $process->setTimeout(0);
        $process->run(function ($type, $buffer) {
            ConsoleLogger::log_info($buffer, 'docker');
        });
        $process->wait();

        if ($process->isSuccessful() === false) {
            ConsoleLogger::log_error(
                'The Docker tag process ended with a non-zero exit code. Check the logs for more information.',
                'InteractsWithDockerImages'
            );
            throw new DockerExportException(
                'The Docker tag process ended with a non-zero exit code. Check the logs for more information.'
            );
        }

        ConsoleLogger::log_success('Image tagged successfully.', 'InteractsWithDockerImages');
    }

    /**
     * Login to the Docker registry
     * @throws DockerExportException
     *
     */
    private function login(): void
    {
        ConsoleLogger::log_info('Logging in to Docker registry...', 'InteractsWithDockerImages');

        $command = $this->generateDockerLoginCommand();

        $process = Process::fromShellCommandline($command);
        $process->setTimeout(0);
        $process->run(function ($type, $buffer) {
            ConsoleLogger::log_info($buffer, 'docker');
        });

        $process->wait();

        if ($process->isSuccessful() === false) {
            ConsoleLogger::log_error(
                'The Docker login process ended with a non-zero exit code. Check the logs for more information.',
                'InteractsWithDockerImages'
            );
            throw new DockerExportException(
                'The Docker login process ended with a non-zero exit code. Check the logs for more information.'
            );
        }

        ConsoleLogger::log_success('Logged in to Docker registry.', 'InteractsWithDockerImages');
    }

    /**
     * Verify the Docker registry settings
     * @throws DockerExportException
     */
    private function verifyDockerRegistry(): void
    {
        $registry_url = config('aurora.docker_registry_url');
        $registry_namespace = config('aurora.docker_registry_namespace');
        $registry_username = config('aurora.docker_registry_username');
        $registry_password = config('aurora.docker_registry_password');

        if (empty($registry_url) ||
            empty($registry_namespace) ||
            empty($registry_username) ||
            empty($registry_password)) {
            ConsoleLogger::log_error(
                'The Docker registry URL, username, password or namespace is not set. Please set them in the config file.',
                'InteractsWithDockerImages'
            );
            throw new DockerExportException(
                'The Docker registry URL or namespace is not set. Please set them in the config file.'
            );
        }

        ConsoleLogger::log_info('Docker registry verified.', 'InteractsWithDockerImages');
    }
}
