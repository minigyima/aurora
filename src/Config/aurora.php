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
    | This option controls the root directory of the frontend compiler
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
    | This option controls whether Mercury's ConfigManager is enabled or not
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
    | This option controls which model Mercury will use to manage its config
    |
    */
    'config_model' => Minigyima\Aurora\Config\ConfigManager\Models\BaseConfig::class,
];
