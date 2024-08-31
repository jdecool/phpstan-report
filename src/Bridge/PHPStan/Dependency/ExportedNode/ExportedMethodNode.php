<?php

declare(strict_types=1);

namespace JDecool\PHPStanReport\Bridge\PHPStan\Dependency\ExportedNode;

use JDecool\PHPStanReport\Bridge\PHPStan\Dependency\ExportedNode;

final class ExportedMethodNode implements ExportedNode
{
    /**
     * @param ExportedParameterNode[] $parameters
     * @param ExportedAttributeNode[] $attributes
     */
    public function __construct(
        public readonly string $name,
        public readonly ?ExportedPhpDocNode $phpDoc,
        public readonly bool $byRef,
        public readonly bool $public,
        public readonly bool $private,
        public readonly bool $abstract,
        public readonly bool $final,
        public readonly bool $static,
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
            $properties['public'],
            $properties['private'],
            $properties['abstract'],
            $properties['final'],
            $properties['static'],
            $properties['returnType'],
            $properties['parameters'],
            $properties['attributes'],
        );
    }
}
