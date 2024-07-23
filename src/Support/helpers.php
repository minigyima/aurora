<?php

/**
 * Helpers - Utility functions that get loaded automatically by AuroraServiceProvider
 */

namespace Minigyima\Aurora\Support;

# Adapted from StackOverflow
# http://stackoverflow.com/a/3352564/283851
# https://gist.github.com/XzaR90/48c6b615be12fa765898
# Forked from https://gist.github.com/mindplay-dk/a4aad91f5a4f1283a5e2
use FilesystemIterator;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;
use Minigyima\Aurora\Support\Response\AuroraResponse;
use Minigyima\Aurora\Support\Response\AuroraResponseStatus;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use stdClass;
use Symfony\Component\Process\Process;

/**
 * Resolve a path
 * @param string ...$paths
 * @return string|false
 */
function path_resolve(string ...$paths): string
{
    return GitHelper::path_resolve(...$paths);
}


/**
 * Recursively delete a directory and all of it's contents - e.g.the equivalent of `rm -r` on the command-line.
 * Consistent with `rmdir()` and `unlink()`, an E_WARNING level error will be generated on failure.
 *
 * @param string $source absolute path to directory or file to delete.
 * @param bool $removeOnlyChildren set to true will only remove content inside directory.
 *
 * @return bool true on success; false on failure
 */
function rrmdir(string $source, bool $removeOnlyChildren = false): bool
{
    if (empty($source) || file_exists($source) === false) {
        return false;
    }

    if (is_file($source) || is_link($source)) {
        return unlink($source);
    }

    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($files as $fileInfo) {
        if ($fileInfo->isDir()) {
            if (rrmdir($fileInfo->getRealPath()) === false) {
                return false;
            }
        } else {
            if ($fileInfo->isLink()) {
                if (unlink($fileInfo->getPathName()) === false) {
                    return false;
                }
            } else {
                if (unlink($fileInfo->getRealPath()) === false) {
                    return false;
                }
            }
        }
    }

    if ($removeOnlyChildren === false) {
        return rmdir($source);
    }

    return true;
}

/**
 * Create a new AuroraResponse instance.
 * @param array|stdClass|Jsonable|JsonSerializable|Arrayable|string $data
 * @param int $statusCode
 * @param array $headers
 * @param int $encodingOptions
 * @param bool $json
 * @param AuroraResponseStatus $status
 * @param string $message
 * @return AuroraResponse
 */
function aurora_response(
    array|stdClass|Jsonable|JsonSerializable|Arrayable|string $data = [],
    int                                                       $statusCode = 200,
    array                                                     $headers = [],
    int                                                       $encodingOptions = 0,
    bool                                                      $json = false,
    AuroraResponseStatus                                      $status = AuroraResponseStatus::SUCCESS,
    string                                                    $message = 'Success',
): AuroraResponse {
    return new AuroraResponse($data, $statusCode, $headers, $encodingOptions, $json, $status, $message);
}

/**
 * Rsync a repository while ignoring files that are ignored by git
 * @param string $source
 * @param string $destination
 * @param bool $ignoreGitDir
 * @return int
 */
function rsync_repo_ignore(string $source, string $destination, bool $ignoreGitDir = true): int
{
    $excluded = GitHelper::getIgnoredFiles($source);
    if ($ignoreGitDir) {
        $excluded[] = '.git';
    }

    return rsync($source, $destination, $excluded);
}

/**
 * Run the rsync command
 * @param string $source
 * @param string $destination
 * @param array $excluded_files
 * @return int
 */
function rsync(string $source, string $destination, array $excluded_files = []): int
{
    $source = path_resolve($source);

    $excluded = array_map(fn ($item) => "'" . rtrim($item, DIRECTORY_SEPARATOR) . "'", $excluded_files);

    $excluded = implode(',', $excluded);
    $excluded = "--exclude={{$excluded}}";

    $source = rtrim($source, DIRECTORY_SEPARATOR);

    $command = "rsync -avz --progress $excluded $source/ $destination";

    $process = Process::fromShellCommandline($command);
    $process->setPty(true);
    $process->start(function ($type, $buffer) {
        ConsoleLogger::log_trace($buffer, 'rsync');
    });

    return $process->wait();
}


/**
 * Unlink a file if it exists
 * @param string $filename
 * @param resource|null $context
 * @return bool
 */
function unlink_if_exists(string $filename, $context = null): bool
{
    if (file_exists($filename)) {
        return unlink($filename, $context);
    }
    return true;
}
