<?php

namespace Minigyima\Aurora\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Minigyima\Aurora\Concerns\Docker\InteractsWithComposeFiles;
use Minigyima\Aurora\Config\Constants;
use Minigyima\Aurora\Models\DockerCompose;
use Minigyima\Aurora\Models\EnvironmentFile;
use Minigyima\Aurora\Support\StrClean;
use function Laravel\Prompts\confirm;

/**
 * ConfigureSoketiCommand - Command for configuring the Soketi Websocket server
 * @package Minigyima\Aurora\Commands
 */
class ConfigureSoketiCommand extends Command
{
    use InteractsWithComposeFiles;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'aurora:configure-soketi';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Configure the database for this Aurora project';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Configuring Soketi...');
        $this->info('Soketi is a websocket server for Aurora');
        $this->info('Soketi is used for real-time communication between the server and the client (browser)');

        $envFile = new EnvironmentFile();

        // Check if soketi support is enabled
        $this->info('Checking if Soketi is enabled...');
        if (config('aurora.sockets_enabled')) {
            $this->info('Soketi is already enabled, continuing');
        } else {
            $choice = confirm('Soketi support is not enabled, would you like to enable it?');
            if ($choice) {
                $envFile->set('AURORA_SOCKETS_ENABLED', true);
                $this->info('Soketi has been enabled');
            } else {
                $this->warn('Soketi has not been enabled');
                return self::FAILURE;
            }
        }

        $metrics = confirm('Would you like to enable metrics for Soketi?');

        $debug_mode = confirm('Would you like to enable debug mode for Soketi?');

        $compose = new DockerCompose(self::getCurrentComposeFile());
        $networks = $compose->getNetworksForService('mercury');
        $ip_addresses = [];
        foreach ($networks as $network) {
            try {
                $ip_addresses[] = $compose->getServiceIp('soketi', $network);
            } catch (Exception $e) {
                $this->warn('Could not get IP address for network: ' . $network);
            }
        }
        if (empty($ip_addresses)) {
            $this->error(
                'Could not get IP addresses for Soketi. Please make sure that Soketi and Mercury have at least one common network.'
            );
            return self::FAILURE;
        }
        $ip_address = array_filter($ip_addresses)[0];

        $this->info('Found Soketi @ ' . $ip_address);
        $app_name = str_replace([' ', '-'], ['_', '_'], StrClean::clean(strtolower(config('app.name'))));

        $app_id = $app_name . '_realtime';
        $app_key = StrClean::clean(Str::random(32));
        $app_secret = StrClean::clean(Str::random(32));

        $this->table(
            [
                'PUSHER_APP_ID',
                'PUSHER_APP_KEY',
                'PUSHER_APP_SECRET',
                'PUSHER_HOST',
                'PUSHER_PORT',
                'PUSHER_SCHEME',
                'Soketi Metrics',
                'Soketi Port',
                'Soketi Metrics Port',
                'Soketi Debug Mode'
            ],
            [
                [
                    $app_id,
                    $app_key,
                    $app_secret,
                    $ip_address,
                    6001,
                    'http',
                    $metrics ? 'Enabled' : 'Disabled',
                    6001,
                    9601,
                    $debug_mode ? 'Enabled' : 'Disabled'
                ]
            ]
        );

        $choice = confirm('Would you like to save these settings?');
        if ($choice) {
            $this->info('Writing settings to .env file...');
            $envFile->set('PUSHER_APP_ID', $app_id);
            $envFile->set('PUSHER_APP_KEY', $app_key);
            $envFile->set('PUSHER_APP_SECRET', $app_secret);
            $envFile->set('PUSHER_HOST', $ip_address);
            $envFile->set('PUSHER_PORT', 6001);
            $envFile->set('PUSHER_SCHEME', 'http');
            $envFile->write();

            $soketi_config = [
                'debug' => $debug_mode,
                'port' => 6001,
                'metrics' => [
                    'enabled' => $metrics,
                    'port' => 9601,
                    'driver' => 'prometheus',
                    'prometheus' => [
                        'prefix' => "{$app_name}_"
                    ]
                ],
                'appManager.array.apps' => [
                    [
                        'id' => $app_id,
                        'key' => $app_key,
                        'secret' => $app_secret,
                        'webhooks' => []
                    ]
                ]
            ];

            if (! file_exists(pathinfo(Constants::SOKETI_CONFIG_FILE_PATH, PATHINFO_DIRNAME))) {
                mkdir(pathinfo(Constants::SOKETI_CONFIG_FILE_PATH, PATHINFO_DIRNAME), 0755, true);
            }

            $this->info('Writing settings to soketi/config.json...');
            $soketi_config_json = json_encode($soketi_config, JSON_PRETTY_PRINT);
            file_put_contents(Constants::SOKETI_CONFIG_FILE_PATH, $soketi_config_json);

            $this->info('Settings saved');
        } else {
            $this->warn('Settings have not been saved');
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
