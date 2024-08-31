<?php

declare(strict_types=1);

namespace JDecool\PHPStanReport\Bridge\PHPStan\Dependency\ExportedNode;

use JDecool\PHPStanReport\Bridge\PHPStan\Dependency\ExportedNode;

final class ExportedTraitUseAdaptation implements ExportedNode
{
    /**
     * @param string[]|null $insteadOfs
     */
    private function __construct(
        public readonly ?string $traitName,
        public readonly string $method,
        public readonly ?int $newModifier,
        public readonly ?string $newName,
        public readonly ?array $insteadOfs,
    ) {}

    /**
     * @param mixed[] $properties
     * @return self
     */
    public static function __set_state(array $properties): ExportedNode
    {
        return new self(
            $properties['traitName'],
            $properties['method'],
            $properties['newModifier'],
            $properties['newName'],
            $properties['insteadOfs'],
        );
    }
}
