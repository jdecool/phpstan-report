<?php

declare(strict_types=1);

namespace JDecool\PHPStanReport\Bridge\PHPStan\Dependency\ExportedNode;

use JDecool\PHPStanReport\Bridge\PHPStan\Dependency\ExportedNode;

final class ExportedPhpDocNode implements ExportedNode
{
    /**
     * @param array<string, string> $uses alias(string) => fullName(string)
     * @param array<string, string> $constUses alias(string) => fullName(string)
     */
    public function __construct(
        public readonly string $phpDocString,
        public readonly ?string $namespace,
        public readonly array $uses,
        public readonly array $constUses,
    ) {}

    /**
     * @param mixed[] $properties
     * @return self
     */
    public static function __set_state(array $properties): ExportedNode
    {
        return new self(
            $properties['phpDocString'],
            $properties['namespace'],
            $properties['uses'],
            $properties['constUses'] ?? [],
        );
    }

}
