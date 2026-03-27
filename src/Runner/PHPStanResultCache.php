<?php

declare(strict_types=1);

namespace JDecool\PHPStanReport\Runner;

use PHPStan\Analyser\Error;

/**
 * @phpstan-type ErrorCollection array<string, Error[]>
 */
final class PHPStanResultCache extends ResultCache
{
    public static function fromFile(string $file): self
    {
        if (!file_exists($file)) {
            throw new \RuntimeException("File {$file} does not exist.");
        }

        $data = require $file;

        return new static($data);
    }
}
