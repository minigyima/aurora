<?php

namespace Minigyima\Aurora\Commands;

use Illuminate\Console\Command;
use Minigyima\Aurora\Services\Mercury;

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
        $mercury = Mercury::use();
        $mercury->boot();
    }
}
