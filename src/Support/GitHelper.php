<?php

namespace Minigyima\Aurora\Support;

use Minigyima\Aurora\Concerns\ResolvesPaths;
use Symfony\Component\Process\Process;

/**
 * Helper class for git operations
 * @package Minigyima\Aurora\Support
 * @internal
 */
class GitHelper
{
    use ResolvesPaths;

    /**
     * Check if a given path is a git repository
     * @param string $repo_path
     * @return bool
     */
    public static function isRepo(string $repo_path): bool
    {
        $path = self::path_resolve($repo_path);
        $process = Process::fromShellCommandline("git -C $path rev-parse");
        return 0 === $process->run();
    }

    /**
     * Check if a given git repository is dirty
     * @param string $repo_path
     * @return bool
     */
    public static function isDirty(string $repo_path): bool
    {
        $path = self::path_resolve($repo_path);
        return '' != shell_exec("git -C $path status --porcelain");
    }

    /**
     * Get the list of files ignored by git
     * @param string $repo_path
     * @return array
     */
    public static function getIgnoredFiles(string $repo_path): array
    {
        $path = self::path_resolve($repo_path);
        return array_map(fn($item) => rtrim($item, DIRECTORY_SEPARATOR),
            explode(
                PHP_EOL,
                trim(shell_exec("git -C $path ls-files --exclude-standard -oi --directory"))
            ));
    }

    /**
     * Initialize a git repository
     * @param string $repo_path
     * @return bool
     */
    public static function init(string $repo_path): bool
    {
        $path = self::path_resolve($repo_path);
        return 0 === (int) shell_exec("git -C $path init");
    }

    /**
     * Get the current git branch
     * @param string $repo_path
     * @return string
     */
    public static function getCurrentBranch(string $repo_path): string
    {
        $path = self::path_resolve($repo_path);
        return trim(shell_exec("git -C $path rev-parse --abbrev-ref HEAD"));
    }

    /**
     * Get the current git commit hash
     * @param string $repo_path
     * @return string
     */
    public static function getCurrentCommitHash(string $repo_path): string
    {
        $path = self::path_resolve($repo_path);
        return trim(shell_exec("git -C $path rev-parse HEAD"));
    }
}
