<?php

namespace Minigyima\Aurora\Concerns;

/**
 * ResolvesPaths - Trait for resolving paths
 * @package Minigyima\Aurora\Concerns
 */
trait ResolvesPaths
{
    /**
     * Resolve a path
     * @param string ...$paths
     * @return string
     */
    public static function path_resolve(string ...$paths): string
    {
        $path_array = array_map(fn($path) => rtrim($path, DIRECTORY_SEPARATOR), $paths);
        $path = implode(DIRECTORY_SEPARATOR, $path_array);
        $path_clean = str_replace(' ', '\ ', $path);
        $resolved = realpath($path_clean);
        return $resolved === false ? $path_clean : $resolved;
    }
}
