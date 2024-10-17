<?php

namespace JDecool\PHPStanReport\Generator;

use JDecool\PHPStanReport\Runner\PHPStanResultCache;

interface ReportGenerator
{
    public function generate(PHPStanResultCache $result, SortField $sortBy): string;

    public static function format(): string;
}
