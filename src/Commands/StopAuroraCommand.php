<?php

namespace Minigyima\Aurora\Commands;

use Illuminate\Console\Command;
use Minigyima\Aurora\Services\Aurora;
use Minigyima\Aurora\Support\ResetTerminal;

/**
 * StopAuroraCommand - Command for stopping the Aurora framework
 * - A small cli wrapper around Aurora::stop()
 * @package Minigyima\Aurora\Commands
 * @see Aurora::stop()
 */
class StopAuroraCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'aurora:stop';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Stop the Aurora framework';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Stopping Aurora...');
        $aurora = Aurora::use();

        $this->info('Invoking Docker Compose...');
        $process = $aurora->stop();
        $process->setPty(true);
        $process->start(function ($type, $buffer) {
            $this->output->write($buffer);
        });

        $process->wait();

        if (stripos($process->getOutput(), 'Cannot connect to the Docker daemon') !== false) {
            $this->error('Aurora failed to stop');
            $this->warn(
                'Please make sure, that your Docker daemon is running and that you have the necessary permissions to run Docker commands'
            );
            exit(1);
        }

        $this->info('Aurora stopped!');
        $this->info('Done! Have a nice day!');

        ResetTerminal::reset();

        return 0;
    }
}
