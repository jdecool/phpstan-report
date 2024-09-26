<?php

namespace JDecool\PHPStanReport\Generator;

enum SortField: string
{
    case Identifier = 'identifier';
    case Counter = 'counter';

    /**
     * @return string[]
     */
    public static function allowedValues(): array
    {
        return array_map(static fn(self $field): string => $field->value, self::cases());
    }
}
