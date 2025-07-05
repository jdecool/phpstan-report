<?php

declare(strict_types=1);

namespace JDecool\PHPStanReport\Command;

use JDecool\PHPStanReport\Runner\PHPStanRunner;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Terminal;

final class ViewCommand extends Command
{
    public function __construct(
        private readonly PHPStanRunner $phpstan,
    ) {
        parent::__construct('view');
    }

    protected function configure(): void
    {
        $this->addArgument('identifier', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'Error identifier');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $parameters = $this->phpstan->dumpParameters();
        $resultCache = $parameters->getResultCache();

        $identifiers = $input->getArgument('identifier');
        $ignoredErrors = $resultCache->filterByIdentifier(...$identifiers);

        if (empty($ignoredErrors)) {
            $output->writeln('<info>No errors found</info>');

            return Command::SUCCESS;
        }

        $this->renderOutput($output, $identifiers, $ignoredErrors);

        return Command::SUCCESS;
    }

    private function renderOutput(OutputInterface $output, array $identifiers, array $ignoredErrors): void
    {
        $output->writeln(sprintf('<info>Found %d errors with identifier: %s</info>', count($ignoredErrors), implode(', ', $identifiers)));
        $output->writeln('');

        $terminal = new Terminal();
        $terminalWidth = $terminal->getWidth();

        $table = new Table($output);
        $table->setHeaders(['Identifier', 'Message', 'File', 'Line']);
        $table->setStyle('default');

        $availableWidth = max(80, $terminalWidth - 10);

        $messageMaxWidth = (int) ($availableWidth * 0.4);
        $fileMaxWidth = (int) ($availableWidth * 0.3);
        $tipMaxWidth = (int) ($availableWidth * 0.2);

        $table->setColumnMaxWidth(1, $messageMaxWidth);
        $table->setColumnMaxWidth(2, $fileMaxWidth);
        $table->setColumnMaxWidth(4, $tipMaxWidth);

        $errorCount = count($ignoredErrors);
        foreach ($ignoredErrors as $index => $error) {
            $table->addRow([
                $error->getIdentifier(),
                $error->getMessage(),
                $error->getFile(),
                $error->getLine() ?? 'N/A',
            ]);

            if ($index < $errorCount - 1) {
                $table->addRow(new TableSeparator());
            }
        }

        $table->render();
    }
}
