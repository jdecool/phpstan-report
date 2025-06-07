<?php

declare(strict_types=1);

namespace JDecool\PHPStanReport\Tests\PHPUnit\Generator;

use JDecool\PHPStanReport\Generator\DistributionGraphReportGenerator;
use JDecool\PHPStanReport\Generator\NumberFormatterFactory;
use JDecool\PHPStanReport\Generator\SortField;
use JDecool\PHPStanReport\Runner\PHPStanResultCache;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;

final class DistributionGraphReportGeneratorTest extends TestCase
{
    private DistributionGraphReportGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->generator = new DistributionGraphReportGenerator(
            (new NumberFormatterFactory())->create(),
        );
    }

    #[Test]
    #[DataProvider('generateReportProvider')]
    public function generateReport(SortField $sort, string $expectedFile, bool $emptyErrors = false): void
    {
        if ($emptyErrors) {
            $emptyCacheData = [
                'locallyIgnoredErrorsCallback' => static fn(): array => [],
                'meta' => [
                    'cacheVersion' => 'v12-linesToIgnore',
                    'phpstanVersion' => '2.0.x-dev',
                    'phpVersion' => PHP_VERSION_ID,
                    'projectConfig' => '{}',
                    'analysedPaths' => [],
                    'scannedFiles' => [],
                    'composerLocks' => [],
                    'composerInstalled' => [],
                    'executedFilesHashes' => [],
                    'phpExtensions' => [],
                    'stubFiles' => [],
                    'level' => '8',
                ],
                'errorsCallback' => static fn(): array => [],
                'linesToIgnore' => [],
                'collectedDataCallback' => static fn(): array => [],
                'lastFullAnalysisTime' => time(),
                'projectExtensionFiles' => [],
                'dependencies' => [],
                'exportedNodesCallback' => static fn(): array => [],
            ];
            $resultCache = new PHPStanResultCache($emptyCacheData);
        } else {
            $cacheFile = __DIR__ . '/../../data/cache.php';
            $resultCache = PHPStanResultCache::fromFile($cacheFile);
        }

        $output = $this->generator->generate(
            $this->createMock(InputInterface::class),
            $resultCache,
            $sort,
        );

        // Normalize line endings in output and expected content
        $normalizedOutput = str_replace("\r\n", "\n", $output);
        $expectedContent = str_replace("\r\n", "\n", file_get_contents($expectedFile));

        static::assertSame($expectedContent, $normalizedOutput);
    }

    public static function generateReportProvider(): iterable
    {
        yield 'identifier ordered' => [
            SortField::Identifier,
            __DIR__ . '/../../data/expected/generator/distribution-graph-identifier-ordered.txt',
            false,
        ];

        yield 'occurrence ordered' => [
            SortField::Occurrence,
            __DIR__ . '/../../data/expected/generator/distribution-graph-occurrence-ordered.txt',
            false,
        ];

        yield 'no ignored errors' => [
            SortField::Identifier, // Sort field doesn't matter much here
            __DIR__ . '/../../data/expected/generator/distribution-graph-no-ignored-errors.txt',
            true,
        ];
    }
}
