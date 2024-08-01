<?php

namespace JDecool\PHPStanReport\Generator;

use JDecool\PHPStanReport\Runner\PHPStanResultCache;
use Symfony\Component\Console\Output\OutputInterface;

final class JsonReportGenerator implements ReportGenerator
{
    public function generate(OutputInterface $output, PHPStanResultCache $result): void
    {
        $output->writeln(json_encode($result->toArray(), flags: JSON_PRETTY_PRINT));
    }

    public static function format(): string
    {
        return 'json';
    }
}
