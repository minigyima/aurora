<?php

namespace Minigyima\Aurora\Models;

use Dotenv\Dotenv;

/**
 * EnvironmentFile - Represents a .env file
 * @package Minigyima\Aurora\Models
 * @internal
 */
class EnvironmentFile
{
    /**
     * The values of the .env file
     * @var array
     */
    public readonly array $values;

    /**
     * The dirty values that have been set
     * @var array
     */
    private array $dirty = [];

    /**
     * EnvironmentFile constructor.
     * @param string|null $envPath
     */
    public function __construct(private readonly string|null $envPath = null)
    {
        if ($envPath) {
            $this->values = $this->load($envPath);
            return;
        } else {
            $this->values = $this->load(base_path('.env'));
        }


    }

    /**
     * Load the .env file
     * @param string $envPath
     * @return array
     */
    private function load(string $envPath): array
    {
        $file = file_get_contents($envPath);
        return Dotenv::parse($file);
    }

    /**
     * Magic method to check if a key exists
     * @param string $key
     * @return bool
     */
    public function __isset(string $key): bool
    {
        return isset(array_merge($this->values, $this->dirty)[$key]);
    }

    /**
     * Set multiple values
     * @param array $values
     * @return void
     */
    public function setMultiple(array $values): void
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * Set a value
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set(string $key, mixed $value): void
    {
        $this->dirty[$key] = $value;
    }

    /**
     * Magic method to unset a value
     * @param string $key
     * @return void
     */
    public function __unset(string $key): void
    {
        unset($this->dirty[$key]);
    }

    /**
     * Magic method to get a value
     * @param string $key
     * @return string
     */
    public function __get(string $key): string
    {
        return array_merge($this->values, $this->dirty)[$key];
    }

    /**
     * Magic method to set a value
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function __set(string $key, mixed $value): void
    {
        $this->set($key, $value);
    }

    /**
     * Get the values
     * @return array
     */
    public function get(): array
    {
        return array_merge($this->values, $this->dirty);
    }

    /**
     * Write the .env file to disk
     * @return void
     */
    public function write(): void
    {
        $env = file_get_contents($this->envPath ?? base_path('.env'));

        foreach ($this->dirty as $key => $value) {
            if (is_string($value) &&
                (str_contains($value, ' ') || str_contains($value, '$'))) {
                $value = str_replace('*QUOTE*', '"', "*QUOTE*$value*QUOTE*");
            }

            $env = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $env);

            if (! preg_match("/^{$key}=.*/m", $env)) {
                $env .= "\n{$key}={$value}";
                continue;
            }
        }

        file_put_contents($this->envPath ?? base_path('.env'), $env . PHP_EOL);
    }

}
