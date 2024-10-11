<?php

namespace Minigyima\Aurora\Commands;

use Illuminate\Console\Command;
use Minigyima\Aurora\Concerns\VerifiesEnvironment;
use Minigyima\Aurora\Support\ConsoleLogger;
use Minigyima\Aurora\Support\StrClean;

use function Minigyima\Aurora\Support\unlink_if_exists;

/**
 * PrepareProductionCommand - Command for preparing the production environment
 *
 * @package Minigyima\Aurora\Commands
 */
class PrepareProductionCommand extends Command
{
    use VerifiesEnvironment;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mercury:prepare-production';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start the Aurora framework';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (!self::runningInMercury()) {
            ConsoleLogger::log_error(
                'This command can only be run in the Mercury environment',
                'PrepareProductionCommand'
            );
            return self::FAILURE;
        }

        if (!self::runningInProduction()) {
            ConsoleLogger::log_error(
                'This command can only be run in the production environment',
                'PrepareProductionCommand'
            );
            return self::FAILURE;
        }

        ConsoleLogger::log_info('Preparing production environment...', 'PrepareProductionCommand');

        ConsoleLogger::log_trace('Checking if Cron is enabled...');
        if (!config('aurora.scheduler_enabled')) {
            ConsoleLogger::log_warning('Cron is not enabled. Removing config...', 'PrepareProductionCommand');
            unlink_if_exists('/etc/supervisor/conf.d/supervisord-20-cron.conf');
        }

        ConsoleLogger::log_trace('Checking if Horizon is enabled...', 'PrepareProductionCommand');
        if (!config('aurora.queue_enabled')) {
            ConsoleLogger::log_warning('Horizon is not enabled. Removing config...', 'PrepareProductionCommand');
            unlink_if_exists('/etc/supervisor/conf.d/supervisord-30-queue.conf');
        }

        ConsoleLogger::log_trace('Checking if Redis is enabled...', 'PrepareProductionCommand');
        if (!config('aurora.redis_enabled')) {
            ConsoleLogger::log_warning('Redis is not enabled. Removing config...', 'PrepareProductionCommand');
            unlink_if_exists('/etc/supervisor/conf.d/supervisord-40-redis.conf');
        }

        ConsoleLogger::log_trace('Checking if Postgres is enabled...', 'PrepareProductionCommand');
        if (!config('aurora.database_enabled')) {
            ConsoleLogger::log_warning('Postgres is not enabled. Removing config...', 'PrepareProductionCommand');
            unlink_if_exists('/etc/supervisor/conf.d/supervisord-50-postgres.conf');
        }

        ConsoleLogger::log_trace('Checking if Soketi is enabled...', 'PrepareProductionCommand');
        if (!config('aurora.sockets_enabled')) {
            ConsoleLogger::log_warning('Soketi is not enabled. Removing config...', 'PrepareProductionCommand');
            unlink_if_exists('/etc/supervisor/conf.d/supervisord-70-soketi.conf');
        } else {
            ConsoleLogger::log_info('Soketi is enabled. Configuring...', 'PrepareProductionCommand');
            $app_name = str_replace([' ', '-'], ['_', '_'], StrClean::clean(strtolower(config('app.name'))));
            $app_id = config('broadcasting.connections.pusher.app_id');
            $app_key = config('broadcasting.connections.pusher.key');
            $app_secret = config('broadcasting.connections.pusher.secret');
            $soketi_port = config('broadcasting.connections.pusher.options.port');

            $soketi_config = [
                'debug' => config('aurora.soketi_debug'),
                'port' => $soketi_port,
                'metrics' => [
                    'enabled' => true,
                    'port' => 9601,
                    'driver' => 'prometheus',
                    'prometheus' => [
                        'prefix' => "{$app_name}_"
                    ]
                ],
                'appManager.array.apps' => [
                    [
                        'id' => $app_id,
                        'key' => $app_key,
                        'secret' => $app_secret,
                        'webhooks' => []
                    ]
                ]
            ];

            ConsoleLogger::log_trace('Writing Soketi config to /etc/soketi-config.json', 'PrepareProductionCommand');
            $soketi_config_json = json_encode($soketi_config, JSON_PRETTY_PRINT);
            file_put_contents('/etc/soketi-config.json', $soketi_config_json);
            ConsoleLogger::log_success('Soketi config written to /etc/soketi-config.json', 'PrepareProductionCommand');

            ConsoleLogger::log_trace('Configuring Nginx for Soketi port...', 'PrepareProductionCommand');
            $nginx_config = file_get_contents('/etc/nginx/nginx.conf');
            $replace_string = 'http://127.0.0.1:6001';
            $replace_with = "http://127.0.0.1:$soketi_port";
            $nginx_config = str_replace($replace_string, $replace_with, $nginx_config);

            ConsoleLogger::log_trace('Configuring the UNIX socket...', 'PrepareProductionCommand');
            $sock_replace = "unix:/tmp/aurora/aurora.sock;";
            $sock_name = config('aurora.unix_socket_name');
            $sock_with = "unix:/tmp/aurora/$sock_name;";
            $nginx_config = str_replace($sock_replace, $sock_with, $nginx_config);

            file_put_contents('/etc/nginx/nginx.conf', $nginx_config);
            ConsoleLogger::log_success('Nginx configured', 'PrepareProductionCommand');
        }

        ConsoleLogger::log_success('Production environment prepared', 'PrepareProductionCommand');

        return self::SUCCESS;
    }
}
