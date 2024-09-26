<?php

namespace JDecool\PHPStanReport\Command;

use JDecool\PHPStanReport\Bridge\PHPStan\Command as Bridge;
use JDecool\PHPStanReport\Exporter\ReportExporter;
use JDecool\PHPStanReport\Runner\PHPStanRunner;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;

final class ExportCommand extends Command
{
    /**
     * @param ServiceLocator<ReportExporter> $exporter
     */
    public function __construct(
        private readonly PHPStanRunner $phpstan,
        private readonly ServiceLocator $exporter,
        private readonly Bridge\AnalyseCommandDefinition $analyseCommandDefinition,
    ) {
        parent::__construct('export');
    }

    protected function configure(): void
    {
        $this->ignoreValidationErrors();

        // setup PHPStan analyze command definition
        $this->setDefinition($this->analyseCommandDefinition->getInputDefinition());

        $this->addOption('export-output-format', null, InputOption::VALUE_OPTIONAL, 'Output format (' . implode(',', $this->getAllowedOutputFormats()) . ')', 'gitlab');
        $this->addOption('export-without-analyze', null, InputOption::VALUE_NONE, 'Do not run the analysis');
        $this->setDescription('Generate a report from the PHPStan cache analysis');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $outputFormat = $input->getOption('export-output-format');
        if (!$this->exporter->has($outputFormat)) {
            $output->writeln('<error>Invalid --export-output-format value option (allowed: ' . implode(', ', $this->getAllowedOutputFormats()) . ').</error>');

            return Command::INVALID;
        }

        $statusCode = Command::SUCCESS;
        if (!$input->getOption('export-without-analyze')) {
            $statusCode = $this->phpstan->analyze();
        }

        $parameters = $this->phpstan->dumpParameters();

        $this->exporter
            ->get($outputFormat)
            ->export($output, $parameters->getResultCache());

        return $statusCode;
    }

    /**
     * @return string[]
     */
    private function getAllowedOutputFormats(): array
    {
        $allowedFormats = array_keys($this->exporter->getProvidedServices());

        sort($allowedFormats);

        return $allowedFormats;
    }
}
