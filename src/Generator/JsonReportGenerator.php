<?php

namespace JDecool\PHPStanReport\Generator;

use JDecool\PHPStanReport\Runner\PHPStanResultCache;
use Symfony\Component\Console\Output\OutputInterface;

final class JsonReportGenerator implements ReportGenerator
{
    public function generate(OutputInterface $output, PHPStanResultCache $result, SortField $sortBy = SortField::Identifier): void
    {
        $data = $result->toArray();

        match ($sortBy) {
            SortField::Identifier => ksort($data['errors_map']),
            SortField::Counter => arsort($data['errors_map']),
        };

        $output->writeln(json_encode($data, flags: JSON_PRETTY_PRINT));
    }

    public static function format(): string
    {
        return 'json';
    }
}
