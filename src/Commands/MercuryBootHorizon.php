<?php

namespace Minigyima\Aurora\Commands;

use Illuminate\Console\Command;
use Minigyima\Aurora\Services\Mercury;
use Minigyima\Aurora\Support\ConsoleLogger;

/**
 * MercuryBootHorizon - Command for booting the Horizon process in Mercury
 * @package Minigyima\Aurora\Commands
 */
class MercuryBootHorizon extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mercury:boot-horizon';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Boot the Horizon process in Mercury';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        ConsoleLogger::log_info('Booting Horizon in Mercury...', 'MercuryBootHorizon');
        $mercury = Mercury::use();
        $mercury->bootHorizon();
    }
}
