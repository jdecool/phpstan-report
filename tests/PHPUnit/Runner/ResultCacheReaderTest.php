<?php

declare(strict_types=1);

namespace JDecool\PHPStanReport\Tests\PHPUnit\Runner;

use JDecool\PHPStanReport\Runner\PHPStanResultCache;
use JDecool\PHPStanReport\Runner\ResultCacheReader;
use JDecool\PHPStanReport\Tests\PHPUnit\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use RuntimeException;

final class ResultCacheReaderTest extends TestCase
{
    /**
     * `tests/data/cache-serialized.php` and `tests/data/cache-serialized-split-entries.php` hold the same analysis
     * as `tests/data/cache.php`, written in the framed formats phpstan/phpstan uses since 2.2.13 and 2.3.5.
     */
    #[Test]
    #[TestWith(['cache-serialized.php'])]
    #[TestWith(['cache-serialized-split-entries.php'])]
    public function allCacheFileFormatsAreRead(string $fixture): void
    {
        $legacy = PHPStanResultCache::fromFile(__DIR__ . '/../../data/cache.php');
        $serialized = PHPStanResultCache::fromFile(__DIR__ . '/../../data/' . $fixture);

        self::assertSame($legacy->toArray(), $serialized->toArray());
        self::assertSame(
            array_map('strval', array_keys($legacy->getErrors())),
            array_map('strval', array_keys($serialized->getErrors())),
        );
        self::assertSame($legacy->getLinesToIgnore(), $serialized->getLinesToIgnore());
    }

    #[Test]
    public function splitEntryValueCanBeFalse(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'cache') . '.php';
        file_put_contents($file, "<?php return; ?>\nlinesToIgnore* 1\n8 4\ns:1:\"a\";b:0;");

        try {
            self::assertSame(['a' => false], ResultCacheReader::read($file)['linesToIgnore']);
        } finally {
            unlink($file);
        }
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
