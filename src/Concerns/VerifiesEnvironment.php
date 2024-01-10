<?php

namespace Minigyima\Aurora\Concerns;

use Minigyima\Aurora\Config\Constants;
use Minigyima\Aurora\Storage\AuroraMarker;

/**
 * VerifiesEnvironment - Trait for verifying the environment that Aurora is running in
 * @package Minigyima\Aurora\Traits
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


}
