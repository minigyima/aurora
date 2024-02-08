<?php

namespace Minigyima\Aurora\Concerns;

use Minigyima\Aurora\Config\Constants;
use Minigyima\Aurora\Support\ConsoleLogger;

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
            ConsoleLogger::log_warning('Using published docker files', 'InteractsWithDockerManifest::makeManifest');
            $isPublished = true;
        } else {
            $files = scandir(__DIR__ . '/../Stubs/docker/app');
        }

        return $this->scan_files_hash($files, $isPublished);
    }

    /**
     * Scan the files and hash them
     * @param array $files
     * @param bool $isPublished
     * @return array
     */
    private function scan_files_hash(array $files, bool $isPublished = false, bool $is_subdir = false): array
    {
        $files = $this->filter_files($files);
        $manifest = [];
        foreach ($files as $file) {
            $path = $isPublished ? base_path('docker/app/' . $file) : __DIR__ . '/../Stubs/docker/app/' . $file;
            if (is_dir($path)) {
                $files = $this->filter_files(scandir($path));
                $files = array_map(fn($current_file) => $file . '/' . $current_file, $files);
                $manifest = array_merge($manifest, $this->scan_files_hash($files, $isPublished, true));
                continue;
            }
            $manifest[$file] = hash_file('sha256', $path);
        }

        return $manifest;
    }

    private function filter_files(array $files): array
    {
        return array_filter($files, function ($file) {
            return ! in_array($file, ['.', '..', '.gitkeep', 'gitignore']);
        });
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
