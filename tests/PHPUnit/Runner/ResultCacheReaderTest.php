<?php

declare(strict_types=1);

namespace JDecool\PHPStanReport\Tests\PHPUnit\Runner;

use JDecool\PHPStanReport\Runner\PHPStanResultCache;
use JDecool\PHPStanReport\Runner\ResultCacheReader;
use JDecool\PHPStanReport\Tests\PHPUnit\TestCase;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;

final class ResultCacheReaderTest extends TestCase
{
    /**
     * `tests/data/cache-serialized.php` holds the same analysis as `tests/data/cache.php`, written in
     * the framed format phpstan/phpstan uses since 2.2.13.
     */
    #[Test]
    public function bothCacheFileFormatsAreRead(): void
    {
        $legacy = PHPStanResultCache::fromFile(__DIR__ . '/../../data/cache.php');
        $serialized = PHPStanResultCache::fromFile(__DIR__ . '/../../data/cache-serialized.php');

        self::assertSame($legacy->toArray(), $serialized->toArray());
        self::assertSame(
            array_map('strval', array_keys($legacy->getErrors())),
            array_map('strval', array_keys($serialized->getErrors())),
        );
        self::assertSame($legacy->getLinesToIgnore(), $serialized->getLinesToIgnore());
    }

    #[Test]
    public function missingFileIsReported(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("'/nope/resultCache.php' not found");

        ResultCacheReader::read('/nope/resultCache.php');
    }

    #[Test]
    public function unsupportedFileIsReported(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'cache') . '.php';
        file_put_contents($file, '<?php return "not an array";');

        try {
            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessage('is not in a supported format');

            ResultCacheReader::read($file);
        } finally {
            unlink($file);
        }
    }

    #[Test]
    public function truncatedFileIsReported(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'cache') . '.php';
        file_put_contents($file, substr(file_get_contents(__DIR__ . '/../../data/cache-serialized.php'), 0, 200));

        try {
            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessage('is truncated');

            ResultCacheReader::read($file);
        } finally {
            unlink($file);
        }
    }
}
