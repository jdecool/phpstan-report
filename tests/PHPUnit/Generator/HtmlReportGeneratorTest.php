<?php

declare(strict_types=1);

namespace JDecool\PHPStanReport\Tests\PHPUnit\Generator;

use JDecool\PHPStanReport\Generator\HtmlReportGenerator;
use JDecool\PHPStanReport\Generator\NumberFormatterFactory;
use JDecool\PHPStanReport\Generator\SortField;
use JDecool\PHPStanReport\Runner\PHPStanResultCache;
use JDecool\PHPStanReport\Tests\PHPUnit\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Console\Input\InputInterface;

final class HtmlReportGeneratorTest extends TestCase
{
    private HtmlReportGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->generator = new HtmlReportGenerator(
            (new NumberFormatterFactory())->create(),
        );
    }

    #[Test]
    #[DataProvider('generateReportProvider')]
    public function generateReport(SortField $sort, string $expectedFile): void
    {
        $output = $this->generator->generate(
            $this->createMock(InputInterface::class),
            PHPStanResultCache::fromFile(__DIR__ . '/../../data/cache.php'),
            $sort,
            '2025-01-01 12:00:00',
        );

        $expected = file_get_contents($expectedFile);

        self::assertEquals($expected, $output);
    }

    public static function generateReportProvider(): iterable
    {
        yield [
            SortField::Identifier,
            __DIR__ . '/../../data/expected/generator/html-generator-identifier-ordered.html',
        ];

        yield [
            SortField::Occurrence,
            __DIR__ . '/../../data/expected/generator/html-generator-counter-ordered.html',
        ];
    }
}
