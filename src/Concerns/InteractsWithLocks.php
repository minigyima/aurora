<?php

namespace Minigyima\Aurora\Concerns;

use Minigyima\Aurora\Config\Constants;

/**
 * InteractsWithLocks - Trait for interacting with Mercury's locks
 * @package Minigyima\Aurora\Traits
 */
trait InteractsWithLocks
{

    /**
     * Check if the lock is set
     * @return bool
     */
    private static function isLocked(): bool
    {
        $lock_name = Constants::LOCK_PREFIX . config('app.name') . '.lock';
        return file_exists("/root/$lock_name");
    }

    /**
     * Set the lock
     * @return void
     */
    private static function lock(): void
    {
        $lock_name = Constants::LOCK_PREFIX . config('app.name') . '.lock';
        file_put_contents("/root/$lock_name", '');
    }

    /**
     * Remove the lock
     * @return void
     */
    private static function unlock(): void
    {
        $lock_name = Constants::LOCK_PREFIX . config('app.name') . '.lock';
        unlink("/root/$lock_name");
    }
}
