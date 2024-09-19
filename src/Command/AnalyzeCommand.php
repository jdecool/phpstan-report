<?php

namespace JDecool\PHPStanReport\Command;

use JDecool\PHPStanReport\Generator\ReportGenerator;
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
    ) {
        parent::__construct('analyze');
    }

    protected function configure(): void
    {
        $this->ignoreValidationErrors();

        $this->addOption('continue-on-error', 'c', InputOption::VALUE_NONE, 'Continue the analysis if error occured');
        $this->addOption('format', 'f', InputOption::VALUE_OPTIONAL, 'Output format', 'text');
        $this->addOption('without-analyze', null, InputOption::VALUE_NONE, 'Do not run the analysis');
        $this->addOption('maximum-allowed-errors', 'm', InputOption::VALUE_OPTIONAL, 'Maximum allowed errors');
        $this->setDescription('Start the PHPStan analysis and generate a report');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $statusCode = Command::SUCCESS;
        if (!$input->getOption('without-analyze')) {
            $statusCode = $this->phpstan->analyze();
        }

        $parameters = $this->phpstan->dumpParameters();

        try {
            $this->generateReport($output, $parameters, $statusCode, $input->getOption('format'), $input->getOption('continue-on-error'));
        } catch (Throwable $e) {
            $this->logger->debug("PHPStan report generation failed: {$e->getMessage()}", [
                'exception' => $e,
                'parameters' => $parameters->toArray(),
            ]);

            throw $e;
        }

        if ($input->hasOption('maximum-allowed-errors')) {
            $maximumAllowedErrors = (int) $input->getOption('maximum-allowed-errors');
            if ($maximumAllowedErrors <= $parameters->getResultCache()->countTotalErrors()) {
                $output->writeln("<error>Maximum allowed errors exceeded ($maximumAllowedErrors allowed).</error>");
                $statusCode = $statusCode !== Command::SUCCESS ? $statusCode : 255;
            }
        }

        return $statusCode;
    }

    private function generateReport(OutputInterface $output, PHPStanParameters $parameters, int $statusCode, string $format, bool $continueOnError): void
    {
        if ($continueOnError || $statusCode === 0) {
            $this->generator
                ->get($format)
                ->generate($output, $parameters->getResultCache());
        } else {
            $this->logger->debug("PHPStan analysis failed", [
                'parameters' => $parameters->toArray(),
            ]);

            $output->writeln('<error>PHPStan analysis failed, no report generated.</error>');
        }
    }
}
