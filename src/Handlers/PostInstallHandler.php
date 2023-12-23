<?php

namespace Minigyima\Aurora\Handlers;

use Illuminate\Support\Facades\Log;

class PostInstallHandler
{
    public static function handle()
    {
        $channel = Log::channel('errorlog');
        $channel->info('Aurora - Running postinst script');
        $channel->info('Patching composer.json');
        $path = base_path('composer.json');
        $composer = json_decode(file_get_contents($path), true);

        $composer['scripts']['boot-aurora'][] = 'Minigyima\Aurora\Handlers\PostInstallHandler::handle';

        file_put_contents($path, json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $channel->info('Patched composer.json');
        $channel->info('Aurora - Finished running postinst script');
    }
}
