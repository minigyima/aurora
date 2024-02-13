<?php

namespace Minigyima\Aurora\Support;

/**
 * Helper class for git operations
 * @package Minigyima\Aurora\Support
 */
class GitHelper
{
    /**
     * Check if a given path is a git repository
     * @param string $repo_path
     * @return bool
     */
    public static function isRepo(string $repo_path): bool
    {
        return 0 === (int) shell_exec("git -C $repo_path rev-parse");
    }

    /**
     * Check if a given git repository is dirty
     * @param string $repo_path
     * @return bool
     */
    public static function isDirty(string $repo_path): bool
    {
        return 0 !== (int) shell_exec("git -C $repo_path status --porcelain");
    }

    /**
     * Get the list of files ignored by git
     * @param string $repo_path
     * @return array
     */
    public static function getIgnoredFiles(string $repo_path): array
    {
        return array_map(fn($item) => rtrim($item, '/'),
            explode(
                "\n",
                trim(shell_exec("git -C $repo_path ls-files --exclude-standard -oi --directory"))
            ));
    }

    /**
     * Initialize a git repository
     * @param string $repo_path
     * @return bool
     */
    public static function init(string $repo_path): bool
    {
        return 0 === (int) shell_exec("git -C $repo_path init");
    }
}
