<?php

declare(strict_types=1);

namespace JDecool\PHPStanReport\Bridge\PHPStan\Dependency\ExportedNode;

use JDecool\PHPStanReport\Bridge\PHPStan\Dependency\ExportedNode;
use JDecool\PHPStanReport\Bridge\PHPStan\Dependency\RootExportedNode;

final class ExportedFunctionNode implements RootExportedNode
{
    /**
     * @param ExportedParameterNode[] $parameters
     * @param ExportedAttributeNode[] $attributes
     */
    public function __construct(
        public readonly string $name,
        public readonly ?ExportedPhpDocNode $phpDoc,
        public readonly bool $byRef,
        public readonly ?string $returnType,
        public readonly array $parameters,
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
            $properties['phpDoc'],
            $properties['byRef'],
            $properties['returnType'],
            $properties['parameters'],
            $properties['attributes'],
        );
    }
}
