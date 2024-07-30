<?php

namespace JDecool\PHPStanReport\Runner;

final class PHPStanParameters
{
    public function __construct(
        private readonly array $data,
    ) {
    }

    public function getResultCache(): PHPStanResultCache
    {
        if (!file_exists($this->data['resultCachePath'])) {
            throw new \RuntimeException('PHPStan result cache file not found.');
        }

        $cache = require $this->data['resultCachePath'];

        return new PHPStanResultCache($cache);
    }
}
