<?php

namespace Minigyima\Aurora\Config\ConfigManager\Interfaces;

interface ConfigInterface
{
    /**
     * Serialize the current config
     *
     * @return string JSON containing the current config
     */
    public function serialize(): string;

    /**
     * Load config
     *
     * @param string $serialized JSON object containing the config data
     * @return static the config
     */
    public static function load(string $serialized): self;

    /**
     * Get the default config
     *
     * @return string JSON object containing the default values
     */
    public static function defaultState(): string;

    /**
     * Get every config option with their respective values
     *
     * @return array key/value pair of all the options
     */
    public function all(): array;
}
