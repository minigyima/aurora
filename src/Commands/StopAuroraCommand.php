<?php

namespace Minigyima\Aurora\Commands;

use Illuminate\Console\Command;
use Minigyima\Aurora\Services\Aurora;


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
        $this->info('Aurora stopped!');
        $this->info('Done! Have a nice day!');
        return 0;
    }
}
