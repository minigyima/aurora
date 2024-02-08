<?php

namespace Minigyima\Aurora\Commands;

use Illuminate\Console\Command;
use Minigyima\Aurora\Concerns\InteractsWithDockerManifest;
use Minigyima\Aurora\Services\Aurora;
use Minigyima\Aurora\Support\ResetTerminal;

/**
 * StartAuroraCommand - Command for starting the Aurora framework
 * - A small cli wrapper around Aurora::start()
 * @package Minigyima\Aurora\Commands
 * @see Aurora::start()
 */
class StartAuroraCommand extends Command
{
    use InteractsWithDockerManifest;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'aurora:start';

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
        $this->info('Starting Aurora...');
        $this->info('Checking manifest for changes...');
        if (! $this->compareWithNew()) {
            $this->warn('Changes detected in the manifest. Rebuilding...');
            $this->call('aurora:build');
            $this->writeManifest();
        }

        pcntl_async_signals(true);
        $aurora = Aurora::use();

        $this->info('Invoking Docker Compose...');
        $process = $aurora->start();
        $process->setPty(true);
        $process->start(function ($type, $buffer) {
            $this->output->write($buffer);
        });

        pcntl_signal(SIGINT, function () use ($process) {
            $process->stop(0);
            //$process->signal(SIGTERM);
            $this->call('aurora:stop');
        });

        $process->wait();

        if (stripos($process->getOutput(), 'Cannot connect to the Docker daemon') !== false) {
            $this->error('Aurora failed to start');
            $this->warn(
                'Please make sure, that your Docker daemon is running and that you have the necessary permissions to run Docker commands'
            );
            exit(1);
        }

        ResetTerminal::reset();

        return 0;
    }
}
