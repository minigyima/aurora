<?php

namespace Minigyima\Aurora\Config\ConfigManager\Services;

use Minigyima\Aurora\Config\ConfigManager\Models\BaseConfig;
use Minigyima\Aurora\Config\Constants;

class ConfigManager
{
    public readonly BaseConfig $config;

    public function __construct()
    {
        $this->loadConfig();
    }

    /**
     * Load config from disk
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
     */
    private function getConfigModel(): string
    {
        return config('aurora.config_model');
    }

    /**
     * Save config to disk
     */
    public function write(): void
    {
        $serialized = $this->config->serialize();
        file_put_contents(Constants::CONFIG_FILE_PATH, $serialized);
    }

    /**
     * Return the currently loaded ConfigManager singleton
     */
    public static function use(): self
    {
        return app(ConfigManager::class);
    }
}
