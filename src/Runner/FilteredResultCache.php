<?php

declare(strict_types=1);

namespace JDecool\PHPStanReport\Runner;

use PHPStan\Analyser\Error;

final class FilteredResultCache extends ResultCache
{
    public static function fromFile(string $file, array $excludedErrorIdentifiers): self
    {
        if (!file_exists($file)) {
            throw new \RuntimeException("File {$file} does not exist.");
        }

        $data = require $file;

        return new static($data, $excludedErrorIdentifiers);
    }

    public static function fromResultatCache(ResultCache $resultCache, array $excludedErrorIdentifiers): self
    {
        return new self($resultCache->rawData, $excludedErrorIdentifiers);
    }

    public function __construct(
        array $data,
        private readonly array $excludedErrorIdentifiers,
    ) {
        parent::__construct($data);
    }

    protected function isExcludedError(Error $error): bool
    {
        return in_array($error->getIdentifier(), $this->excludedErrorIdentifiers, true);
    }
}
