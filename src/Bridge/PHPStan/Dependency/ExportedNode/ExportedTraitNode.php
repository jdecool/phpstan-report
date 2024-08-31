<?php

declare(strict_types=1);

namespace JDecool\PHPStanReport\Bridge\PHPStan\Dependency\ExportedNode;

use JDecool\PHPStanReport\Bridge\PHPStan\Dependency\ExportedNode;
use JDecool\PHPStanReport\Bridge\PHPStan\Dependency\RootExportedNode;

final class ExportedTraitNode implements RootExportedNode
{
    public function __construct(
        public readonly string $traitName,
    ) {}

    /**
     * @param mixed[] $properties
     * @return self
     */
    public static function __set_state(array $properties): ExportedNode
    {
        return new self($properties['traitName']);
    }

}
