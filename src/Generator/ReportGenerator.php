<?php

declare(strict_types=1);

namespace JDecool\PHPStanReport\Generator;

use JDecool\PHPStanReport\Runner\ResultCache;

interface ReportGenerator
{
    public function canBeDumpedInFile(): bool;

    public function generate(ResultCache $result, SortField $sortBy): string;

    public static function format(): string;
}
