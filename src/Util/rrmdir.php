<?php

namespace Minigyima\Aurora\Util;

# Adapted from StackOverflow
# http://stackoverflow.com/a/3352564/283851
# https://gist.github.com/XzaR90/48c6b615be12fa765898
# Forked from https://gist.github.com/mindplay-dk/a4aad91f5a4f1283a5e2

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

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

    $files = new RecursiveIteratorIterator
    (
        new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($files as $fileInfo) {
        if ($fileInfo->isDir()) {
            if (rrmdir($fileInfo->getRealPath()) === false) {
                return false;
            }
        } else {
            if (unlink($fileInfo->getRealPath()) === false) {
                return false;
            }
        }
    }

    if ($removeOnlyChildren === false) {
        return rmdir($source);
    }

    return true;
}
