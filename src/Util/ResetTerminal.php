<?php

namespace Minigyima\Aurora\Util;

use Symfony\Component\Process\Process;

class ResetTerminal
{
    public static function reset(): void
    {
        $proc = Process::fromShellCommandline('tput cnorm')->setTty(true);
        $proc->start();
        $proc->wait();
    }
}
