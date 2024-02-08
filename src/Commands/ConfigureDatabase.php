<?php

namespace Minigyima\Aurora\Commands;

use Exception;
use Illuminate\Console\Command;
use Minigyima\Aurora\Concerns\InteractsWithComposeFiles;
use Minigyima\Aurora\Config\Constants;
use Minigyima\Aurora\Models\DockerCompose;
use Minigyima\Aurora\Models\EnvironmentFile;
use Minigyima\Aurora\Support\StrClean;
use Str;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\password;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;
use function Minigyima\Aurora\Support\rrmdir;

/**
 * ConfigureDatabase - Command for configuring the database
 * @package Minigyima\Aurora\Commands
 */
class ConfigureDatabase extends Command
{
    use InteractsWithComposeFiles;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'aurora:configure-database';

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
        $config_type = select(
            label: 'How would you like to configure the database?',
            options: ['automatic' => 'Automatic', 'semi-automatic' => 'Semi-Automatic', 'manual' => 'Manual'],
            default: 'automatic',
            hint: 'Semi-Automatic requires database credentials, and Manual only updates the .env file.',
        );

        $this->info('You selected: ' . $config_type);

        switch ($config_type) {
            case 'manual':
                return $this->manual();
                break;
            case 'automatic':
                return $this->automatic();
                break;
            case 'semi-automatic':
                return $this->semiAutomatic();
                break;
            default:
                $this->error('Invalid option');
                return self::FAILURE;
                break;
        }
        // if(array_key_exists())
    }

    /**
     * Manual database configuration
     * @return int
     * @throws Exception
     */
    private function manual()
    {
        $ip_address = text(
            'Please enter the IP address of the database server',
            validate: fn($value) => filter_var($value, FILTER_VALIDATE_IP) ? $value : 'Invalid IP address',
            hint: 'e.g: 172.128.1.1'
        );
        $db_name = text('Please enter the database name', hint: 'e.g: my_database');
        $username = text('Please enter the database username', hint: 'e.g: my_username');
        $password = password('Please enter the database password', hint: 'e.g: my_password');
        $database_driver = select(
            label: 'Please select the database driver',
            options: ['pgsql' => 'Postgres', 'mysql' => 'MySQL'],
            default: 'pgsql',
            hint: 'Postgres is the default database driver for Aurora.'
        );
        $port = text(
            'Please enter the database port',
            default: 5432,
            validate: fn($value) => is_numeric($value) ? null : 'Invalid port',
            hint: 'e.g: 5432'
        );

        return $this->finish($db_name, $username, $password, $ip_address, $port, $database_driver, false);
    }

    /**
     * Finish the configuration
     * @param string $db_name
     * @param string $username
     * @param string $password
     * @param string $ip_addr
     * @param int $port
     * @param string $driver
     * @param bool $write_override
     * @return int
     */
    private function finish(
        string $db_name,
        string $username,
        string $password,
        string $ip_addr,
        int    $port = 5432,
        string $driver = 'pgsql',
        bool   $write_override = true,
    ): int {
        $this->table(
            ['Database', 'Username', 'Password', 'IP Address', 'Port', 'Driver'],
            [[$db_name, $username, $password, $ip_addr, $port, $driver]]
        );

        $confirmed = confirm(
            'Would you like to persist these changes?',
            default: false,
            hint: $write_override ? 'docker-compose.override.yml and .env will get updated.' : '.env will get updated.'
        );

        if (! $confirmed) {
            $this->error('Aborted');
            return self::FAILURE;
        }
        $this->info('Writing to .env file...');
        $this->writeEnv($db_name, $username, $password, $ip_addr);

        if ($write_override) {
            $this->info('Writing to docker-compose.override.yml...');
            $this->writeDockerComposeOverride($db_name, $username, $password);
            $this->clearPostgresData();
        }

        $this->info('Done! Happy coding!');
        return self::SUCCESS;
    }

    /**
     * Write to the .env file to configure the database
     * @param string $db_name
     * @param string $username
     * @param string $password
     * @param mixed $ip_addr
     * @param int $port
     * @param string $connection
     * @return void
     */
    private function writeEnv(
        string $db_name,
        string $username,
        string $password,
        mixed  $ip_addr,
        int    $port = 5432,
        string $connection = 'pgsql'
    ): void {
        $this->warn('Backing up .env to .env.aurora.bak...');
        copy(base_path('.env'), base_path('.env.aurora.bak'));

        $env = new EnvironmentFile();
        $env->setMultiple([
            'DB_CONNECTION' => $connection,
            'DB_HOST' => $ip_addr,
            'DB_PORT' => $port,
            'DB_DATABASE' => $db_name,
            'DB_USERNAME' => $username,
            'DB_PASSWORD' => $password,
        ]);
        $env->write();
    }

    /**
     * Write to the docker-compose.override.yml file to configure the database
     * @param string $db_name
     * @param string $username
     * @param string $password
     * @return void
     */
    private function writeDockerComposeOverride(string $db_name, string $username, string $password): void
    {
        $override = new DockerCompose(base_path('docker-compose.override.yml'), true);

        if (file_exists(base_path('docker-compose.override.yml'))) {
            $this->warn(
                'docker-compose.override.yml already exists. Renaming to docker-compose.override.aurora.bak...'
            );
            rename(base_path('docker-compose.override.yml'), base_path('docker-compose.override.aurora.bak'));
        }

        $override->mergeKey('postgres', 'environment', [
            'POSTGRES_DB' => $db_name,
            'POSTGRES_USER' => $username,
            'POSTGRES_PASSWORD' => $password,
        ]);

        $override->write();
    }

    /**
     * Clear the Postgres data
     * @return void
     */
    private function clearPostgresData(): void
    {
        $prompt = confirm(
            'Would you like to clear the Postgres data?',
            default: false,
            hint: 'This will delete all data in the Postgres database.'
        );

        if (! $prompt) {
            $this->info('Skipping Postgres data deletion.');
            return;
        }
        $this->info('Stopping services...');
        $this->call('aurora:stop');

        $this->info('Clearing Postgres data...');
        rrmdir(base_path(Constants::AURORA_DOCKER_STORAGE_PATH . '/database'));

        $this->info('Successfully cleared Postgres data.');
    }

    /**
     * Automatic database configuration
     * @return int
     * @throws Exception
     */
    private function automatic()
    {
        $confirm = confirm(
            'Are you sure you want to automatically configure the database? This will overwrite any existing database configuration.',
            default: false,

        );

        if (! $confirm) {
            $this->error('Aborted');
            return self::FAILURE;
        }

        $ip_addr = $this->getPostgresIp();
        $this->info('Postgres IP: ' . $ip_addr);

        $this->info('Generating credentials...');
        $app_name = str_replace([' ', '-'], ['_', '_'], StrClean::clean(strtolower(config('app.name'))));
        $db_name = $app_name . '_db';
        $username = $app_name . '_super';
        $password = Str::random(32);

        return $this->finish($db_name, $username, $password, $ip_addr, 5432, 'pgsql', true);
    }

    /**
     * Get the IP address of the Postgres service
     * @throws Exception
     */
    private function getPostgresIp(): string
    {
        $compose = new DockerCompose();

        return $compose->getServiceIp('postgres', 'aurora');
    }

    /**
     * Semi-automatic database configuration
     * @return int
     * @throws Exception
     */
    private function semiAutomatic()
    {
        $ip_addr = $this->getPostgresIp();
        $this->info('Postgres IP: ' . $ip_addr);

        $app_name = str_replace([' ', '-'], ['_', '_'], StrClean::clean(strtolower(config('app.name'))));

        $username = text('What is the username?', default: $app_name, hint: 'This is the username for the database');
        $password = password('What is the password?', hint: 'This is the password for the database');
        $db_name = text('What is the database name?', default: $app_name, hint: 'This is the name of the database');

        return $this->finish($db_name, $username, $password, $ip_addr, 5432, 'pgsql', true);
    }
}
