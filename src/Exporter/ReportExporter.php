<?php

namespace JDecool\PHPStanReport\Exporter;

use JDecool\PHPStanReport\Runner\PHPStanResultCache;
use Symfony\Component\Console\Output\OutputInterface;

interface ReportExporter
{
    public function export(PHPStanResultCache $result): string;

    public static function format(): string;
}
