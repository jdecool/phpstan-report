<?php

namespace JDecool\PHPStanReport\Runner;

use RuntimeException;

final class PHPStanRunner
{
    private const DEFAULT_TIMEOUT = 60; // in seconds

    public function __construct(
        public readonly string $phpstanBinary,
    ) {
        if (!is_executable($this->phpstanBinary)) {
            throw new \LogicException("File {$this->phpstanBinary} should be executable.");
        }
    }

    public function dumpParameters(): PHPStanParameters
    {
        $content = shell_exec("{$this->phpstanBinary} dump-parameters --json 2> /dev/null");
        $content = str_replace(['\\<', '\\>'], ['\\\\<', '\\\\>'], $content); // fix PHPStan JSON output escaping

        $phpstanParameters = json_decode($content, true, flags: JSON_THROW_ON_ERROR);

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
            throw new RuntimeException("Failed to run command: {$cmd}");
        }

        do {
            usleep(300_000);

            $procStatus = proc_get_status($proc);
        } while ($procStatus['running']);

        return proc_close($proc);
    }
}
