<?php

namespace Minigyima\Aurora\Config;

class Constants
{
    public const MERCURY_LOGO = 'MERCURY';

    public const MERCURY_GREETING = 'Mercury - Container runtime for Mercury';

    /**
     * Path to the aurora storage directory
     */
    public const AURORA_STORAGE_PATH = 'storage/aurora';

    public const AURORA_DOCKER_STORAGE_PATH = self::AURORA_STORAGE_PATH . '/mercury';

    public const AURORA_MANIFEST_PATH = self::AURORA_STORAGE_PATH . '/manifest.json';

    public const string CONFIG_FILE_PATH = self::AURORA_STORAGE_PATH . 'config.json';

    /**
     * Marker class name
     */
    public const MARKER = 'AuroraMarker';
    /**
     * Namespace for the marker file
     */
    public const MARKER_NAMESPACE = 'Minigyima\Aurora\Storage';
    /**
     * Path to the marker file
     */
    public const MARKER_PATH = self::AURORA_STORAGE_PATH . '/AuroraMarker.php';

    public const MERCURY_MARKER = '/mercury.container';

    public const LOCK_PREFIX = 'mercury-';

    public const DEBUG_SCRIPT_PATH = '/mercury_debug.sh';
    public const SWOOLE_SCRIPT_PATH = '/mercury_swoole.sh';

    public const KILL_SCRIPT_PATH = '/mercury_kill.sh';

    public const array INJECTED_SCRIPTS = [
        'shell' => ["Composer\\Config::disableProcessTimeout", '@php artisan aurora:shell'],
        'start' => ["Composer\\Config::disableProcessTimeout", '@php artisan aurora:start'],
        'stop' => ["Composer\\Config::disableProcessTimeout", '@php artisan aurora:stop'],
        'build' => ["Composer\\Config::disableProcessTimeout", '@php artisan aurora:build'],
    ];
}
