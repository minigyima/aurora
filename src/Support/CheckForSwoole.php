<?php

namespace Minigyima\Aurora\Support;

use Illuminate\Support\Facades\App;
use Swoole\Http\Server;

/**
 * CheckForSwoole - Utility class for checking if Swoole is loaded
 * @package Minigyima\Aurora\Support
 */
class CheckForSwoole
{
    /**
     * Name of the extension to check for
     * @var string
     */
    private static string $extension = 'openswoole';

    /**
     * Check loaded extensions
     * @return bool
     */
    public static function check(): bool
    {
        return extension_loaded(self::$extension) && ! App::runningInConsole() && app()->bound(Server::class);
    }
}
