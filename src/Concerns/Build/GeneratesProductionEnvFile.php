<?php

namespace Minigyima\Aurora\Concerns\Build;

use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Str;
use Minigyima\Aurora\Models\EnvironmentFile;
use Minigyima\Aurora\Support\ConsoleLogger;
use Minigyima\Aurora\Support\StrClean;

/**
 * Trait GeneratesProductionEnvFile - Generates the production environment file
 * @package Minigyima\Aurora\Concerns
 */
trait GeneratesProductionEnvFile
{
    /**
     * Generate the production environment file
     * - Uses the .env file as a template, and fills in the necessary values
     * - The production environment file is .env.production
     * - Utilizes the current Aurora configuration
     * @return void
     * @see EnvironmentFile
     */
    private function generateProductionEnvFile(): void
    {
        copy(base_path('.env'), base_path('.env.production'));

        ConsoleLogger::log_success(
            'Production environment file initialized, patching .env.production...',
            'ProductionEnvFile'
        );

        $new_key = 'base64:' . base64_encode(
                Encrypter::generateKey(config('app.cipher'))
            );

        $env = new EnvironmentFile(base_path('.env.production'));
        $env->set('APP_ENV', 'production');
        $env->set('APP_DEBUG', 'false');
        $env->set('AURORA_DEBUG', false);
        $env->set('APP_KEY', $new_key);

        $app_name_clean = str_replace([' ', '-'], ['_', '_'], StrClean::clean(strtolower(config('app.name'))));

        if (config('aurora.database_enabled')) {
            $db_name = $app_name_clean . '_db';
            $username = $app_name_clean . '_super';
            $password = Str::random(32);

            $env->set('DB_CONNECTION', 'pgsql');
            $env->set('DB_HOST', 'localhost');
            $env->set('DB_PORT', '5432');
            $env->set('DB_DATABASE', $db_name);
            $env->set('DB_USERNAME', $username);
            $env->set('DB_PASSWORD', $password);
        }

        if (config('aurora.redis_enabled')) {
            $env->set('REDIS_HOST', 'localhost');
        }

        if (config('aurora.sockets_enabled')) {
            $app_id = $app_name_clean . '_realtime';
            $app_key = StrClean::clean(Str::random(32));
            $app_secret = StrClean::clean(Str::random(32));

            $env->set('PUSHER_APP_ID', $app_id);
            $env->set('PUSHER_APP_KEY', $app_key);
            $env->set('PUSHER_APP_SECRET', $app_secret);
            $env->set('PUSHER_HOST', 'locahost');
            $env->set('PUSHER_PORT', '6001');
            $env->set('PUSHER_SCHEME', 'http');
        }

        $env->write();
    }
}
