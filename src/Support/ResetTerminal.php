<?php

namespace Minigyima\Aurora\Support;

use Symfony\Component\Process\Process;

/**
 * ResetTerminal - Utility class for resetting the terminal
 * - Calls `tput cnorm` to reset the terminal
 * @package Minigyima\Aurora\Support
 */
class ResetTerminal
{
    /**
     * Reset the terminal
     * @return void
     */
    public static function reset(): void
    {
        if (posix_isatty(STDOUT)) {
            $proc = Process::fromShellCommandline('tput cnorm')->setTty(true);
            $proc->start();
            $proc->wait();
        } else {
            fwrite(STDOUT, "\033[?25h");
        }
    }
}
