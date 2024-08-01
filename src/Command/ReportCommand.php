<?php

namespace JDecool\PHPStanReport\Command;

use JDecool\PHPStanReport\Report\ReportGenerator;
use JDecool\PHPStanReport\Runner\PHPStanRunner;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;

final class ReportCommand extends Command
{
    public const NAME = 'generate';

    /**
     * @param ServiceLocator<ReportGenerator> $generator
     */
    public function __construct(
        private readonly PHPStanRunner $phpstan,
        private readonly ServiceLocator $generator,
    ) {
        parent::__construct(self::NAME);
    }

    protected function configure(): void
    {
        $this->ignoreValidationErrors();

        $this->addOption('format', 'f', InputOption::VALUE_OPTIONAL, 'Output format', 'text');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $parameters = $this->phpstan->dumpParameters();

        $statusCode = $this->phpstan->analyze();

        if ($statusCode === 0) {
            $this->generator
                ->get($input->getOption('format'))
                ->generate($output, $parameters->getResultCache());
        } else {
            $output->writeln('<error>PHPStan analysis failed, no report generated.</error>');
        }

        return $statusCode;
    }
}
