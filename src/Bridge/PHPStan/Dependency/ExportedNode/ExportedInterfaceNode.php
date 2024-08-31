<?php

declare(strict_types=1);

namespace JDecool\PHPStanReport\Bridge\PHPStan\Dependency\ExportedNode;

use JDecool\PHPStanReport\Bridge\PHPStan\Dependency\ExportedNode;
use JDecool\PHPStanReport\Bridge\PHPStan\Dependency\RootExportedNode;

final class ExportedInterfaceNode implements RootExportedNode
{
    /**
     * @param string[] $extends
     * @param ExportedNode[] $statements
     */
    public function __construct(
        public readonly string $name,
        public readonly ?ExportedPhpDocNode $phpDoc,
        public readonly array $extends,
        public readonly array $statements,
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
            $properties['extends'],
            $properties['statements'],
        );
    }
}
