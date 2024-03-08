<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Frontend enabled
    |--------------------------------------------------------------------------
    |
    | This option controls whether the frontend compiler is enabled or not
    |
    */
    'frontend_enabled' => true,

    /*
    |--------------------------------------------------------------------------
    | Frontend rootDir
    |--------------------------------------------------------------------------
    |
    | This option controls the aurora directory of the frontend compiler
    |
    */
    'frontend_root_dir' => base_path('frontend'),

    /*
    |--------------------------------------------------------------------------
    | Warden enabled
    |--------------------------------------------------------------------------
    |
    | This option controls whether Warden is enabled or not
    |
    */
    'warden_enabled' => false,

    /*
    |--------------------------------------------------------------------------
    | Sockets enabled
    |--------------------------------------------------------------------------
    |
    | This option controls whether Sockets are enabled or not (Soketi)
    | WARNING: This option becomes ineffective after the 'aurora-docker' assets are published
    |
    */
    'sockets_enabled' => env('AURORA_SOCKETS_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Sockets - Debug mode
    |--------------------------------------------------------------------------
    |
    | This option controls whether Soketi is running in debug mode or not
    |
    */
    'soketi_debug' => env('AURORA_SOKETI_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Redis enabled
    |--------------------------------------------------------------------------
    |
    | This option controls whether the Redis container is enabled or not
    | WARNING: This option becomes ineffective after the 'aurora-docker' assets are published
    |
    */
    'redis_enabled' => env('AURORA_REDIS_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Queue enabled
    |--------------------------------------------------------------------------
    |
    | This option controls whether the Horizon queue worker container is enabled or not
    | WARNING: This option becomes ineffective after the 'aurora-docker' assets are published
    |
    */
    'queue_enabled' => env('AURORA_QUEUE_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Database enabled
    |--------------------------------------------------------------------------
    |
    | This option controls whether the Postgres database container is enabled or not
    | WARNING: This option becomes ineffective after the 'aurora-docker' assets are published
    |
    */
    'database_enabled' => env('AURORA_DATABASE_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Scheduler enabled
    |--------------------------------------------------------------------------
    |
    | This option controls whether the Cron container is enabled or not
    | WARNING: This option becomes ineffective after the 'aurora-docker' assets are published
    |
    */
    'scheduler_enabled' => env('AURORA_SCHEDULER_ENABLED', true),

    /*
     |--------------------------------------------------------------------------
     | Refresh Token Expiration
     |--------------------------------------------------------------------------
     |
     | This option controls how long a refresh token is valid for
     |
     */
    'refresh_token_expiration' => 60 * 24 * 30,

    /*
    |--------------------------------------------------------------------------
    | ConfigManager enabled
    |--------------------------------------------------------------------------
    |
    | This option controls whether Aurora's ConfigManager is enabled or not
    |
    */
    'config_manager_enabled' => true,

    /*
    |--------------------------------------------------------------------------
    | Automatically register exception handler
    |--------------------------------------------------------------------------
    |
    | This option controls whether Aurora's exception handler is automatically registered or not
    |
    */
    'automatically_register_exception_handler' => true,

    /*
    |--------------------------------------------------------------------------
    | Debug mode
    |--------------------------------------------------------------------------
    |
    | This option controls whether Mercury is in debug mode or not
    | Debug mode will switch Mercury's docker runtime (Mercury) to run using FPM instead of Swoole
    |
    */
    'debug_mode' => env('AURORA_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Config Model
    |--------------------------------------------------------------------------
    |
    | This option controls which model Aurora will use to manage its config
    |
    */
    'config_model' => Minigyima\Aurora\Config\ConfigManager\Models\BaseConfig::class,

];
