<?php

namespace JDecool\PHPStanReport\Command;

use JDecool\PHPStanReport\Report\ReportGenerator;
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

        $this->addOption('format', 'f', InputOption::VALUE_OPTIONAL, 'Output format', 'text');
        $this->setDescription('Start the PHPStan analysis and generate a report');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $parameters = $this->phpstan->dumpParameters();

        $statusCode = $this->phpstan->analyze();

        try {
            $this->generateReport($output, $parameters, $statusCode, $input->getOption('format'));
        } catch (Throwable $e) {
            $this->logger->debug("PHPStan report generation failed: {$e->getMessage()}", [
                'exception' => $e,
                'parameters' => $parameters->toArray(),
            ]);

            throw $e;
        }

        return $statusCode;
    }

    private function generateReport(OutputInterface $output, PHPStanParameters $parameters, int $statusCode, string $format): void
    {
        if ($statusCode === 0) {
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
