<?php

namespace JDecool\PHPStanReport\Exporter;

use JDecool\PHPStanReport\Runner\PHPStanResultCache;
use Symfony\Component\Console\Output\OutputInterface;

interface ReportExporter
{
    public function export(OutputInterface $output, PHPStanResultCache $result): void;

    public static function format(): string;
}
