<?php

declare(strict_types=1);

namespace JDecool\PHPStanReport\Runner;

final class ExecutionResult
{
    public function __construct(
        public readonly int $exitCode,
        public readonly string $output = '',
    ) {}

    public function hasFailed(): bool
    {
        return $this->exitCode !== 0;
    }
}
