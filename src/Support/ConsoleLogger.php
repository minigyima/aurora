<?php

namespace Minigyima\Aurora\Support;

use Symfony\Component\Console\Formatter\OutputFormatterStyle;

/**
 * ConsoleLogger - Utility class for logging to the console
 * - Logs are written to STDERR
 * @package Minigyima\Aurora\Support
 */
class ConsoleLogger
{
    /**
     * Log an info message
     * @param string $message
     * @param string $sender
     * @return void
     */
    public static function log_info(string $message, string $sender = 'Aurora'): void
    {
        $logMsg = date('Y-m-d H:i:s') . " [info] @ $sender: $message\n";
        $formatter = new OutputFormatterStyle('blue', 'default', ['bold']);
        fwrite(STDERR, $formatter->apply($logMsg));
    }

    /**
     * Log a success message
     * @param string $message
     * @param string $sender
     * @return void
     */
    public static function log_success(string $message, string $sender = 'Aurora'): void
    {
        $logMsg = date('Y-m-d H:i:s') . " [success] @ $sender: $message\n";
        $formatter = new OutputFormatterStyle('green', 'default', ['bold']);
        fwrite(STDERR, $formatter->apply("$logMsg"));
    }

    /**
     * Log a warning message
     * @param string $message
     * @param string $sender
     * @return void
     */
    public static function log_warning(string $message, string $sender = 'Aurora'): void
    {
        $logMsg = date('Y-m-d H:i:s') . " [warn] @ $sender: $message\n";
        $formatter = new OutputFormatterStyle('yellow', 'default', ['bold']);
        fwrite(STDERR, $formatter->apply($logMsg));
    }

    /**
     * Log an error message
     * @param string $message
     * @param string $sender
     * @return void
     */
    public static function log_error(string $message, string $sender = 'Aurora'): void
    {
        $logMsg = date('Y-m-d H:i:s') . " [err] @ $sender: $message\n";
        $formatter = new OutputFormatterStyle('red', 'default', ['bold']);
        fwrite(STDERR, $formatter->apply($logMsg));
    }
}
