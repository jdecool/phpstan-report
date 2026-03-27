<?php

declare(strict_types=1);

namespace JDecool\PHPStanReport\Runner;

use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;

final class CachedResultCache implements ResultCacheInterface
{
    private const CACHE_DIR = '.phpstan-report-cache';

    public function __construct(
        private readonly ResultCacheInterface $resultCache,
        private readonly string $cacheKey,
        private readonly LoggerInterface $logger,
        private readonly Filesystem $filesystem,
        private readonly string $projectRoot,
    ) {}

    public function getCacheFilePath(): string
    {
        return $this->projectRoot . '/' . self::CACHE_DIR . '/' . md5($this->cacheKey) . '.cache';
    }

    public function saveToCache(): void
    {
        $cacheDir = $this->projectRoot . '/' . self::CACHE_DIR;

        if (!$this->filesystem->exists($cacheDir)) {
            $this->filesystem->mkdir($cacheDir);
        }

        $cacheData = $this->resultCache->toArray();
        $cacheFile = $this->getCacheFilePath();

        $this->filesystem->dumpFile($cacheFile, '<?php return ' . var_export($cacheData, true) . ';');

        $this->logger->debug('Result cache saved', [
            'cache_file' => $cacheFile,
            'errors_count' => $cacheData['count_total_errors'],
        ]);
    }

    public function loadFromCache(): ?array
    {
        $cacheFile = $this->getCacheFilePath();

        if (!file_exists($cacheFile)) {
            return null;
        }

        $cachedData = include $cacheFile;

        $this->logger->debug('Result cache loaded', [
            'cache_file' => $cacheFile,
            'errors_count' => $cachedData['count_total_errors'],
        ]);

        return $cachedData;
    }

    public function isCacheValid(): bool
    {
        $cacheFile = $this->getCacheFilePath();

        if (!file_exists($cacheFile)) {
            return false;
        }

        $cacheTime = filemtime($cacheFile);
        $currentTime = time();

        // Cache is valid for 24 hours
        return ($currentTime - $cacheTime) < 86400;
    }

    // Delegate all methods to the underlying ResultCache
    public function getLevel(): string
    {
        return $this->resultCache->getLevel();
    }

    public function getErrors(): array
    {
        return $this->resultCache->getErrors();
    }

    public function getLocallyIgnoredErrors(): array
    {
        return $this->resultCache->getLocallyIgnoredErrors();
    }

    public function getLinesToIgnore(): array
    {
        return $this->resultCache->getLinesToIgnore();
    }

    public function getErrorsMap(): array
    {
        return $this->resultCache->getErrorsMap();
    }

    public function countTotalErrors(): int
    {
        return $this->resultCache->countTotalErrors();
    }

    public function countErrors(): int
    {
        return $this->resultCache->countErrors();
    }

    public function countLocallyIgnoredErrors(): int
    {
        return $this->resultCache->countLocallyIgnoredErrors();
    }

    public function countLinesToIgnore(): int
    {
        return $this->resultCache->countLinesToIgnore();
    }

    public function filterByIdentifier(string $identifier, string ...$identifiers): array
    {
        return $this->resultCache->filterByIdentifier($identifier, ...$identifiers);
    }

    public function toArray(): array
    {
        return $this->resultCache->toArray();
    }
}
