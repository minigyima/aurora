<?php

namespace Minigyima\Aurora\Concerns;

/**
 * Trait for testing if Docker exists on a given system
 * @package Minigyima\Aurora\Traits
 */
trait TestsForDocker
{

    /**
     * Check if Docker is installed
     * @return bool
     */
    public function testForDocker(): bool
    {
        $test_method = (false === stripos(PHP_OS, 'winnt')) ? 'which' : 'where';
        return ! (null === shell_exec("$test_method docker"));
    }
}
