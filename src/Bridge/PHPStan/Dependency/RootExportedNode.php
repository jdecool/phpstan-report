<?php

declare(strict_types=1);

namespace JDecool\PHPStanReport\Bridge\PHPStan\Dependency;

interface RootExportedNode extends ExportedNode
{
    public const TYPE_CLASS = 'class';

    public const TYPE_INTERFACE = 'interface';

    public const TYPE_ENUM = 'enum';

    public const TYPE_TRAIT = 'trait';

    public const TYPE_FUNCTION = 'function';

}
