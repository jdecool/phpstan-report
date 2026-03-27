<?php

declare(strict_types=1);

namespace JDecool\PHPStanReport\Command;

use JDecool\PHPStanReport\Bridge\PHPStan\Command as Bridge;
use JDecool\PHPStanReport\Generator\ReportGenerator;
use JDecool\PHPStanReport\Generator\SortField;
use JDecool\PHPStanReport\Logger\ExecutionMetrics;
use JDecool\PHPStanReport\Runner\CachedResultCache;
use JDecool\PHPStanReport\Runner\ExecutionResult;
use JDecool\PHPStanReport\Runner\FilteredResultCache;
use JDecool\PHPStanReport\Runner\PHPStanParameters;
use JDecool\PHPStanReport\Runner\PHPStanRunner;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\Filesystem\Filesystem;
use Throwable;

final class AnalyzeCommand extends Command
{
    /**
     * @var string[]
     */
    private static array $allowedOutputFormats = [];

    /**
     * @param ServiceLocator<ReportGenerator> $generator
     */
    public function __construct(
        private readonly PHPStanRunner $phpstan,
        private readonly ServiceLocator $generator,
        private readonly LoggerInterface $logger,
        private readonly Bridge\AnalyseCommandDefinition $analyseCommandDefinition,
        private readonly Filesystem $fs,
        private readonly ExecutionMetrics $metrics,
        private readonly string $projectRoot,
    ) {
        parent::__construct();

        foreach ($this->getDefinition()->getOptions() as $option) {
            if (!str_starts_with($option->getName(), 'report-')) {
                continue;
            }

            $this->phpstan->registerOptionToIgnore($option->getName());
        }
    }

    public static function getDefaultName(): string
    {
        return 'analyze';
    }

    /**
     * @return string[]
     */
    public function getAliases(): array
    {
        return ['analyse'];
    }

