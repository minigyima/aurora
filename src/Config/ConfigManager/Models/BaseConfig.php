<?php

namespace Minigyima\Aurora\Config\ConfigManager\Models;

use Minigyima\Aurora\Config\ConfigManager\Contracts\ConfigBehaviour;
use Minigyima\Aurora\Config\ConfigManager\Interfaces\ConfigInterface;

/**
 * Standard configuration used by the app
 */
class BaseConfig implements ConfigInterface
{
    use ConfigBehaviour;

    public string $exampleString = 'Hello, world!';

    public int $exampleInt = 42;

}
