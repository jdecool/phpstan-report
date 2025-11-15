<?php

declare(strict_types=1);

namespace JDecool\PHPStanReport\Tests\PHPUnit\Runner;

use JDecool\PHPStanReport\Runner\PHPStanResultCache;
use JDecool\PHPStanReport\Tests\PHPUnit\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

final class PHPStanResultCacheTest extends TestCase
{
    #[Test]
    public function levelExtraction(): void
    {
        $result = PHPStanResultCache::fromFile(__DIR__ . '/../../data/cache.php');

        self::assertSame('max', $result->getLevel());
    }

    #[Test]
    public function countTotalErrors(): void
    {
        $result = PHPStanResultCache::fromFile(__DIR__ . '/../../data/cache.php');

        self::assertSame(6, $result->countTotalErrors());
    }

    #[Test]
    public function countErrors(): void
    {
        $result = PHPStanResultCache::fromFile(__DIR__ . '/../../data/cache.php');

        self::assertSame(2, $result->countErrors());
    }

    #[Test]
    public function countLocallyIgnoredErrors(): void
    {
        $result = PHPStanResultCache::fromFile(__DIR__ . '/../../data/cache.php');

        self::assertSame(4, $result->countLocallyIgnoredErrors());
    }

    #[Test]
    public function countLinesToIgnore(): void
    {
        $result = PHPStanResultCache::fromFile(__DIR__ . '/../../data/cache.php');

        self::assertSame(2, $result->countLinesToIgnore());
    }

    #[Test]
    public function getErrorsMap(): void
    {
        $result = PHPStanResultCache::fromFile(__DIR__ . '/../../data/cache.php');

        static::assertDumpFileEquals($result->getErrorsMap(), __DIR__ . '/data/result_error_map.dump');
    }

    #[Test]
    public function toArray(): void
    {
        $result = PHPStanResultCache::fromFile(__DIR__ . '/../../data/cache.php');

        static::assertDumpFileEquals($result->toArray(), __DIR__ . '/data/result_to_array.dump');
    }

    #[Test]
    public function getErrors(): void
    {
        $result = PHPStanResultCache::fromFile(__DIR__ . '/../../data/cache.php');

        static::assertDumpFileEquals($result->getErrors(), __DIR__ . '/data/result_get_errors.dump');
    }

    #[Test]
    public function getLocallyIgnoredErrors(): void
    {
        $result = PHPStanResultCache::fromFile(__DIR__ . '/../../data/cache.php');

        static::assertDumpFileEquals($result->getLocallyIgnoredErrors(), __DIR__ . '/data/result_get_locally_ignored_errors.dump');
    }

    #[Test]
    public function getLinesToIgnore(): void
    {
        $result = PHPStanResultCache::fromFile(__DIR__ . '/../../data/cache.php');

        static::assertDumpFileEquals($result->getLinesToIgnore(), __DIR__ . '/data/result_lines_to_ignore.dump');
    }

    /**
     * @param array<string> $identifiers
     */
    #[Test]
    #[DataProvider('filterByIdentifierProvider')]
    public function filterByIdentifier(string $expectedFile, array $identifiers): void
    {
        $result = PHPStanResultCache::fromFile(__DIR__ . '/../../data/cache.php');

        static::assertDumpFileEquals($result->filterByIdentifier(...$identifiers), $expectedFile);
    }

    public static function filterByIdentifierProvider(): iterable
    {
        yield [__DIR__ . '/data/result_filter_by_identifier_1param.dump', ['binaryOp.invalid']];
        yield [__DIR__ . '/data/result_filter_by_identifier_2params.dump', ['binaryOp.invalid', 'class.notFound']];
    }
}
