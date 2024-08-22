<?php

namespace JDecool\PHPStanReport\Runner;

use Psr\Log\LoggerInterface;

final class PHPStanRunnerFactory
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {}

    public function create(): PHPStanRunner
    {
        global $_composer_bin_dir;

        $binDir = $_composer_bin_dir ?? __DIR__ . '/../../vendor/bin';
        $phpstanBin = "$binDir/phpstan";
        if (!file_exists($phpstanBin)) {
            throw new \RuntimeException();
        }

        return new PHPStanRunner($phpstanBin, $this->logger);
    }
}
