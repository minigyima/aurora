<?php

namespace Minigyima\Aurora\Config\ConfigManager\Contracts;

use App\Helpers\ConfigManager\Models\Config;
use ReflectionClass;
use ReflectionProperty;
/**
 * Trait for "magical" configuration handling
 * Handles automatic property matching, default state and serialization
 */
trait ConfigBehaviour
{
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
     * Load the default properties as an array
     *
     * @return array default properties
     */
    private static function defaultProps(): array
    {
        $arr = [];
        $reflect = new ReflectionClass(self::class);

        foreach ($reflect->getProperties(ReflectionProperty::IS_PUBLIC) as $prop) {
            $arr[$prop->getName()] = $prop->getDefaultValue();
        }

        return $arr;
    }

    /**
     * Get the default config
     *
     * @return string JSON object containing the default values
     */
    public static function defaultState(): string
    {
        return json_encode(self::defaultProps());
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

    /**
     * Load config
     *
     * @param string $serialized JSON object containing the config data
     * @return Config|ConfigBehaviour the config
     */
    public static function load(string $serialized): self
    {
        $deserialized = json_decode($serialized, true);

        $class = new self();
        $reflect = new ReflectionClass(self::class);
        foreach ($reflect->getProperties(ReflectionProperty::IS_PUBLIC) as $prop) {
            $name = $prop->getName();
            if (isset($deserialized[$name])) {
                $class->{$name} = $deserialized[$name];
            } else {
                $class->{$name} = self::defaultProps()[$name];
            }
        }

        return $class;
    }
}
