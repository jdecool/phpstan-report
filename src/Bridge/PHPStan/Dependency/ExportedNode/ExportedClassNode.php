<?php

declare(strict_types=1);

namespace JDecool\PHPStanReport\Bridge\PHPStan\Dependency\ExportedNode;

use JDecool\PHPStanReport\Bridge\PHPStan\Dependency\ExportedNode;
use JDecool\PHPStanReport\Bridge\PHPStan\Dependency\RootExportedNode;

final class ExportedClassNode implements RootExportedNode
{
    /**
     * @param string[] $implements
     * @param string[] $usedTraits
     * @param ExportedTraitUseAdaptation[] $traitUseAdaptations
     * @param ExportedNode[] $statements
     * @param ExportedAttributeNode[] $attributes
     */
    public function __construct(
        public readonly string $name,
        public readonly ?ExportedPhpDocNode $phpDoc,
        public readonly bool $abstract,
        public readonly bool $final,
        public readonly ?string $extends,
        public readonly array $implements,
        public readonly array $usedTraits,
        public readonly array $traitUseAdaptations,
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
            $properties['phpDoc'],
            $properties['abstract'],
            $properties['final'],
            $properties['extends'],
            $properties['implements'],
            $properties['usedTraits'],
            $properties['traitUseAdaptations'],
            $properties['statements'],
            $properties['attributes'],
        );
    }
}
