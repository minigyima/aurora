<?php

namespace Minigyima\Aurora\Concerns;

use Minigyima\Aurora\Config\Constants;

/**
 * InteractsWithDockerManifest - Trait for interacting with the docker manifest
 * @package Minigyima\Aurora\Traits
 */
trait InteractsWithDockerManifest
{
    /**
     * Check if the docker manifest has changed
     * @return bool
     */
    private function compareWithNew(): bool
    {
        $manifest = $this->makeManifest();
        return $this->compareManifests($manifest);
    }

    /**
     * Make a manifest of the current docker files
     * @return array
     */
    private function makeManifest(): array
    {
        $isPublished = false;
        if (file_exists(base_path('docker'))) {
            $files = scandir(base_path('docker/app'));
            $isPublished = true;
        } else {
            $files = scandir(__DIR__ . '/../Stubs/docker/app');
        }

        $files = array_filter($files, function ($file) {
            return ! in_array($file, ['.', '..', '.gitkeep', 'gitignore']);
        });

        $manifest = [];
        foreach ($files as $file) {
            $path = $isPublished ? base_path('docker/app/' . $file) : __DIR__ . '/../Stubs/docker/app/' . $file;
            $manifest[$file] = hash_file('sha256', $path);
        }

        return $manifest;
    }

    /**
     * Compare the current manifest with the stored manifest
     * @param array $manifest
     * @return bool
     */
    private function compareManifests(array $manifest): bool
    {
        $currentManifest = $this->readManifest();
        if (count($currentManifest) !== count($manifest)) {
            return false;
        }

        foreach ($manifest as $filename => $hash) {
            if (! array_key_exists($filename, $currentManifest)) {
                return false;
            }

            if ($currentManifest[$filename] !== $hash) {
                return false;
            }
        }

        return true;
    }

    /**
     * Read the current manifest
     * @return array
     */
    private function readManifest(): array
    {
        $manifestPath = base_path(Constants::AURORA_MANIFEST_PATH);
        if (! file_exists($manifestPath)) {
            $this->writeManifest();
        }

        return json_decode(file_get_contents($manifestPath), true);
    }

    /**
     * Write the current manifest
     * @return void
     */
    private function writeManifest()
    {
        $manifest = $this->makeManifest();
        $manifestPath = base_path(Constants::AURORA_MANIFEST_PATH);
        file_put_contents($manifestPath, json_encode($manifest));
    }
}
