<?php

declare(strict_types=1);

namespace JDecool\PHPStanReport\Runner;

use PHPStan\Analyser\Error;

/**
 * @phpstan-type ErrorCollection array<string, Error[]>
 */
final class PHPStanResultCache extends ResultCache
{
    /** @var ErrorCollection */
    protected array $errors;

    /** @var ErrorCollection */
    protected array $locallyIgnoredErrors;

    protected array $linesToIgnore;

    public static function fromFile(string $file): self
    {
        if (!file_exists($file)) {
            throw new \RuntimeException("File {$file} does not exist.");
        }

        $data = require $file;

        return new static($data);
    }

    /**
     * @return ErrorCollection
     */
    public function getErrors(): array
    {
        return $this->errors ??= $this->data['errorsCallback']();
    }

    /**
     * @return ErrorCollection
     */
    public function getLocallyIgnoredErrors(): array
    {
        return $this->locallyIgnoredErrors ??= $this->data['locallyIgnoredErrorsCallback']();
    }

    /**
     * @return array<string, array<string, array<int, null>>>
     */
    public function getLinesToIgnore(): array
    {
        return $this->linesToIgnore ??= $this->data['linesToIgnore'];
    }
}
