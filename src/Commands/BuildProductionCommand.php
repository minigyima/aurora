<?php

namespace Minigyima\Aurora\Commands;

use Illuminate\Console\Command;
use Minigyima\Aurora\Concerns\InteractsWithDockerManifest;
use Minigyima\Aurora\Concerns\TestsForDocker;
use Minigyima\Aurora\Concerns\VerifiesEnvironment;
use Minigyima\Aurora\Config\Constants;
use Minigyima\Aurora\Support\ConsoleLogger;
use Minigyima\Aurora\Support\GitHelper;
use function Laravel\Prompts\confirm;
use function Minigyima\Aurora\Support\rrmdir;
use function Minigyima\Aurora\Support\rsync_repo_ignore;

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
    protected $signature = 'aurora:build-production';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start the Aurora framework';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $app_name = config('app.name');
        ConsoleLogger::log_info('Building production for ' . $app_name, 'BuildProductionCommand');
        ConsoleLogger::log_trace('Running pre-build checks...', 'BuildProductionCommand');
        if (self::runningInMercury()) {
            ConsoleLogger::log_error('This command is not available in Mercury', 'BuildProductionCommand');
            return self::FAILURE;
        }

        if (! self::testForGit()) {
            ConsoleLogger::log_error('Git is not installed on your system', 'BuildProductionCommand');
            return self::FAILURE;
        }

        ConsoleLogger::log_success('Pre-build checks passed!', 'BuildProductionCommand');

        $base_path = base_path();

        if (! GitHelper::isRepo(base_path())) {
            ConsoleLogger::log_warning('This project is not a git repository', 'BuildProductionCommand');
            $choice = confirm('Would you like to initialize a git repository?');
            if ($choice) {
                ConsoleLogger::log_info('Initializing git repository...', 'BuildProductionCommand');
                GitHelper::init(base_path());
                ConsoleLogger::log_success('Git repository initialized', 'BuildProductionCommand');
            } else {
                ConsoleLogger::log_error('Git repository not initialized', 'BuildProductionCommand');
                return self::FAILURE;
            }
        }

        if (GitHelper::isDirty(base_path())) {
            ConsoleLogger::log_warning('There are uncommitted changes in the repository', 'BuildProductionCommand');
            $choice = confirm('Would you like to continue?');
            if (! $choice) {
                ConsoleLogger::log_error(
                    'Build cancelled. Please commit any uncommited changes.',
                    'BuildProductionCommand'
                );
                return self::FAILURE;
            }
        }

        ConsoleLogger::log_info('Building production...', 'BuildProductionCommand');
        ConsoleLogger::log_info('Making a copy of your source code...', 'BuildProductionCommand');
        $temp_path = base_path(Constants::AURORA_TEMP_PATH);
        if (file_exists($temp_path)) {
            ConsoleLogger::log_warning('Cleaning up temporary directory...', 'BuildProductionCommand');
            rrmdir($temp_path);
        }

        ConsoleLogger::log_trace('Copying source code to temporary directory...', 'BuildProductionCommand');
        mkdir($temp_path);
        rsync_repo_ignore($base_path, $temp_path);

        return self::SUCCESS;
    }
}
