<?php

declare(strict_types=1);

namespace JDecool\PHPStanReport\Runner;

final class PHPStanParameters
{
    public function __construct(
        private readonly array $data,
    ) {}

    public function getResultCache(): PHPStanResultCache
    {
        return new PHPStanResultCache(ResultCacheReader::read($this->data['resultCachePath']));
    }

    public function toArray(): array
    {
        return $this->data;
    }
}
