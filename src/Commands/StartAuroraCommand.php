<?php

namespace Minigyima\Aurora\Commands;

use Illuminate\Console\Command;
use Minigyima\Aurora\Services\Aurora;
use Minigyima\Aurora\Traits\InteractsWithDockerManifest;

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

        $process = $aurora->start();
        $process->setPty(true);
        $process->start(function ($type, $buffer) {
            $this->output->write($buffer);
        });

        pcntl_signal(SIGINT, function () use ($process) {
            $process->signal(SIGTERM);
            $this->call('aurora:stop');
        });


        $process->wait();
        return 0;
    }
}
