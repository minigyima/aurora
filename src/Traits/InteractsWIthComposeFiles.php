<?php

namespace Minigyima\Aurora\Traits;

use Symfony\Component\Yaml\Yaml;

trait InteractsWIthComposeFiles
{
    private static function parseComposeFile(): array
    {
        $contents = self::getCurrentComposeFileContents();

        return Yaml::parse($contents);
    }

    private static function getCurrentComposeFileContents(): string
    {
        return file_get_contents(self::getCurrentComposeFile());
    }

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

    private static function updateComposeFile(string $contents): void
    {
        file_put_contents(self::getCurrentComposeFile(), $contents);
    }
}
