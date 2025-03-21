<?php

declare(strict_types=1);

namespace JDecool\PHPStanReport\Tests\PHPUnit\Runner;

use JDecool\PHPStanReport\Runner\PHPStanResultCache;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PHPStanResultCacheTest extends TestCase
{
    #[Test]
    public function levelExtraction(): void
    {
        $result = PHPStanResultCache::fromFile(__DIR__ . '/../../data/cache.php');

        self::assertSame('max', $result->getLevel());
    }
}
