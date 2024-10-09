<?php

namespace Minigyima\Aurora\Config\ConfigManager\Contracts;

use App\Helpers\ConfigManager\Models\Config;
use ReflectionClass;
use ReflectionProperty;

/**
 * ConfigBehaviour - The default ConfigInterface implementation
 * - Trait for "magical" configuration handling
 * - Handles automatic property matching, default state and serialization
 * @package Minigyima\Aurora\Config\ConfigManager\Contracts
 * @see ConfigInterface
 */
trait ConfigBehaviour
{
    /**
     * Get the default config
     *
     * @return string JSON object containing the default values
     */
    public static function defaultState(): string
    {
        return json_encode(static::defaultProps());
    }

    /**
     * Load the default properties as an array
     *
     * @return array default properties
     */
    private static function defaultProps(): array
    {
        $arr = [];
        $reflect = new ReflectionClass(static::class);

        foreach ($reflect->getProperties(ReflectionProperty::IS_PUBLIC) as $prop) {
            $arr[$prop->getName()] = $prop->getDefaultValue();
        }

        return $arr;
    }

    /**
     * Load config
     *
     * @param string $serialized JSON object containing the config data
     * @return Config|ConfigBehaviour the config
     */
    public static function load(string $serialized): static
    {
        $deserialized = json_decode($serialized, true);

        $class = new static();
        $reflect = new ReflectionClass(static::class);
        foreach ($reflect->getProperties(ReflectionProperty::IS_PUBLIC) as $prop) {
            $name = $prop->getName();
            if (isset($deserialized[$name])) {
                $class->{$name} = $deserialized[$name];
            } else {
                $class->{$name} = static::defaultProps()[$name];
            }
        }

        return $class;
    }

    /**
     * Serialize the current config
     *
     * @return string JSON containing the current config
     */
    public function serialize(): string
    {
        return json_encode(get_object_vars($this));
    }

    /**
     * Get every config option
     *
     * @return array list of config options
     */
    public function all(): array
    {
        return get_object_vars($this);
    }
}
