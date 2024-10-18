<?php

namespace JDecool\PHPStanReport\Runner;

use PHPStan\Analyser\Error;

/**
 * @phpstan-type ErrorCollection array<string, Error[]>
 */
final class PHPStanResultCache
{
    private int $countTotalErrors;

    /** @var ErrorCollection */
    private array $errors;

    private int $countErrors;

    /** @var ErrorCollection */
    private array $locallyIgnoredErrors;

    private int $countLocallyIgnoredErrors;

    private array $linesToIgnore;

    private int $countLinesToIgnore;

    /**
     * @var array<string, int>
     */
    private array $errorMap = [];

    public function __construct(
        private readonly array $data,
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

    /**
     * @return ErrorCollection
     */
    public function getErrors(): array
    {
        return $this->errors ??= $this->data['errorsCallback']();
    }

    public function countErrors(): int
    {
        return $this->countErrors ??= $this->computerErrors($this->getErrors());
    }

    /**
     * @return ErrorCollection
     */
    public function getLocallyIgnoredErrors(): array
    {
        return $this->locallyIgnoredErrors ??= $this->data['locallyIgnoredErrorsCallback']();
    }

    public function countLocallyIgnoredErrors(): int
    {
        return $this->countLocallyIgnoredErrors ??= $this->computerErrors($this->getLocallyIgnoredErrors());
    }

    public function getLinesToIgnore(): array
    {
        return $this->linesToIgnore ??= $this->data['linesToIgnore'];
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
    private function computerErrors(array $errors): int
    {
        return array_reduce(
            $errors,
            static fn(int $counter, array $error): int => $counter + count($error),
            0,
        );
    }
}
