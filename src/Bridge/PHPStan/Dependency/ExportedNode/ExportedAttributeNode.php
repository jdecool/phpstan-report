<?php

declare(strict_types=1);

namespace JDecool\PHPStanReport\Bridge\PHPStan\Dependency\ExportedNode;

use JDecool\PHPStanReport\Bridge\PHPStan\Dependency\ExportedNode;

final class ExportedAttributeNode implements ExportedNode
{
    /**
     * @param array<int|string, string> $args argument name or index(string|int) => value expression (string)
     */
    public function __construct(
        public readonly string $name,
        public readonly array $args,
    ) {}

    /**
     * @param mixed[] $properties
     * @return self
     */
    public static function __set_state(array $properties): ExportedNode
    {
        return new self(
            $properties['name'],
            $properties['args'],
        );
    }
}
