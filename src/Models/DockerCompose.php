<?php

namespace Minigyima\Aurora\Models;

use ArrayAccess;
use Minigyima\Aurora\Errors\InvalidDockerComposeException;
use Minigyima\Aurora\Traits\InteractsWIthComposeFiles;
use Override;
use Symfony\Component\Yaml\Yaml;

/**
 * Class DockerCompose - Represents a docker-compose.yml file
 * @package Minigyima\Aurora\Models
 *
 */
class DockerCompose implements ArrayAccess
{
    use InteractsWIthComposeFiles;

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
     * @param string $key
     * @return bool
     */
    public function __isset(string $key): bool
    {
        return isset(array_merge($this->props, $this->dirty)[$key]);
    }

    /**
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
     * @param string $key
     * @param string $value
     * @return void
     */
    public function set(string $key, string $value): void
    {
        $this->dirty[$key] = $value;
    }

    /**
     * @param string $key
     * @return void
     */
    public function __unset(string $key): void
    {
        unset($this->dirty[$key]);
    }

    /**
     * @param string $key
     * @return string|null
     */
    public function __get(string $key): string|null
    {
        return array_merge($this->props, $this->dirty)[$key] ?? null;
    }

    /**
     * @param string $key
     * @param string $value
     * @return void
     */
    public function __set(string $key, string $value): void
    {
        $this->set($key, $value);
    }

    /**
     * @return void
     */
    public function write(): void
    {
        $yaml = Yaml::dump(array_merge($this->props, $this->dirty), 4, 4);
        file_put_contents($this->path, $yaml);
    }

    /**
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

    public function getService(string $name): array
    {
        if (! array_key_exists($name, $this->props['services'])) {
            throw new InvalidDockerComposeException("Service $name does not exist");
        }

        return $this->getServices()['postgres'];
    }

    public function getServices(): array
    {
        return $this->props['services'] ?? [];
    }

    #[Override]
    public function offsetExists(mixed $offset): bool
    {
        return isset(array_merge($this->props, $this->dirty)[$offset]);
    }

    #[Override]
    public function offsetGet(mixed $offset): mixed
    {
        return array_merge($this->props, $this->dirty)[$offset] ?? null;
    }

    #[Override]
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->set($offset, $value);
    }

    #[Override]
    public function offsetUnset(mixed $offset): void
    {
        unset($this->dirty[$offset]);
    }

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
