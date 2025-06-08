<?php

declare(strict_types=1);

namespace JDecool\PHPStanReport\Generator;

enum SortField: string
{
    case None = 'none';
    case Identifier = 'identifier';
    case Occurrence = 'occurrence';

    /**
     * @return string[]
     */
    public static function allowedValues(): array
    {
        return array_map(
            static fn(self $field): string => $field->value,
            array_filter(self::cases(), static fn(self $field): bool => $field !== self::None),
        );
    }
}
