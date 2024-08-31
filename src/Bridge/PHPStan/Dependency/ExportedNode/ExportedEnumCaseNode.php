<?php

declare(strict_types=1);

namespace JDecool\PHPStanReport\Bridge\PHPStan\Dependency\ExportedNode;

use JDecool\PHPStanReport\Bridge\PHPStan\Dependency\ExportedNode;

final class ExportedEnumCaseNode implements ExportedNode
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $value,
        public readonly ?ExportedPhpDocNode $phpDoc,
    ) {}

    /**
     * @param mixed[] $properties
     * @return self
     */
    public static function __set_state(array $properties): ExportedNode
    {
        return new self(
            $properties['name'],
            $properties['value'],
            $properties['phpDoc'],
        );
    }
}
