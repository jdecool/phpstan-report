<?php

declare(strict_types=1);

namespace JDecool\PHPStanReport\Tests\PHPUnit\Runner;

use JDecool\PHPStanReport\Runner\FilteredResultCache;
use JDecool\PHPStanReport\Tests\PHPUnit\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

final class FilteredResultCacheTest extends TestCase
{
    #[Test]
    public function levelExtraction(): void
    {
        $result = FilteredResultCache::fromFile(__DIR__ . '/../../data/cache.php', ['binaryOp.invalid', 'class.notFound']);

        self::assertSame('max', $result->getLevel());
    }

    #[Test]
    public function countTotalErrors(): void
    {
        $result = FilteredResultCache::fromFile(__DIR__ . '/../../data/cache.php', ['binaryOp.invalid', 'class.notFound']);

        self::assertSame(3, $result->countTotalErrors());
    }

    #[Test]
    public function countErrors(): void
    {
        $result = FilteredResultCache::fromFile(__DIR__ . '/../../data/cache.php', ['binaryOp.invalid', 'class.notFound']);

        self::assertSame(1, $result->countErrors());
    }

    #[Test]
    public function countLocallyIgnoredErrors(): void
    {
        $result = FilteredResultCache::fromFile(__DIR__ . '/../../data/cache.php', ['binaryOp.invalid', 'class.notFound']);

        self::assertSame(2, $result->countLocallyIgnoredErrors());
    }

    #[Test]
    public function countLinesToIgnore(): void
    {
        $result = FilteredResultCache::fromFile(__DIR__ . '/../../data/cache.php', ['binaryOp.invalid', 'class.notFound']);

        self::assertSame(2, $result->countLinesToIgnore());
    }

    #[Test]
    public function getErrorsMap(): void
    {
        $result = FilteredResultCache::fromFile(__DIR__ . '/../../data/cache.php', ['binaryOp.invalid', 'class.notFound']);

        static::assertDumpFileEquals($result->getErrorsMap(), __DIR__ . '/data/filtered_result_error_map.dump');
    }

    #[Test]
    public function toArray(): void
    {
        $result = FilteredResultCache::fromFile(__DIR__ . '/../../data/cache.php', ['binaryOp.invalid', 'class.notFound']);

        static::assertDumpFileEquals($result->toArray(), __DIR__ . '/data/filtered_result_to_array.dump');
    }

    #[Test]
    public function getErrors(): void
    {
        $result = FilteredResultCache::fromFile(__DIR__ . '/../../data/cache.php', ['binaryOp.invalid', 'class.notFound']);

        static::assertDumpFileEquals($result->getErrors(), __DIR__ . '/data/filtered_result_get_errors.dump');
    }

    #[Test]
    public function getLocallyIgnoredErrors(): void
    {
        $result = FilteredResultCache::fromFile(__DIR__ . '/../../data/cache.php', ['binaryOp.invalid', 'class.notFound']);

        static::assertDumpFileEquals($result->getLocallyIgnoredErrors(), __DIR__ . '/data/filtered_result_get_locally_ignored_errors.dump');
    }

    #[Test]
    public function getLinesToIgnore(): void
    {
        $result = FilteredResultCache::fromFile(__DIR__ . '/../../data/cache.php', ['binaryOp.invalid', 'class.notFound']);

        static::assertDumpFileEquals($result->getLinesToIgnore(), __DIR__ . '/data/filtered_result_lines_to_ignore.dump');
    }

    /**
     * @param array<string> $identifiers
     */
    #[Test]
    #[DataProvider('filterByIdentifierProvider')]
    public function filterByIdentifier(string $expectedFile, array $identifiers): void
    {
        $result = FilteredResultCache::fromFile(__DIR__ . '/../../data/cache.php', ['binaryOp.invalid', 'class.notFound']);

        static::assertDumpFileEquals($result->filterByIdentifier(...$identifiers), $expectedFile);
    }

    public static function filterByIdentifierProvider(): iterable
    {
        yield [__DIR__ . '/data/filtered_result_filter_by_identifier_1param.dump', ['argument.type']];
        yield [__DIR__ . '/data/filtered_result_filter_by_identifier_2params.dump', ['missingType.property', 'variable.undefined']];
    }
}
