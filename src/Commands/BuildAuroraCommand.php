<?php

namespace Minigyima\Aurora\Commands;

use Illuminate\Console\Command;
use Minigyima\Aurora\Services\Aurora;
use Minigyima\Aurora\Support\ResetTerminal;

/**
 * BuildAuroraCommand - Command for building the Aurora framework
 * - A small cli wrapper around Aurora::build()
 * @package Minigyima\Aurora\Commands
 * @see Aurora::build()
 */
class BuildAuroraCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'aurora:build';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Build the Aurora framework - Mercury Docker runtime';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Building the Mercury runtime...');
        $aurora = Aurora::use();

        $process = $aurora->build();

        $this->info('Starting Docker build process...');
        $process->setPty(true);
        $process->start(function ($type, $buffer) {
            $this->output->write($buffer);
        });

        $process->wait();
        $this->info('Mercury build complete!');
        $this->info('Done! Happy coding!');

        ResetTerminal::reset();

        return 0;
    }
}
