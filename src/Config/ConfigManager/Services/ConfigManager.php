<?php

namespace Minigyima\Aurora\Config\ConfigManager\Services;

use Minigyima\Aurora\Config\ConfigManager\Models\BaseConfig;
use Minigyima\Aurora\Config\Constants;

/**
 * ConfigManager - Manages the config file
 * @package Minigyima\Aurora\Config\ConfigManager\Services
 */
class ConfigManager
{

    /**
     * The config model
     * @var BaseConfig
     */
    public readonly BaseConfig $config;

    /**
     * ConfigManager constructor.
     */
    public function __construct()
    {
        $this->loadConfig();
    }

    /**
     * Load config from disk
     * @return void
     */
    private function loadConfig(): void
    {
        $exists = file_exists(Constants::CONFIG_FILE_PATH);

        if (! $exists) {
            $this->config = $this->getConfigModel()::load($this->getConfigModel()::defaultState());
            $this->write();
        } else {
            $serialized = file_get_contents(Constants::CONFIG_FILE_PATH);
            $this->config = $this->getConfigModel()::load($serialized);
        }
    }

    /**
     * Get the config model
     * @return string
     */
    private function getConfigModel(): string
    {
        return config('aurora.config_model');
    }

    /**
     * Save config to disk
     * @return void
     */
    public function write(): void
    {
        $serialized = $this->config->serialize();
        file_put_contents(Constants::CONFIG_FILE_PATH, $serialized);
    }

    /**
     * Return the currently loaded ConfigManager singleton
     * @return self
     */
    public static function use(): self
    {
        return app(ConfigManager::class);
    }
}
