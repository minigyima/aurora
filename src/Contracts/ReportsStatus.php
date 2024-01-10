<?php

namespace Minigyima\Aurora\Contracts;

/**
 * ReportsStatus - Interface for reporting the status of a service
 * @package Minigyima\Aurora\Contracts
 */
interface ReportsStatus
{
    public function active(): bool;
}
