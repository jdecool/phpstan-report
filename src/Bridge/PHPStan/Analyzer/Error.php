<?php

declare(strict_types=1);

namespace JDecool\PHPStanReport\Bridge\PHPStan\Analyzer;

use Throwable;

class Error
{
    public function __construct(
        public readonly string $message,
        public readonly string $file,
        public readonly ?int $line = null,
        public readonly bool|Throwable $canBeIgnored = true,
        public readonly ?string $filePath = null,
        public readonly ?string $traitFilePath = null,
        public readonly ?string $tip = null,
        public readonly ?int $nodeLine = null,
        public readonly ?string $nodeType = null,
        public readonly ?string $identifier = null,
        public readonly array $metadata = [],
    ) {}

    /**
     * @param mixed[] $properties
     */
    public static function __set_state(array $properties): self
    {
        return new self(
            $properties['message'],
            $properties['file'],
            $properties['line'],
            $properties['canBeIgnored'],
            $properties['filePath'],
            $properties['traitFilePath'],
            $properties['tip'],
            $properties['nodeLine'] ?? null,
            $properties['nodeType'] ?? null,
            $properties['identifier'] ?? null,
            $properties['metadata'] ?? [],
        );
    }

}
