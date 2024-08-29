<?php

namespace JDecool\PHPStanReport\Application;

use Symfony\Component\Console\Input\ArgvInput;

class Context
{
    public function __construct(
        private readonly ArgvInput $argv,
        private readonly bool $debug,
    ) {}

    public function isDebug(): bool
    {
        return $this->debug || $this->argv->hasParameterOption(['--debug']);
    }

    public function getPhpstanBinary(): string
    {
        global $_composer_bin_dir;

        if ($this->argv->hasParameterOption(['--phpstan-bin'])) {
            $bin = $this->argv->getParameterOption(['--phpstan-bin']);

            if (!file_exists($bin)) {
                throw new \RuntimeException("File {$bin} does not exist.");
            }

            return $bin;
        }

        $binDir = $_composer_bin_dir ?? __DIR__ . '/../../vendor/bin';
        $phpstanBin = "$binDir/phpstan";
        if (!file_exists($phpstanBin)) {
            throw new \RuntimeException();
        }

        return $phpstanBin;
    }
}
