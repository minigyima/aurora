<?php

namespace Minigyima\Aurora\Concerns\Docker;

use function Minigyima\Aurora\Support\path_resolve;

/**
 * InteractsWithComposeFiles - Trait for interacting with docker-compose files
 * @package Minigyima\Aurora\Traits
 */
trait InteractsWithComposeFiles
{
    /**
     * Get the path of the currently used docker-compose file
     * @return string
     */
    private static function getCurrentComposeFile(): string
    {
        if (file_exists(base_path('docker-compose.yml'))) {
            $path = path_resolve(
                base_path('docker-compose.yml')
            );
        } else {
            if (file_exists(base_path('docker-compose.yaml'))) {
                $path = path_resolve(
                    base_path('docker-compose.yaml')
                );
            } else {
                $path = path_resolve(__DIR__, '/../../Stubs/docker-compose.yml');
            }
        }

        return $path;
    }
}
