<?php

namespace Minigyima\Aurora\Models;

use ArrayAccess;
use Minigyima\Aurora\Concerns\InteractsWithComposeFiles;
use Minigyima\Aurora\Errors\InvalidDockerComposeException;
use Override;
use Symfony\Component\Yaml\Yaml;

/**
 * Class DockerCompose - Represents a docker-compose.yml file
 * @package Minigyima\Aurora\Models
 */
class DockerCompose implements ArrayAccess
{
    use InteractsWithComposeFiles;

    /**
     * The values of the docker-compose.yml file
     * @var array|mixed
     */
    private array $props;
    /**
     * The path to the docker-compose.yml file
     * @var string
     */
    private string $path;
    /**
     * The dirty values that have been set
     * @var array
     */
    private array $dirty = [];

    /**
     * DockerCompose constructor.
     * @param string|null $path
     * @throws InvalidDockerComposeException
     */
    public function __construct(string|null $path = null, private readonly bool $isOverrideFile = false)
    {
        if ($path) {
            $this->path = $path;
        } else {
            $this->path = self::getCurrentComposeFile();
        }

        if (! file_exists($this->path)) {
            $this->props = [
                'version' => '3.9'
            ];
        }
        $this->props = $this->loadAndValidate();
    }

    /**
     * Load and validate the docker-compose.yml file
     * @return array
     * @throws InvalidDockerComposeException
     */
    private function loadAndValidate(): array
    {
        if (! file_exists($this->path)) {
            return [
                'version' => '3.9'
            ];
        }

        $values = Yaml::parseFile($this->path);
        if (! array_key_exists('version', $values)) {
            throw new InvalidDockerComposeException('docker-compose.yml does not contain a version key');
        }

        if (! $this->isOverrideFile && ! array_key_exists('services', $values)) {
            throw new InvalidDockerComposeException('docker-compose.yml does not contain a services key');
        }

        if (! $this->isOverrideFile && ! array_key_exists('networks', $values)) {
            throw new InvalidDockerComposeException('docker-compose.yml does not contain a networks key');
        }

        return $values;
    }

    /**
     * Magic method to check if a key exists
     * @param string $key
     * @return bool
     */
    public function __isset(string $key): bool
    {
        return isset(array_merge($this->props, $this->dirty)[$key]);
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
     * @param string $value
     * @return void
     */
    public function set(string $key, string $value): void
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
     * @return string|null
     */
    public function __get(string $key): string|null
    {
        return array_merge($this->props, $this->dirty)[$key] ?? null;
    }

    /**
     * Magic method to set a value
     * @param string $key
     * @param string $value
     * @return void
     */
    public function __set(string $key, string $value): void
    {
        $this->set($key, $value);
    }

    /**
     * Write the docker-compose.yml file to disk
     * @return void
     */
    public function write(): void
    {
        $yaml = Yaml::dump(array_merge($this->props, $this->dirty), 4, 4);
        file_put_contents($this->path, $yaml);
    }

    /**
     * Get the IP address of a service
     * @throws InvalidDockerComposeException
     */
    public function getServiceIp(string $name, string $network): string
    {
        $service = $this->getService($name);

        if (! array_key_exists('networks', $service)) {
            throw new InvalidDockerComposeException("Service $name not assigned to a network");
        }

        $networks = $service['networks'];

        if (! array_key_exists($network, $networks)) {
            throw new InvalidDockerComposeException("Service $name not assigned to the $network network");
        }

        if (! array_key_exists('ipv4_address', $networks[$network])) {
            throw new InvalidDockerComposeException("Service $name does not have an IPv4 address");
        }

        return $networks[$network]['ipv4_address'];
    }

    /**
     * Get a service
     * @param string $name
     * @return array
     * @throws InvalidDockerComposeException
     */
    public function getService(string $name): array
    {
        if (! array_key_exists($name, $this->props['services'])) {
            throw new InvalidDockerComposeException("Service $name does not exist");
        }

        return $this->getServices()['postgres'];
    }

    /**
     * Get the services described in the docker-compose.yml file
     * @return array
     */
    public function getServices(): array
    {
        return $this->props['services'] ?? [];
    }

    /**
     * Magic method to check if a key exists
     * @param mixed $offset
     * @return bool
     * @see ArrayAccess::offsetExists()
     */
    #[Override]
    public function offsetExists(mixed $offset): bool
    {
        return isset(array_merge($this->props, $this->dirty)[$offset]);
    }

    /**
     * Magic method to get a value
     * @param mixed $offset
     * @return mixed
     * @see ArrayAccess::offsetGet()
     */
    #[Override]
    public function offsetGet(mixed $offset): mixed
    {
        return array_merge($this->props, $this->dirty)[$offset] ?? null;
    }

    /**
     * Magic method to set a value
     * @param mixed $offset
     * @param mixed $value
     * @return void
     * @see ArrayAccess::offsetSet()
     */
    #[Override]
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->set($offset, $value);
    }

    /**
     * Magic method to unset a value
     * @param mixed $offset
     * @return void
     * @see ArrayAccess::offsetUnset()
     */
    #[Override]
    public function offsetUnset(mixed $offset): void
    {
        unset($this->dirty[$offset]);
    }

    /**
     * Merges a service configuration with a new one
     * @param string $service
     * @param string $key
     * @param array $new_value
     * @return void
     */
    public function mergeKey(string $service, string $key, array $new_value): void
    {
        $services = [...$this->getServices()];
        $service_arr = $services[$service] ?? [];
        $value = $service_arr[$key] ?? [];

        $value = array_merge($value, $new_value);
        $service_arr[$key] = $value;
        $services[$service] = $service_arr;

        $this->dirty['services'] = $services;
    }

}
