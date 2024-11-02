<?php

namespace JDecool\PHPStanReport\Runner;

use PHPStan\Analyser\Error;

/**
 * @phpstan-type ErrorCollection array<string, Error[]>
 */
abstract class ResultCache
{
    protected int $countTotalErrors;

    protected int $countErrors;

    protected int $countLocallyIgnoredErrors;

    protected int $countLinesToIgnore;

    /**
     * @var array<string, int>
     */
    protected array $errorMap = [];

    public function __construct(
        protected readonly array $data,
    ) {}

    /**
     * @return array<string, int>
     */
    public function getErrorsMap(): array
    {
        if (!empty($this->errorMap)) {
            return $this->errorMap;
        }

        $map = [];

        foreach ($this->getErrors() as $errors) {
            foreach ($errors as $error) {
                $map[$error->getIdentifier()] ??= 0;
                $map[$error->getIdentifier()]++;
            }
        }

        foreach ($this->getLocallyIgnoredErrors() as $errors) {
            foreach ($errors as $error) {
                $map[$error->getIdentifier()] ??= 0;
                $map[$error->getIdentifier()]++;
            }
        }

        ksort($map);

        return $this->errorMap = $map;
    }

    public function countTotalErrors(): int
    {
        return $this->countTotalErrors ??= $this->countErrors() + $this->countLocallyIgnoredErrors();
    }

    public function countErrors(): int
    {
        return $this->countErrors ??= $this->computerErrors($this->getErrors());
    }

    public function countLocallyIgnoredErrors(): int
    {
        return $this->countLocallyIgnoredErrors ??= $this->computerErrors($this->getLocallyIgnoredErrors());
    }

    public function countLinesToIgnore(): int
    {
        return $this->countLinesToIgnore ??= array_reduce(
            $this->getLinesToIgnore(),
            static function (int $counter, array $lines): int {
                return $counter + array_reduce($lines, static fn(int $c, $l): int => $c + count($l), 0);
            },
            0,
        );
    }

    /**
     * @return Error[]
     */
    public function filterByIdentifier(string $identifier): array
    {
        $map = [];

        foreach ($this->getErrors() as $errors) {
            foreach ($errors as $error) {
                if ($error->getIdentifier() !== $identifier) {
                    continue;
                }

                $map[] = $error;
            }
        }

        foreach ($this->getLocallyIgnoredErrors() as $errors) {
            foreach ($errors as $error) {
                if ($error->getIdentifier() !== $identifier) {
                    continue;
                }

                $map[] = $error;
            }
        }

        return $map;
    }

    public function toArray(): array
    {
        return [
            'count_total_errors' => $this->countTotalErrors(),
            'count_errors' => $this->countErrors(),
            'count_locally_ignored_errors' => $this->countLocallyIgnoredErrors(),
            'count_lines_to_ignores' => $this->countLinesToIgnore(),
            'errors_map' => $this->getErrorsMap(),
        ];
    }

    /**
     * @param ErrorCollection $errors
     */
    protected function computerErrors(array $errors): int
    {
        return array_reduce(
            $errors,
            static fn(int $counter, array $error): int => $counter + count($error),
            0,
        );
    }

    /**
     * @return ErrorCollection
     */
    abstract public function getErrors(): array;

    /**
     * @return ErrorCollection
     */
    abstract public function getLocallyIgnoredErrors(): array;

    /**
     * @return array<string, array<string, array<int, null>>>
     */
    abstract public function getLinesToIgnore(): array;
}
