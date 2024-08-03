<?php

namespace JDecool\PHPStanReport\Runner;

use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\Console\Input\ArgvInput;

final class PHPStanRunner
{
    private const OPTIONS_TO_EXCLUDE = [
        '--format',
    ];

    private readonly ArgvInput $argv;

    public function __construct(
        public readonly string $phpstanBinary,
        public readonly LoggerInterface $logger,
    ) {
        if (!is_executable($this->phpstanBinary)) {
            throw new \LogicException("File {$this->phpstanBinary} should be executable.");
        }

        $this->argv = new ArgvInput();
    }

    public function dumpParameters(): PHPStanParameters
    {
        $args = '';
        if ($this->argv->hasParameterOption(['-c', '--configuration'])) {
            $args .= " --configuration={$this->argv->getParameterOption(['-c', '--configuration'])}";
        }
        if ($this->argv->hasParameterOption(['--memory-limit'])) {
            $memoryLimit = $this->argv->getParameterOption(['--memory-limit']);
            ini_set('memory_limit', $memoryLimit);

            $args .= " --memory-limit={$memoryLimit}";
        }
        if (!$this->argv->hasParameterOption(['--json'])) {
            $args .= ' --json';
        }

        $command = "{$this->phpstanBinary} dump-parameters $args 2> /dev/null";
        $this->logger->debug("Execute command: {$command}");

        $content = shell_exec($command);
        $content = str_replace(['\\<', '\\>'], ['\\\\<', '\\\\>'], $content); // fix PHPStan JSON output escaping

        $phpstanParameters = json_decode($content, true, flags: JSON_THROW_ON_ERROR);

        return new PHPStanParameters($phpstanParameters);
    }

    public function analyze(): int
    {
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
