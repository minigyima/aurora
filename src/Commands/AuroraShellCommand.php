<?php

namespace Minigyima\Aurora\Commands;

use Illuminate\Console\Command;
use Minigyima\Aurora\Services\Aurora;
use Minigyima\Aurora\Util\ResetTerminal;

class AuroraShellCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'aurora:shell';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Starts a shell inside the Mercury container for this Aurora project';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting shell inside Mercury container...');
        $this->info('Type "exit" to exit the shell.');

        $aurora = Aurora::use();
        $process = $aurora->shell();

        $process->start();

        $process->wait();

        if ($process->getExitCode() !== 0) {
            $this->warn('Failed to execute `bash` inside the Mercury container.');
            $this->warn('Please make sure that Aurora is running and try again.');
            return 1;
        }

        ResetTerminal::reset();

        return 0;
    }
}
