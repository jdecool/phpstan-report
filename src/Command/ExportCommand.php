<?php

namespace JDecool\PHPStanReport\Command;

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
    ) {
        parent::__construct('export');
    }

    protected function configure(): void
    {
        $this->ignoreValidationErrors();

        $this->addOption('format', 'f', InputOption::VALUE_OPTIONAL, 'Output format', 'gitlab');
        $this->addOption('without-analyze', null, InputOption::VALUE_NONE, 'Do not run the analysis');
        $this->setDescription('Generate a report from the PHPStan cache analysis');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $statusCode = Command::SUCCESS;
        if (!$input->getOption('without-analyze')) {
            $statusCode = $this->phpstan->analyze();
        }

        $parameters = $this->phpstan->dumpParameters();

        $this->exporter
            ->get($input->getOption('format'))
            ->export($output, $parameters->getResultCache());

        return $statusCode;
    }
}
