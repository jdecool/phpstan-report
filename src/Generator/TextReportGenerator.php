<?php

declare(strict_types=1);

namespace JDecool\PHPStanReport\Generator;

use JDecool\PHPStanReport\Runner\ResultCache;
use NumberFormatter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;

final class TextReportGenerator implements ReportGenerator
{
    public function __construct(
        private readonly NumberFormatter $formatter,
    ) {}

    public function addCommandOptions(Command $command): void {}

    public function canBeDumpedInFile(): bool
    {
        return true;
    }

    public function generate(InputInterface $input, ResultCache $result, SortField $sortBy = SortField::None): string
    {
        $output = new BufferedOutput();

        $output->write("Processing results...\n\n");

        $output->write("* Level: {$result->getLevel()}\n");
        $output->write("* Total error(s): {$this->formatter->format($result->countTotalErrors(), NumberFormatter::DECIMAL)}\n");
        $output->write("  * Error(s): {$this->formatter->format($result->countErrors(), NumberFormatter::DECIMAL)}\n");
        $output->write("  * Locally ignored error(s): {$this->formatter->format($result->countLocallyIgnoredErrors(), NumberFormatter::DECIMAL)}\n");
        $output->write("  * Line(s) to ignore: {$this->formatter->format($result->countLinesToIgnore(), NumberFormatter::DECIMAL)}\n");

        if ($result->countTotalErrors() > 0) {
            $output->write("\nSummary\n");
            $output->write("-------\n\n");

            $rows = $this->createSummaryTableRows($result, $sortBy);
            (new Table($output))
                ->setHeaders(['Identifier', 'Count'])
                ->setRows($rows)
                ->render()
            ;
        }

        return $output->fetch();
    }

    public static function format(): string
    {
        return 'text';
    }

    private function createSummaryTableRows(ResultCache $result, SortField $sortBy): array
    {
        $errorsMap = $result->getErrorsMap();
        match ($sortBy) {
            SortField::Identifier => ksort($errorsMap),
            SortField::Occurrence => arsort($errorsMap),
            SortField::None => $errorsMap,
        };

        $rows = [];
        foreach ($errorsMap as $identifier => $count) {
            $rows[] = [$identifier, $this->formatter->format($count, NumberFormatter::DECIMAL)];
        }

        $rows[] = new TableSeparator();
        $rows[] = ['Total', $this->formatter->format($result->countTotalErrors(), NumberFormatter::DECIMAL)];

        return $rows;
    }
}
