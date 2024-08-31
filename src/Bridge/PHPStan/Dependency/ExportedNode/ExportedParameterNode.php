<?php

declare(strict_types=1);

namespace JDecool\PHPStanReport\Bridge\PHPStan\Dependency\ExportedNode;

use JDecool\PHPStanReport\Bridge\PHPStan\Dependency\ExportedNode;

final class ExportedParameterNode implements ExportedNode
{
    /**
     * @param ExportedAttributeNode[] $attributes
     */
    public function __construct(
        public readonly string $name,
        public readonly ?string $type,
        public readonly bool $byRef,
        public readonly bool $variadic,
        public readonly bool $hasDefault,
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
            $properties['type'],
            $properties['byRef'],
            $properties['variadic'],
            $properties['hasDefault'],
            $properties['attributes'],
        );
    }
}
