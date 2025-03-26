<?php

declare(strict_types=1);

namespace JDecool\PHPStanReport\Tests\PHPUnit\Generator;

use JDecool\PHPStanReport\Generator\JsonReportGenerator;
use JDecool\PHPStanReport\Generator\SortField;
use JDecool\PHPStanReport\Runner\PHPStanResultCache;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;

final class JsonReportGeneratorTest extends TestCase
{
    private JsonReportGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->generator = new JsonReportGenerator();
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

        static::assertJsonStringEqualsJsonString($expected, $output);
    }

    public static function generateReportProvider(): iterable
    {
        yield [
            SortField::Identifier,
            file_get_contents(__DIR__ . '/../../data/expected/generator/json-generator-identifier-ordered.json'),
        ];

        yield [
            SortField::Occurrence,
            file_get_contents(__DIR__ . '/../../data/expected/generator/json-generator-counter-ordered.json'),
        ];
    }
}
