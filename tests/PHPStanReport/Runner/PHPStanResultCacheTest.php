<?php

declare(strict_types=1);

namespace JDecool\PHPStanReport\Tests\PHPStanReport\Runner;

use JDecool\PHPStanReport\Runner\PHPStanResultCache;
use PHPUnit\Framework\TestCase;

final class PHPStanResultCacheTest extends TestCase
{
    public function testLevelExtraction(): void
    {
        $result = PHPStanResultCache::fromFile(__DIR__ . '/../../data/cache.php');

        self::assertSame('max', $result->getLevel());
    }
}
