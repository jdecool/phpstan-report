<?php

declare(strict_types=1);

namespace JDecool\PHPStanReport\Runner;

interface ResultCacheInterface
{
    public function getLevel(): string;

    public function getErrors(): array;

    public function getLocallyIgnoredErrors(): array;

    public function getLinesToIgnore(): array;

    public function getErrorsMap(): array;

    public function countTotalErrors(): int;

    public function countErrors(): int;

    public function countLocallyIgnoredErrors(): int;

    public function countLinesToIgnore(): int;

    public function filterByIdentifier(string $identifier, string ...$identifiers): array;

    public function toArray(): array;
}
