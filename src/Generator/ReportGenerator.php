<?php

namespace JDecool\PHPStanReport\Generator;

use JDecool\PHPStanReport\Runner\PHPStanResultCache;
use Symfony\Component\Console\Output\OutputInterface;

interface ReportGenerator
{
    public function generate(PHPStanResultCache $result, SortField $sortBy): string;

    public static function format(): string;
}
