<?php

namespace Minigyima\Aurora\Traits;

use Minigyima\Aurora\Config\Constants;
use Minigyima\Aurora\Storage\AuroraMarker;

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
