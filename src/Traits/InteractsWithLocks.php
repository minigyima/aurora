<?php

namespace Minigyima\Aurora\Traits;

use Minigyima\Aurora\Config\Constants;

trait InteractsWithLocks
{

    private static function isLocked(): bool
    {
        $lock_name = Constants::LOCK_PREFIX . config('app.name') . '.lock';
        return file_exists("/root/$lock_name");
    }

    private static function lock(): void
    {
        $lock_name = Constants::LOCK_PREFIX . config('app.name') . '.lock';
        file_put_contents("/root/$lock_name", '');
    }

    private static function unlock(): void
    {
        $lock_name = Constants::LOCK_PREFIX . config('app.name') . '.lock';
        unlink("/root/$lock_name");
    }
}
