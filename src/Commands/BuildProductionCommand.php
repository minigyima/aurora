<?php

namespace Minigyima\Aurora\Commands;

use Illuminate\Console\Command;
use Minigyima\Aurora\Concerns\InteractsWithDockerManifest;
use Minigyima\Aurora\Concerns\TestsForDocker;
use Minigyima\Aurora\Concerns\VerifiesEnvironment;
use Minigyima\Aurora\Config\Constants;
use Minigyima\Aurora\Errors\AuroraException;
use Minigyima\Aurora\Services\Aurora;
use Minigyima\Aurora\Support\ConsoleLogger;
use function Laravel\Prompts\confirm;

/**
 * BuildProductionCommand - Command for building the Aurora framework
 *
 * @package Minigyima\Aurora\Commands
 */
class BuildProductionCommand extends Command
{
    use InteractsWithDockerManifest, TestsForDocker, VerifiesEnvironment;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'aurora:build-production {--yes : Skip confirmation prompt} {--export : Export the production build} {--directory= : The directory to export the production build to} {--push : Push the image to the registry specified in the config}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Build the Aurora framework for production';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        ConsoleLogger::log_info('Clearing configuration cache...', 'BuildProductionCommand');
        $this->call('config:cache');

        $yes = $this->option('yes');
        $export = $this->option('export');
        $directory = $this->option('directory');
        $push = $this->option('push');

        $app_name = config('app.name');
        ConsoleLogger::log_info('Building production for ' . $app_name, 'BuildProductionCommand');
        ConsoleLogger::log_trace('Running pre-build checks...', 'BuildProductionCommand');
        if (self::runningInMercury()) {
            ConsoleLogger::log_error('This command is not available in Mercury', 'BuildProductionCommand');
            return self::FAILURE;
        }

        ConsoleLogger::log_info('Checking manifest for changes...', 'BuildProductionCommand');
        if (! $this->compareWithNew()) {
            ConsoleLogger::log_warning('Changes detected in the manifest. Rebuilding...', 'BuildProductionCommand');
            $result = $this->call('aurora:build');
            if ($result !== self::SUCCESS) {
                ConsoleLogger::log_error('Build failed. Please check the logs and try again', 'BuildProductionCommand');
                return self::FAILURE;
            }
            $this->writeManifest();
        }

        ConsoleLogger::log_info('Enabled features: ', 'BuildProductionCommand');
        $this->table([
            'Sockets',
            'Scheduler',
            'Queue',
            'Database',
            'Redis'
        ], [
            [
                config('aurora.sockets_enabled') ? 'Enabled' : 'Disabled',
                config('aurora.scheduler_enabled') ? 'Enabled' : 'Disabled',
                config('aurora.queue_enabled') ? 'Enabled' : 'Disabled',
                config('aurora.database_enabled') ? 'Enabled' : 'Disabled',
                config('aurora.redis_enabled') ? 'Enabled' : 'Disabled'
            ]
        ]);

        if (! ($yes || confirm('Is this correct?'))) {
            ConsoleLogger::log_error(
                "Please update the configuration and try again. You may need to run 'php artisan config:clear'",
                'BuildProductionCommand'
            );
            return self::FAILURE;
        }

        $aurora = Aurora::use();

        try {
            $aurora->buildProduction(
                export: $export,
                export_dir: $directory ?? Constants::AURORA_BUILD_PATH,
                yes: $yes,
                push: $push
            );
        } catch (AuroraException $e) {
            return self::FAILURE;
        }

        $this->info('Done. Have a *GREAT* day!');

        return self::SUCCESS;
    }
}
