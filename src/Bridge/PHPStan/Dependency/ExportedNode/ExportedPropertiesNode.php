<?php

declare(strict_types=1);

namespace JDecool\PHPStanReport\Bridge\PHPStan\Dependency\ExportedNode;

use JDecool\PHPStanReport\Bridge\PHPStan\Dependency\ExportedNode;

final class ExportedPropertiesNode implements ExportedNode
{
    /**
     * @param string[] $names
     * @param ExportedAttributeNode[] $attributes
     */
    public function __construct(
        public readonly array $names,
        public readonly ?ExportedPhpDocNode $phpDoc,
        public readonly ?string $type,
        public readonly bool $public,
        public readonly bool $private,
        public readonly bool $static,
        public readonly bool $readonly,
        public readonly array $attributes,
    ) {}

    /**
     * @param mixed[] $properties
     * @return self
     */
    public static function __set_state(array $properties): ExportedNode
    {
        return new self(
            $properties['names'],
            $properties['phpDoc'],
            $properties['type'],
            $properties['public'],
            $properties['private'],
            $properties['static'],
            $properties['readonly'],
            $properties['attributes'],
        );
    }
}
