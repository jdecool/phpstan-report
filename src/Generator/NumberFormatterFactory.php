<?php

declare(strict_types=1);

namespace JDecool\PHPStanReport\Generator;

use NumberFormatter;

class NumberFormatterFactory
{
    public function create(): NumberFormatter
    {
        $locale = setlocale(LC_ALL, '');

        return new NumberFormatter($locale, NumberFormatter::DECIMAL);
    }
}
