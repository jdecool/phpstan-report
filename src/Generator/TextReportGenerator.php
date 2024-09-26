<?php

namespace JDecool\PHPStanReport\Generator;

use JDecool\PHPStanReport\Runner\PHPStanResultCache;
use NumberFormatter;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class TextReportGenerator implements ReportGenerator
{
    public function __construct(
        private readonly NumberFormatter $formatter,
    ) {}

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
            $rows[] = [$identifier, $this->formatter->format($count, NumberFormatter::DECIMAL)];
        }

        $rows[] = new TableSeparator();
        $rows[] = ['Total', $result->countTotalErrors()];

        return $rows;
    }
}
