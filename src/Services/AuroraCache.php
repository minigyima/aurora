<?php

namespace Minigyima\Aurora\Services;

use Illuminate\Contracts\Cache\Store;
use Illuminate\Support\Facades\Cache;
use Minigyima\Aurora\Support\CheckForSwoole;
use Override;

/*
 * AuroraCache
 * A cache driver that uses APCu for debug mode and Octane for production
 * @package Minigyima\Aurora\Services
 */

class AuroraCache implements Store
{
    /**
     * @var bool - Whether to use Swoole or not
     */
    private readonly bool $useSwoole;

    /**
     * AuroraCache constructor.
     * @return void
     */
    public function __construct()
    {
        $this->useSwoole = CheckForSwoole::check();
    }

    #[Override]
    public function many(array $keys)
    {
        if ($this->useSwoole) {
            return Cache::store('octane')->many($keys);
        } else {
            return Cache::store('apc')->many($keys);
        }
    }

    #[Override]
    public function put($key, $value, $seconds)
    {
        if ($this->useSwoole) {
            return Cache::store('octane')->put($key, $value, $seconds);
        } else {
            return Cache::store('apc')->put($key, $value, $seconds);
        }
    }

    #[Override]
    public function get($key)
    {
        if ($this->useSwoole) {
            return Cache::store('octane')->get($key);
        } else {
            return Cache::store('apc')->get($key);
        }
    }

    #[Override]
    public function putMany(array $values, $seconds)
    {
        if ($this->useSwoole) {
            return Cache::store('octane')->putMany($values, $seconds);
        } else {
            return Cache::store('apc')->putMany($values, $seconds);
        }
    }

    #[Override]
    public function increment($key, $value = 1)
    {
        if ($this->useSwoole) {
            return Cache::store('octane')->increment($key, $value);
        } else {
            return Cache::store('apc')->increment($key, $value);
        }
    }

    #[Override]
    public function decrement($key, $value = 1)
    {
        if ($this->useSwoole) {
            return Cache::store('octane')->decrement($key, $value);
        } else {
            return Cache::store('apc')->decrement($key, $value);
        }
    }

    #[Override]
    public function forever($key, $value)
    {
        if ($this->useSwoole) {
            return Cache::store('octane')->forever($key, $value);
        } else {
            return Cache::store('apc')->forever($key, $value);
        }
    }

    #[Override]
    public function forget($key)
    {
        if ($this->useSwoole) {
            return Cache::store('octane')->forget($key);
        } else {
            return Cache::store('apc')->forget($key);
        }
    }

    #[Override]
    public function flush()
    {
        if ($this->useSwoole) {
            return Cache::store('octane')->flush();
        } else {
            return Cache::store('apc')->flush();
        }
    }

    #[Override]
    public function getPrefix()
    {
        if ($this->useSwoole) {
            return Cache::store('octane')->getPrefix();
        } else {
            return Cache::store('apc')->getPrefix();
        }
    }
}
