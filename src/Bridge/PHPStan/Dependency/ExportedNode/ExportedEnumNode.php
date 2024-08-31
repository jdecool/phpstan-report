<?php

declare(strict_types=1);

namespace JDecool\PHPStanReport\Bridge\PHPStan\Dependency\ExportedNode;

use JDecool\PHPStanReport\Bridge\PHPStan\Dependency\ExportedNode;
use JDecool\PHPStanReport\Bridge\PHPStan\Dependency\RootExportedNode;

final class ExportedEnumNode implements RootExportedNode
{
    /**
     * @param string[] $implements
     * @param ExportedNode[] $statements
     * @param ExportedAttributeNode[] $attributes
     */
    public function __construct(
        public readonly string $name,
        public readonly ?string $scalarType,
        public readonly ?ExportedPhpDocNode $phpDoc,
        public readonly array $implements,
        public readonly array $statements,
        public readonly array $attributes,
    ) {}

    /**
     * @param mixed[] $properties
     * @return self
     */
    public static function __set_state(array $properties): ExportedNode
    {
        return new self(
            $properties['name'],
            $properties['scalarType'],
            $properties['phpDoc'],
            $properties['implements'],
            $properties['statements'],
            $properties['attributes'],
        );
    }
}
