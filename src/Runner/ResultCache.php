<?php

declare(strict_types=1);

namespace JDecool\PHPStanReport\Runner;

use PHPStan\Analyser\Error;

use function Amp\async;
use function Amp\Future\await;

/**
 * @phpstan-type ErrorCollection array<string, Error[]>
 */
abstract class ResultCache
{
    protected array $rawData;

    protected ErrorContainer $errors;

    protected ErrorContainer $locallyIgnoredErrors;

    protected int $countLinesToIgnore;

    /**
     * @var array<string, int>
     */
    protected array $errorMap = [];

    public function __construct(array $data)
    {
        $this->rawData = $this->initialize($data);
    }

    public function getLevel(): string
    {
        return $this->rawData['meta']['level'];
    }

    /**
     * @return ErrorCollection
     */
    public function getErrors(): array
    {
        return $this->errors->getRawErrors();
    }

    /**
     * @return ErrorCollection
     */
    public function getLocallyIgnoredErrors(): array
    {
        return $this->locallyIgnoredErrors->getRawErrors();
    }

    /**
     * @return array<string, array<string, array<int, null>>>
     */
    public function getLinesToIgnore(): array
    {
        return $this->rawData['linesToIgnore'];
    }

    /**
     * @return array<string, int>
     */
    public function getErrorsMap(): array
    {
        if (!empty($this->errorMap)) {
            return $this->errorMap;
        }

        $map = $this->errors->getMap();

        foreach ($this->locallyIgnoredErrors->getMap() as $identifier => $counter) {
            $map[$identifier] ??= 0;
            $map[$identifier] += $counter;
        }

        ksort($map);

        return $this->errorMap = $map;
    }

    public function countTotalErrors(): int
    {
        return $this->errors->getTotalErrors() + $this->locallyIgnoredErrors->getTotalErrors();
    }

    public function countErrors(): int
    {
        return $this->errors->getTotalErrors();
    }

    public function countLocallyIgnoredErrors(): int
    {
        return $this->locallyIgnoredErrors->getTotalErrors();
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
    public function filterByIdentifier(string $identifier, string ...$identifiers): array
    {
        $errorsIdentifiers = array_merge([$identifier], $identifiers);

        $map = [];

        foreach ($errorsIdentifiers as $errorIdentifier) {
            $map = array_merge(
                $map,
                $this->errors->getByIdentifier($errorIdentifier),
                $this->locallyIgnoredErrors->getByIdentifier($errorIdentifier),
            );
        }

        return $map;
    }

    public function toArray(): array
    {
        return [
            'level' => $this->getLevel(),
            'count_total_errors' => $this->countTotalErrors(),
            'count_errors' => $this->countErrors(),
            'count_locally_ignored_errors' => $this->countLocallyIgnoredErrors(),
            'count_lines_to_ignores' => $this->countLinesToIgnore(),
            'errors_map' => $this->getErrorsMap(),
        ];
    }

    protected function initialize(array $data): array
    {
        [$this->errors, $this->locallyIgnoredErrors] = await([
            async(fn() => $this->filterErrors($data['errorsCallback'])),
            async(fn() => $this->filterErrors($data['locallyIgnoredErrorsCallback'])),
        ]);

        return $data;
    }

    protected function filterErrors(callable $fn): ErrorContainer
    {
        $container = new ErrorContainer();

        foreach ($fn() as $file => $errors) {
            foreach ($errors as $error) {
                if ($this->isExcludedError($error)) {
                    continue;
                }

                $container->addError($file, $error);
            }
        }

        return $container;
    }

    protected function isExcludedError(Error $error): bool
    {
        return false;
    }
}
