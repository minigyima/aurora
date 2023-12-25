<?php

namespace Minigyima\Aurora\Models;

use Dotenv\Dotenv;

class EnvironmentFile
{
    public readonly array $values;

    private array $dirty = [];

    public function __construct(private readonly string|null $envPath = null)
    {
        if ($envPath) {
            $this->values = $this->load($envPath);
            return;
        } else {
            $this->values = $this->load(base_path('.env'));
        }


    }

    private function load(string $envPath): array
    {
        $file = file_get_contents($envPath);
        return Dotenv::parse($file);
    }

    public function __isset(string $key): bool
    {
        return isset(array_merge($this->values, $this->dirty)[$key]);
    }

    public function setMultiple(array $values): void
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value);
        }
    }

    public function set(string $key, mixed $value): void
    {
        $this->dirty[$key] = $value;
    }

    public function __unset(string $key): void
    {
        unset($this->dirty[$key]);
    }

    public function __get(string $key): string
    {
        return array_merge($this->values, $this->dirty)[$key];
    }

    public function __set(string $key, mixed $value): void
    {
        $this->set($key, $value);
    }

    public function get(): array
    {
        return array_merge($this->values, $this->dirty);
    }

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

        file_put_contents($this->envPath ?? base_path('.env'), $env);
    }

}
