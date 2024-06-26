<?php

namespace Minigyima\Aurora\Concerns\Docker;

use Minigyima\Aurora\Support\ConsoleLogger;
use function Minigyima\Aurora\Support\path_resolve;

/**
 * Trait for interacting with Docker commands
 * @package Minigyima\Aurora\Concerns
 */
trait InteractsWithDockerCommands
{
    use InteractsWithComposeFiles;

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
            $files[] = path_resolve(
                base_path('docker-compose.override.yaml')
            );
        }

        if (file_exists(base_path('docker-compose.override.yml'))) {
            $files[] = path_resolve(
                base_path('docker-compose.override.yml')
            );
        }

        if (file_exists(base_path('docker-compose.aurora.extra.yaml'))) {
            $files[] = path_resolve(
                base_path('docker-compose.aurora.extra.yaml')
            );
        }

        if (file_exists(base_path('docker-compose.aurora.extra.yml'))) {
            $files[] = path_resolve(
                base_path('docker-compose.aurora.extra.yml')
            );
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

        ConsoleLogger::log_trace('Using compose files: ' . implode(', ', $files), 'InteractsWithDockerCommands');
        ConsoleLogger::log_trace('Using profiles: ' . implode(', ', $profiles), 'InteractsWithDockerCommands');

        $command = "docker compose $file_str $profile_str -p $name $command";
        ConsoleLogger::log_trace('Using compose command: ' . $command, 'InteractsWithDockerCommands');

        return trim($command);
    }

    /**
     * Generate the Docker build command
     * @param string $docker_tag
     * @param string $path
     * @return string
     */
    private function generateDockerBuildCommand(string $docker_tag, string $path): string
    {
        $path = path_resolve($path);
        $tag_base = explode(':', $docker_tag)[0];
        $tag_latest = $tag_base . ':latest';
        $command = "docker build -t $tag_latest -t $docker_tag $path";
        ConsoleLogger::log_trace("Docker command: $command", 'InteractsWithDockerCommands');
        return $command;
    }

    /**
     * Generate docker save command
     * @param string $docker_tag
     * @param string $path
     * @return string
     */
    private function generateDockerSaveCommand(string $docker_tag, string $path): string
    {
        $path = path_resolve($path);
        $tag_base = explode(':', $docker_tag)[0];
        $tag_latest = $tag_base . ':latest';
        $command = "docker save -o $path $docker_tag $tag_latest";
        ConsoleLogger::log_trace("Docker command: $command", 'InteractsWithDockerCommands');
        return $command;
    }

    /**
     * Generate the Docker tag command, to tag the image with the latest registry's tag
     * @param string $docker_tag
     * @return string
     */
    private function generateDockerTagCommand(string $docker_tag)
    {
        $tag_base = explode(':', $docker_tag)[0];
        $tag_latest = $tag_base . ':latest';

        $registry_url = config('aurora.docker_registry_url');
        $registry_namespace = config('aurora.docker_registry_namespace');

        $command = "docker tag $docker_tag $registry_url/$registry_namespace/$tag_latest && " .
            "docker tag $docker_tag $registry_url/$registry_namespace/$docker_tag";

        ConsoleLogger::log_trace("Docker command: $command", 'InteractsWithDockerCommands');
        return $command;
    }

    /**
     * Generate the Docker push command
     * @param string $docker_tag
     * @return string
     */
    private function generateDockerPushCommand(string $docker_tag): string
    {
        $tag_base = explode(':', $docker_tag)[0];
        $tag_latest = $tag_base . ':latest';

        $registry_url = config('aurora.docker_registry_url');
        $registry_namespace = config('aurora.docker_registry_namespace');

        $tag_registry = "$registry_url/$registry_namespace/$docker_tag";
        $tag_registry_latest = "$registry_url/$registry_namespace/$tag_latest";

        $command = "docker push $tag_registry && docker push $tag_registry_latest";
        ConsoleLogger::log_trace("Docker command: $command", 'InteractsWithDockerCommands');
        return $command;
    }

    /**
     * Generate the Docker login command
     * @return string
     */
    private function generateDockerLoginCommand(): string
    {
        $username = config('aurora.docker_registry_username');
        $password = config('aurora.docker_registry_password');
        $registry_url = config('aurora.docker_registry_url');
        $command = "docker login -u '$username' -p '$password' $registry_url";
        $command_redacted = "docker login -u '$username' -p '******' $registry_url";
        ConsoleLogger::log_trace("Docker command: $command_redacted", 'InteractsWithDockerCommands');
        return $command;
    }
}
