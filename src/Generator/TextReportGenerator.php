<?php

namespace JDecool\PHPStanReport\Generator;

use JDecool\PHPStanReport\Runner\PHPStanResultCache;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Output\OutputInterface;

final class TextReportGenerator implements ReportGenerator
{
    public function generate(OutputInterface $output, PHPStanResultCache $result): void
    {
        $output->write("Processing results...\n\n");

        $output->write("* Total error(s): {$result->countTotalErrors()}\n");
        $output->write("  * Error(s): {$result->countErrors()}\n");
        $output->write("  * Locally ignored error(s): {$result->countLocallyIgnoredErrors()}\n");
        $output->write("  * Line(s) to ignore: {$result->countLinesToIgnore()}\n\n");

        $output->write("Summary\n");
        $output->write("-------\n\n");

        $rows = $this->createSummaryTableRows($result);
        (new Table($output))
            ->setHeaders(['Identifier', 'Count'])
            ->setRows($rows)
            ->render()
        ;
    }

    public static function format(): string
    {
        return 'text';
    }

    private function createSummaryTableRows(PHPStanResultCache $result): array
    {
        $rows = [];
        foreach ($result->getErrorsMap() as $identifier => $count) {
            $rows[] = [$identifier, $count];
        }

        $rows[] = new TableSeparator();
        $rows[] = ['Total', $result->countTotalErrors()];

        return $rows;
    }
}
