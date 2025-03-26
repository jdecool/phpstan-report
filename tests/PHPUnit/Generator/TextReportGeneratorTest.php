<?php

declare(strict_types=1);

namespace JDecool\PHPStanReport\Tests\PHPUnit\Generator;

use JDecool\PHPStanReport\Generator\NumberFormatterFactory;
use JDecool\PHPStanReport\Generator\SortField;
use JDecool\PHPStanReport\Generator\TextReportGenerator;
use JDecool\PHPStanReport\Runner\PHPStanResultCache;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;

final class TextReportGeneratorTest extends TestCase
{
    private TextReportGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->generator = new TextReportGenerator(
            (new NumberFormatterFactory())->create(),
        );
    }

    #[Test]
    #[DataProvider('generateReportProvider')]
    public function generateReport(SortField $sort, string $expected): void
    {
        $output = $this->generator->generate(
            $this->createMock(InputInterface::class),
            PHPStanResultCache::fromFile(__DIR__ . '/../../data/cache.php'),
            $sort,
        );

        static::assertSame($expected, $output);
    }

    public static function generateReportProvider(): iterable
    {
        yield [
            SortField::Identifier,
            file_get_contents(__DIR__ . '/../../data/expected/generator/text-generator-identifier-ordered.txt'),
        ];

        yield [
            SortField::Occurrence,
            file_get_contents(__DIR__ . '/../../data/expected/generator/text-generator-counter-ordered.txt'),
        ];
    }
}
