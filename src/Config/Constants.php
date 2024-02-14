<?php

namespace Minigyima\Aurora\Config;

class Constants
{
    /**
     * Path to the aurora storage directory
     */
    public const AURORA_STORAGE_PATH = 'storage/aurora';

    /**
     * Aurora build path
     */
    public const AURORA_BUILD_PATH = self::AURORA_STORAGE_PATH . '/build';

    /**
     * Temp path for building the production docker image
     */
    public const AURORA_TEMP_PATH = self::AURORA_STORAGE_PATH . '/temp';

    /**
     * Path to the aurora docker storage directory
     */
    public const AURORA_DOCKER_STORAGE_PATH = self::AURORA_STORAGE_PATH . '/mercury';

    /**
     * Path to the aurora docker manifest file
     */
    public const AURORA_MANIFEST_PATH = self::AURORA_STORAGE_PATH . '/manifest.json';

    /**
     * Path to the aurora config file
     */
    public const string CONFIG_FILE_PATH = self::AURORA_STORAGE_PATH . 'config.json';

    /**
     * Path to the aurora soketi config file
     */
    public const string SOKETI_CONFIG_FILE_PATH = self::AURORA_STORAGE_PATH . '/soketi/config.json';

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

    /**
     * Path to the mercury container file
     */
    public const MERCURY_MARKER = '/mercury.container';

    /**
     * Path to the production marker file
     */
    public const PRODUCTION_MARKER = '/is_prod';

    /**
     * Prefix mercury uses when locking the first run
     */
    public const LOCK_PREFIX = 'mercury-';

    /**
     * Path to the Debug (FPM) boot script
     */
    public const DEBUG_SCRIPT_PATH = '/mercury_debug.sh';

    /**
     * Path to the Swoole boot script
     */
    public const SWOOLE_SCRIPT_PATH = '/mercury_swoole.sh';

    /**
     * Path to the kill script
     */
    public const KILL_SCRIPT_PATH = '/mercury_kill.sh';

    /**
     * The current version of Aurora
     */
    public const AURORA_VERSION = '0.0.1';

    /**
     * The current version of Mercury
     */
    public const MERCURY_VERSION = '0.2';

    public const HORIZON_REDIS_MAX_RETRIES = 5;

    /**
     * The scripts that get injected into composer.json
     */
    public const array INJECTED_SCRIPTS = [
        'shell' => ["Composer\\Config::disableProcessTimeout", '@php artisan aurora:shell'],
        'start' => [
            "Composer\\Config::disableProcessTimeout",
            '@php artisan config:clear',
            '@php artisan aurora:start'
        ],
        'stop' => ["Composer\\Config::disableProcessTimeout", '@php artisan aurora:stop'],
        'build' => [
            "Composer\\Config::disableProcessTimeout",
            '@php artisan config:clear',
            '@php artisan aurora:build'
        ],
    ];

    public const array IGNORED_FILES = [
        '/.pnpm-store',
        'docker-compose.override.yaml',
        'docker-compose.override.aurora.bak',
        '.env.aurora.bak',
        Constants::AURORA_STORAGE_PATH,
    ];
}
