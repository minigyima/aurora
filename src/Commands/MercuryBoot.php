<?php

namespace Minigyima\Aurora\Commands;

use Illuminate\Console\Command;
use Minigyima\Aurora\Services\Mercury;
use Minigyima\Aurora\Support\ConsoleLogger;

/**
 * MercuryBoot - Command for booting the Mercury process
 * - A small cli wrapper around Mercury::boot()
 * @package Minigyima\Aurora\Commands
 * @see Mercury::boot()
 */
class MercuryBoot extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mercury:boot';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bootstraps the Mercury process';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        pcntl_async_signals(true);
        pcntl_signal(SIGINT, function () {
            ConsoleLogger::log_info('Shutting down gracefully...', 'Mercury');
            $this->exit(0);
        });

        $mercury = Mercury::use();
        return $mercury->boot();
    }
}
