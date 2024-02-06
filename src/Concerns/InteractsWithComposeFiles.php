<?php

namespace Minigyima\Aurora\Concerns;

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
            $path = base_path('docker-compose.yml');
        } else {
            if (file_exists(base_path('docker-compose.yaml'))) {
                $path = base_path('docker-compose.yaml');
            } else {
                $path = __DIR__ . '/../Stubs/docker-compose.yml';
            }
        }

        return $path;
    }
}