    protected function configure(): void
    {
        $this->ignoreValidationErrors();

        // setup PHPStan analyze command definition
        $this->setDefinition($this->analyseCommandDefinition->getInputDefinition());

        $this->addOption('report-continue-on-error', null, InputOption::VALUE_NONE, 'Continue the analysis if error occurred');
        $this->addOption('report-output-format', null, InputOption::VALUE_OPTIONAL, 'Output format (allowed: ' . implode(', ', $this->getAllowedOutputFormats()) . ')', 'text');
        $this->addOption('report-without-analyze', null, InputOption::VALUE_NONE, 'Do not run the analysis');
        $this->addOption('report-maximum-allowed-errors', null, InputOption::VALUE_OPTIONAL, 'Maximum allowed errors');
        $this->addOption('report-sort-by', null, InputOption::VALUE_OPTIONAL, 'Sort report result (allowed: ' . implode(', ', SortField::allowedValues()) . ')', SortField::None->value);
        $this->addOption('report-exclude-identifier', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Identifier to exclude from the report');
        $this->addOption('report-heatmap', null, InputOption::VALUE_OPTIONAL, 'Generate a heatmap of files with most ignored errors and save to specified path');

        /**
         * @var ReportGenerator $generator
         */
        foreach ($this->generator->getProvidedServices() as $outputFormat => $generator) {
            $reportGenerator = $this->generator->get($outputFormat);

            $reportGenerator->addCommandOptions($this);

            if ($reportGenerator->canBeDumpedInFile()) {
                $this->addOption("report-file-{$outputFormat}", null, InputOption::VALUE_OPTIONAL, "Output file for {$outputFormat} report");
            }
        }

        $this->setDescription('Start the PHPStan analysis and generate a report');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->metrics->start();

        $outputFormat = $input->getOption('report-output-format');
        if (!$this->generator->has($outputFormat)) {
            $output->writeln('<error>Invalid --report-output-format value option (allowed: ' . implode(', ', $this->getAllowedOutputFormats()) . ').</error>');

            $this->metrics->displayMetrics($output);

            return Command::INVALID;
        }

        $reportSortBy = SortField::tryFrom($input->getOption('report-sort-by'));
        if ($reportSortBy === null) {
            $output->writeln('<error>Invalid --report-sort-by value option (allowed: ' . implode(', ', SortField::allowedValues()) . ').</error>');

            $this->metrics->displayMetrics($output);

            return Command::INVALID;
        }

        $executionResult = new ExecutionResult(Command::SUCCESS);
        $parameters = null;

        if (!$input->getOption('report-without-analyze')) {
            $executionResult = $this->phpstan->analyze();
            $parameters = $this->phpstan->dumpParameters();
        } else {
            $parameters = $this->phpstan->dumpParameters();
        }

        // Create cache key based on analysis parameters
        $cacheKey = $this->createCacheKey($input, $parameters);

        // Try to load from cache
        $cachedResultCache = new CachedResultCache(
            $parameters->getResultCache(),
            $cacheKey,
            $this->logger,
            $this->fs,
            $this->projectRoot,
        );

        $resultCache = $parameters->getResultCache();

        // Use cached results if available and valid
        if ($cachedResultCache->isCacheValid()) {
            $output->writeln('<info>Using cached analysis results</info>');

            // Load cached data and create a new ResultCache with cached statistics
            $cachedData = $cachedResultCache->loadFromCache();
            if ($cachedData !== null) {
                // For now, we'll use the current result cache but this could be enhanced
                // to restore full cached state in future versions
                $output->writeln(sprintf(
                    '<info>Cache info: %d total errors, %d lines ignored</info>',
                    $cachedData['count_total_errors'],
                    $cachedData['count_lines_to_ignores'],
                ));
            }
        } else {
            // Save current results to cache for future runs
            $cachedResultCache->saveToCache();
        }

        try {
            ($output instanceof ConsoleOutputInterface)
                ? $output->getErrorOutput()->write("{$executionResult->output}")
                : $output->write($executionResult->output);

            $this->generateReport(
                $input,
                $output,
                $parameters,
                $executionResult,
                $outputFormat,
                $input->getOption('report-continue-on-error'),
                $input->getOption('report-exclude-identifier'),
                $reportSortBy,
            );
        } catch (Throwable $e) {
            $this->logger->debug("PHPStan report generation failed: {$e->getMessage()}", [
                'exception' => $e,
                'parameters' => $parameters->toArray(),
            ]);

            throw $e;
        }

        foreach ($this->getAllowedOutputFormats() as $format) {
            $outputFile = $input->hasOption("report-file-{$format}") ? $input->getOption("report-file-{$format}") : null;
            if ($outputFile !== null) {
                $output = $this->generator
                    ->get($format)
                    ->generate($input, $resultCache, $reportSortBy);

                $this->fs->dumpFile($outputFile, $output);
            }
        }

        $maximumAllowedErrors = $input->getOption('report-maximum-allowed-errors');
        if (is_numeric($maximumAllowedErrors)) {
            $maximumAllowedErrors = (int) $maximumAllowedErrors;
            if ($maximumAllowedErrors <= $resultCache->countTotalErrors()) {
                $output->writeln("<error>Maximum allowed errors exceeded ($maximumAllowedErrors allowed).</error>");
                $executionResult = $executionResult->hasFailed() ? $executionResult : new ExecutionResult(255, $executionResult->output);
            }
        }

        $this->metrics->displayMetrics($output);

        return $executionResult->exitCode;
    }

    /**
     * @param string[] $excludedErrorIdentifiers
     */
    private function generateReport(
        InputInterface $input,
        OutputInterface $output,
        PHPStanParameters $parameters,
        ExecutionResult $executionResult,
        string $format,
        bool $continueOnError,
        array $excludedErrorIdentifiers,
        SortField $sortedBy,
    ): void {
        if (!$continueOnError && $executionResult->hasFailed()) {
            $this->logger->debug("PHPStan analysis failed", [
                'parameters' => $parameters->toArray(),
            ]);

            $output->writeln('<error>PHPStan analysis failed, no report generated.</error>');

            return;
        }

        $resultCache = $parameters->getResultCache();
        if (!empty($excludedErrorIdentifiers)) {
            $resultCache = FilteredResultCache::fromResultatCache($resultCache, $excludedErrorIdentifiers);
        }

        $result = $this->generator
            ->get($format)
            ->generate($input, $resultCache, $sortedBy);

        $output->writeln($result);
    }

    private function createCacheKey(InputInterface $input, PHPStanParameters $parameters): string
    {
        // Create a unique cache key based on relevant analysis parameters
        $paramData = $parameters->toArray();

        $cacheComponents = [
            'configuration_file' => $input->getOption('configuration') ?? '',
            'level' => $paramData['level'] ?? '',
            'memory_limit' => $input->getOption('memory-limit') ?? '',
            'result_cache_path' => $paramData['resultCachePath'] ?? '',
        ];

        return serialize($cacheComponents);
    }

    /**
     * @return string[]
     */
    private function getAllowedOutputFormats(): array
    {
        if (!empty(self::$allowedOutputFormats)) {
            return self::$allowedOutputFormats;
        }

        $allowedFormats = array_keys($this->generator->getProvidedServices());

        sort($allowedFormats);

        return self::$allowedOutputFormats = $allowedFormats;
    }
}
