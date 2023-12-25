<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Frontend enabled
    |--------------------------------------------------------------------------
    |
    | This option controls whether the frontend is enabled or not
    |
    */
    'frontend_enabled' => true,

    /*
    |--------------------------------------------------------------------------
    | Warden enabled
    |--------------------------------------------------------------------------
    |
    | This option controls whether Warden is enabled or not
    |
    */
    'warden_enabled' => true,

    /*
    |--------------------------------------------------------------------------
    | Sockets enabled
    |--------------------------------------------------------------------------
    |
    | This option controls whether Sockets are enabled or not (Soketi)
    |
    */
    'sockets_enabled' => true,

    /*
    |--------------------------------------------------------------------------
    | Auth enabled
    |--------------------------------------------------------------------------
    |
    | This option controls whether Mercury's built in Auth is enabled or not
    |
    */
    'auth_enabled' => true,

    /*
    |--------------------------------------------------------------------------
    | ConfigManager enabled
    |--------------------------------------------------------------------------
    |
    | This option controls whether Mercury's ConfigManager is enabled or not
    |
    */
    'config_manager_enabled' => true,

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
    | This option controls which model Mercury will use to manage its config
    |
    */
    'config_model' => Minigyima\Aurora\Config\ConfigManager\Models\BaseConfig::class,
];
