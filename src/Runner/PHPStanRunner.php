<?php

namespace JDecool\PHPStanReport\Runner;

use Nette\Neon\Neon;

final class PHPStanRunner
{
    public function __construct(
        public readonly string $phpstanBinary,
    ) {
        if (!is_executable($this->phpstanBinary)) {
            throw new \LogicException("File {$this->phpstanBinary} should be executable.");
        }
    }

    public function dumpParameters(): PHPStanParameters
    {
        $content = shell_exec("{$this->phpstanBinary} dump-parameters 2> /dev/null");
        $phpstanParameters = Neon::decode($content);

        return new PHPStanParameters($phpstanParameters);
    }

    public function analyze(): int
    {
        global $argv;
        $argv = $_SERVER['argv'] ?? [];
        $cmd = $argv;

        $cmd[0] = $this->phpstanBinary;
        $cmd = implode(' ', $cmd);

        $proc = proc_open($cmd, [], $pipes);
        if ($proc === false) {
            exit(1);
        }

        do {
            $procStatus = proc_get_status($proc);
            usleep(300000);
        } while ($procStatus['running']);

        return proc_close($proc);
    }
}
