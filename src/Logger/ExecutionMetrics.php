<?php

declare(strict_types=1);

namespace JDecool\PHPStanReport\Logger;

use Symfony\Component\Console\Output\OutputInterface;

final class ExecutionMetrics
{
    private float $startTime = .0;
    private int $startMemory = 0;

    public function start(): void
    {
        $this->startTime = microtime(true);
        $this->startMemory = memory_get_usage(true);
    }

    public function displayMetrics(OutputInterface $output): void
    {
        $duration = microtime(true) - $this->startTime;
        $endMemory = memory_get_usage(true);
        $memoryUsed = $endMemory - $this->startMemory;

        $durationFormatted = number_format($duration, 2);
        $memoryUsedFormatted = number_format($memoryUsed / 1024 / 1024, 2);

        $output->writeln('');
        $output->writeln("<info>Duration: {$durationFormatted}s | Memory used: {$memoryUsedFormatted}MB</info>");
    }
}
