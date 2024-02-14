<?php

namespace Minigyima\Aurora\Commands;

use Illuminate\Console\Command;
use Minigyima\Aurora\Concerns\VerifiesEnvironment;
use Minigyima\Aurora\Support\ConsoleLogger;

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
        if (! self::runningInMercury()) {
            ConsoleLogger::log_error('This command can only be run in the Mercury environment');
            return self::FAILURE;
        }

        if (! self::runningInProduction()) {
            ConsoleLogger::log_error('This command can only be run in the production environment');
            return self::FAILURE;
        }

        ConsoleLogger::log_info('Preparing production environment...', 'PrepareProductionCommand');

        ConsoleLogger::log_trace('Checking if Cron is enabled...');
        if (! config('aurora.scheduler_enabled')) {
            ConsoleLogger::log_warning('Cron is not enabled. Removing config...');
            unlink('/etc/supervisor/conf.d/supervisord-20-cron.conf');
        }

        ConsoleLogger::log_trace('Checking if Horizon is enabled...');
        if (! config('aurora.queue_enabled')) {
            ConsoleLogger::log_warning('Horizon is not enabled. Removing config...');
            unlink('/etc/supervisor/conf.d/supervisord-30-queue.conf');
        }

        ConsoleLogger::log_trace('Checking if Redis is enabled...');
        if (! config('aurora.redis_enabled')) {
            ConsoleLogger::log_warning('Redis is not enabled. Removing config...');
            unlink('/etc/supervisor/conf.d/supervisord-40-redis.conf');
        }

        ConsoleLogger::log_trace('Checking if Postgres is enabled...');
        if (! config('aurora.database_enabled')) {
            ConsoleLogger::log_warning('Postgres is not enabled. Removing config...');
            unlink('/etc/supervisor/conf.d/supervisord-50-postgres.conf');
        }

        ConsoleLogger::log_success('Production environment prepared', 'PrepareProductionCommand');

        return self::SUCCESS;
    }
}
