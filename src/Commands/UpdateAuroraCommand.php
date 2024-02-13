<?php

namespace Minigyima\Aurora\Commands;

use Illuminate\Console\Command;
use Minigyima\Aurora\Handlers\PostInstallHandler;

/**
 * UpdateAuroraCommand - Ran every time composer update is called
 * @package Minigyima\Aurora\Commands
 */
class UpdateAuroraCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'aurora:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the project with the latest Aurora changes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating your Aurora project...');
        $this->info('Adding files to .gitignore');
        PostInstallHandler::initGitIgnore();
        $this->info('Aurora has been updated');
        return self::SUCCESS;
    }
}
