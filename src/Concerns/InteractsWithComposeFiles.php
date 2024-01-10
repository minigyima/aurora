<?php

namespace Minigyima\Aurora\Concerns;

use Symfony\Component\Yaml\Yaml;

/**
 * InteractsWithComposeFiles - Trait for interacting with docker-compose files
 * @package Minigyima\Aurora\Traits
 */
trait InteractsWithComposeFiles
{
    /**
     * Parse the current docker-compose file
     * @return array
     */
    private static function parseComposeFile(): array
    {
        $contents = self::getCurrentComposeFileContents();

        return Yaml::parse($contents);
    }

    /**
     * Update the current docker-compose file
     * @param array $contents
     * @return void
     */
    private static function getCurrentComposeFileContents(): string
    {
        return file_get_contents(self::getCurrentComposeFile());
    }

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

    /**
     * Update the current docker-compose file
     * @param string $contents
     * @return void
     */
    private static function updateComposeFile(string $contents): void
    {
        file_put_contents(self::getCurrentComposeFile(), $contents);
    }
}
