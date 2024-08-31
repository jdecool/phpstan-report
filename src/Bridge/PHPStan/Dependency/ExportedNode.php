<?php

declare(strict_types=1);

namespace JDecool\PHPStanReport\Bridge\PHPStan\Dependency;

interface ExportedNode
{
    /**
     * @param mixed[] $properties
     */
    public static function __set_state(array $properties): self;
}
