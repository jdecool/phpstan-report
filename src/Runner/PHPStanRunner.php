<?php

namespace JDecool\PHPStanReport\Runner;

use JDecool\PHPStanReport\Application\Context;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\Console\Input\ArgvInput;

final class PHPStanRunner
{
    private const OPTIONS_TO_EXCLUDE = [
        '-c', '--continue-on-error',
        '-f', '--output-format',
        '-m', '--maximum-allowed-errors',
        '--phpstan-bin',
    ];

    public function __construct(
        private readonly Context $context,
        private readonly LoggerInterface $logger,
        private readonly ArgvInput $argv,
    ) {
        if (!is_executable($this->context->getPhpstanBinary())) {
            throw new \LogicException("File {$this->context->getPhpstanBinary()} should be executable.");
        }
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

        $command = PHP_BINARY . " {$this->context->getPhpstanBinary()} dump-parameters $args 2> /dev/null";
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
        $cmd[0] = $this->context->getPhpstanBinary();
        $cmd[1] = 'analyze';
        array_unshift($cmd, PHP_BINARY);

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

        $exitCode = proc_close($proc);

        return $procStatus['exitcode'] ?? $exitCode;
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
