<?php

namespace JDecool\PHPStanReport\Runner;

use Psr\Log\LoggerInterface;
use RuntimeException;

final class PHPStanRunner
{
    private const DEFAULT_TIMEOUT = 60; // in seconds

    private const OPTIONS_TO_EXCLUDE = [
        '--format',
    ];

    public function __construct(
        public readonly string $phpstanBinary,
        public readonly LoggerInterface $logger,
    ) {
        if (!is_executable($this->phpstanBinary)) {
            throw new \LogicException("File {$this->phpstanBinary} should be executable.");
        }
    }

    public function dumpParameters(): PHPStanParameters
    {
        $command = "{$this->phpstanBinary} dump-parameters --json 2> /dev/null";
        $this->logger->debug("Execute command: {$command}");

        $content = shell_exec($command);
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
        $cmd[1] = 'analyze';
        $cmd = implode(' ', array_filter(
            $cmd,
            fn($arg) => $this->shouldBeExcluded($arg) === false,
        ));

        $this->logger->debug("Execute command: {$cmd}");

        $proc = proc_open($cmd, [STDIN, STDERR, STDERR], $pipes);
        if ($proc === false) {
            throw new RuntimeException("Failed to run command: {$cmd}");
        }

        do {
            usleep(300_000);

            $procStatus = proc_get_status($proc);
        } while ($procStatus['running']);

        return proc_close($proc);
    }

    private function shouldBeExcluded(string $arg): bool
    {
        foreach (self::OPTIONS_TO_EXCLUDE as $option) {
            if (str_starts_with($arg, $option)) {
                return true;
            }
        }

        return false;
    }
}
