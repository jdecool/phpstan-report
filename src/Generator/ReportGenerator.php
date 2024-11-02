<?php

namespace JDecool\PHPStanReport\Generator;

use JDecool\PHPStanReport\Runner\PHPStanResultCache;
use JDecool\PHPStanReport\Runner\ResultCache;

interface ReportGenerator
{
    public function generate(ResultCache $result, SortField $sortBy): string;

    public static function format(): string;
}
