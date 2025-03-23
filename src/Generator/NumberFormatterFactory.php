<?php

declare(strict_types=1);

namespace JDecool\PHPStanReport\Generator;

use NumberFormatter;

class NumberFormatterFactory
{
    public function create(): NumberFormatter
    {
        return new NumberFormatter('en_US', NumberFormatter::DECIMAL);
    }
}
