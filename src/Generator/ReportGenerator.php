<?php

namespace JDecool\PHPStanReport\Generator;

use JDecool\PHPStanReport\Runner\PHPStanResultCache;
use Symfony\Component\Console\Output\OutputInterface;

interface ReportGenerator
{
    public function generate(OutputInterface $output, PHPStanResultCache $result): void;

    public static function format(): string;
}