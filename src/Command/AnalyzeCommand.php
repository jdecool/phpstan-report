<?php

namespace JDecool\PHPStanReport\Command;

use JDecool\PHPStanReport\Bridge\PHPStan\Command as Bridge;
use JDecool\PHPStanReport\Generator\ReportGenerator;
use JDecool\PHPStanReport\Generator\SortField;
use JDecool\PHPStanReport\Runner\PHPStanParameters;
use JDecool\PHPStanReport\Runner\PHPStanRunner;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Throwable;

final class AnalyzeCommand extends Command
{
    /**
     * @param ServiceLocator<ReportGenerator> $generator
     */
    public function __construct(
        private readonly PHPStanRunner $phpstan,
        private readonly ServiceLocator $generator,
        private readonly LoggerInterface $logger,
        private readonly Bridge\AnalyseCommandDefinition $analyseCommandDefinition,
    ) {
        parent::__construct('analyze');
    }

    protected function configure(): void
    {
        $this->ignoreValidationErrors();

        // setup PHPStan analyze command definition
        $this->setDefinition($this->analyseCommandDefinition->getInputDefinition());

        $this->addOption('report-continue-on-error', null, InputOption::VALUE_NONE, 'Continue the analysis if error occured');
        $this->addOption('report-output-format', null, InputOption::VALUE_OPTIONAL, 'Output format (allowed: ' . implode(', ', $this->getAllowedOutputFormats()) . ')', 'text');
        $this->addOption('report-without-analyze', null, InputOption::VALUE_NONE, 'Do not run the analysis');
        $this->addOption('report-maximum-allowed-errors', null, InputOption::VALUE_OPTIONAL, 'Maximum allowed errors');
        $this->addOption('report-sort-by', null, InputOption::VALUE_OPTIONAL, 'Sort report result (allowed: ' . implode(', ', SortField::allowedValues()) . ')', SortField::Identifier->value);
        $this->setDescription('Start the PHPStan analysis and generate a report');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $outputFormat = $input->getOption('report-output-format');
        if (!$this->generator->has($outputFormat)) {
            $output->writeln('<error>Invalid --report-output-format value option (allowed: ' . implode(', ', $this->getAllowedOutputFormats()) . ').</error>');

            return Command::INVALID;
        }

        $reportSortBy = SortField::tryFrom($input->getOption('report-sort-by'));
        if ($reportSortBy === null) {
            $output->writeln('<error>Invalid --report-sort-by value option (allowed: ' . implode(', ', SortField::allowedValues()) . ').</error>');

            return Command::INVALID;
        }

        $statusCode = Command::SUCCESS;
        if (!$input->getOption('report-without-analyze')) {
            $statusCode = $this->phpstan->analyze();
        }

        $parameters = $this->phpstan->dumpParameters();

        try {
            $this->generateReport($output, $parameters, $statusCode, $outputFormat, $input->getOption('report-continue-on-error'), $reportSortBy);
        } catch (Throwable $e) {
            $this->logger->debug("PHPStan report generation failed: {$e->getMessage()}", [
                'exception' => $e,
                'parameters' => $parameters->toArray(),
            ]);

            throw $e;
        }

        $maximumAllowedErrors = $input->getOption('report-maximum-allowed-errors');
        if (is_numeric($maximumAllowedErrors)) {
            $maximumAllowedErrors = (int) $maximumAllowedErrors;
            if ($maximumAllowedErrors <= $parameters->getResultCache()->countTotalErrors()) {
                $output->writeln("<error>Maximum allowed errors exceeded ($maximumAllowedErrors allowed).</error>");
                $statusCode = $statusCode !== Command::SUCCESS ? $statusCode : 255;
            }
        }

        return $statusCode;
    }

    private function generateReport(OutputInterface $output, PHPStanParameters $parameters, int $statusCode, string $format, bool $continueOnError, SortField $sortedBy): void
    {
        if ($continueOnError || $statusCode === 0) {
            $result = $this->generator
                ->get($format)
                ->generate($parameters->getResultCache(), $sortedBy);

            $output->writeln($result);
        } else {
            $this->logger->debug("PHPStan analysis failed", [
                'parameters' => $parameters->toArray(),
            ]);

            $output->writeln('<error>PHPStan analysis failed, no report generated.</error>');
        }
    }

    /**
     * @return string[]
     */
    private function getAllowedOutputFormats(): array
    {
        $allowedFormats = array_keys($this->generator->getProvidedServices());

        sort($allowedFormats);

        return $allowedFormats;
    }
}
