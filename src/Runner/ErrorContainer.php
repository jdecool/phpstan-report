<?php

declare(strict_types=1);

namespace JDecool\PHPStanReport\Runner;

use PHPStan\Analyser\Error;

class ErrorContainer
{
    /**
     * @var array<string, Error[]>
     */
    private array $raw = [];

    /**
     * @var array<string, Error[]>
     */
    private array $errorsByIdentifier = [];

    /**
     * @var array<string, positive-int>
     */
    private array $map = [];

    private int $total = 0;

    public function addError(string $file, Error $error): void
    {
        $this->raw[$file][] = $error;

        $this->errorsByIdentifier[$error->getIdentifier()] ??= [];
        $this->errorsByIdentifier[$error->getIdentifier()][] = $error;

        $this->map[$error->getIdentifier()] ??= 0;
        $this->map[$error->getIdentifier()]++;

        $this->total++;
    }

    /**
     * @return array<string, Error[]>
     */
    public function getRawErrors(): array
    {
        return $this->raw;
    }

    /**
     * @return array<Error>
     */
    public function getByIdentifier(string $identifier): array
    {
        return $this->errorsByIdentifier[$identifier] ?? [];
    }

    public function getTotalErrors(): int
    {
        return $this->total;
    }

    /**
     * @return array<string, positive-int>
     */
    public function getMap(): array
    {
        return $this->map;
    }
}
