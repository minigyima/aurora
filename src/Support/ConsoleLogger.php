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

        self::log_stderr($formatter->apply($logMsg));
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

        self::log_stderr($formatter->apply($logMsg));
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

        self::log_stderr($formatter->apply($logMsg));
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

        self::log_stderr($formatter->apply($logMsg));
    }

    /**
     * Log a trace message
     * @param string $message
     * @param string $sender
     * @return void
     */
    public static function log_trace(string $message, string $sender = 'Aurora'): void
    {
        $logMsg = date('Y-m-d H:i:s') . " [trace] @ $sender: $message\n";
        $formatter = new OutputFormatterStyle('magenta', 'default', ['bold']);

        self::log_stderr($formatter->apply($logMsg));
    }

    private static function log_stderr(string $message) {
        if(CheckForSwoole::check()) {
            fwrite(STDERR, $message);
        }
        else {
            fwrite(fopen('php://stderr', 'wb'), $message);
        }
    }
}
