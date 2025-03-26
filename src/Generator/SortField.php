<?php

declare(strict_types=1);

namespace JDecool\PHPStanReport\Generator;

enum SortField: string
{
    case Identifier = 'identifier';
    case Occurrence = 'occurrence';

    /**
     * @return string[]
     */
    public static function allowedValues(): array
    {
        return array_map(static fn(self $field): string => $field->value, self::cases());
    }
}
