<?php

declare(strict_types=1);

namespace JDecool\PHPStanReport\Bridge\PHPStan\Dependency\ExportedNode;

use JDecool\PHPStanReport\Bridge\PHPStan\Dependency\ExportedNode;

final class ExportedClassConstantsNode implements ExportedNode
{
    /**
     * @param ExportedClassConstantNode[] $constants
     */
    public function __construct(
        public readonly array $constants,
        public readonly bool $public,
        public readonly bool $private,
        public readonly bool $final,
        public readonly ?ExportedPhpDocNode $phpDoc,
    ) {}

    /**
     * @param mixed[] $properties
     * @return self
     */
    public static function __set_state(array $properties): ExportedNode
    {
        return new self(
            $properties['constants'],
            $properties['public'],
            $properties['private'],
            $properties['final'],
            $properties['phpDoc'],
        );
    }
}
