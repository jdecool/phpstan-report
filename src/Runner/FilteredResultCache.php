<?php

namespace JDecool\PHPStanReport\Runner;

use PHPStan\Analyser\Error;

/**
 * @phpstan-type ErrorCollection array<string, Error[]>
 */
final class FilteredResultCache extends ResultCache
{
    /** @var ErrorCollection */
    protected array $errors;

    /** @var ErrorCollection */
    protected array $locallyIgnoredErrors;

    protected array $linesToIgnore;

    public static function fromResultatCache(ResultCache $resultCache, array $excludedErrorIdentifiers): self
    {
        return new self($resultCache->data, $excludedErrorIdentifiers);
    }

    public function __construct(
        array $data,
        private readonly array $excludedErrorIdentifiers,
    ) {
        parent::__construct($data);
    }

    /**
     * @return ErrorCollection
     */
    public function getErrors(): array
    {
        return $this->errors ??= $this->filterErrors($this->data['errorsCallback']);
    }

    /**
     * @return ErrorCollection
     */
    public function getLocallyIgnoredErrors(): array
    {
        return $this->locallyIgnoredErrors ??= $this->filterErrors($this->data['locallyIgnoredErrorsCallback']);
    }

    /**
     * @return array<string, array<string, array<int, null>>>
     */
    public function getLinesToIgnore(): array
    {
        return $this->linesToIgnore ??= $this->data['linesToIgnore'];
    }

    /**
     * @param callable(): ErrorCollection $cacheFn
     * @return ErrorCollection
     */
    private function filterErrors(callable $cacheFn): array
    {
        return array_map(
            fn(array $errors) => array_filter(
                $errors,
                fn(Error $error): bool => !in_array($error->getIdentifier(), $this->excludedErrorIdentifiers, true),
            ),
            $cacheFn(),
        );
    }
}
