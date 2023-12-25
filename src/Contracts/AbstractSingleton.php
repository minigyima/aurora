<?php

namespace Minigyima\Aurora\Contracts;

use Exception;

abstract class AbstractSingleton implements ReportsStatus
{
    /**
     * Whether the service is active or not
     *
     * @var boolean
     */
    protected bool $active = false;

    /**
     * Return the currently loaded singleton
     *
     * @return static
     */
    abstract public static function use(): static;

    /**
     * Return whether the service is active or not
     * getter edition
     *
     * @return boolean
     */
    public function active(): bool
    {
        return $this->active;
    }

    protected function requireActive()
    {
        if (! $this->active) {
            throw new Exception('Service is not active');
        }
    }
}
