<?php

namespace Minigyima\Aurora\Concerns;

use Minigyima\Aurora\Config\Constants;
use Minigyima\Aurora\Storage\AuroraMarker;

/**
 * VerifiesEnvironment - Trait for verifying the environment that Aurora is running in
 * @package Minigyima\Aurora\Concernns
 */
trait VerifiesEnvironment
{
    /**
     * Check if we're running in Mercury
     *
     * @return bool
     */
    private static function runningInMercury(): bool
    {
        return file_exists(Constants::MERCURY_MARKER);
    }

    /**
     * Check if we're running in Production
     *
     * @return bool
     */
    private static function runningInProduction(): bool
    {
        return file_exists(Constants::PRODUCTION_MARKER);
    }

    /**
     * Check if this is the first run
     *
     * @return bool
     */
    private static function isFirstRun(): bool
    {
        return ! (
            class_exists(
                "Minigyima\\Aurora\\Storage\\AuroraMarker"
            ) &&
            AuroraMarker::PATCHED
        );
    }

    /**
     * Check if git is installed
     *
     * @return bool
     */
    private static function testForGit(): bool
    {
        $test_method = (false === stripos(PHP_OS, 'winnt')) ? 'which' : 'where';
        return ! (null === shell_exec("$test_method git"));
    }


}
