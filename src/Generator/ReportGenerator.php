<?php

declare(strict_types=1);

namespace JDecool\PHPStanReport\Generator;

use JDecool\PHPStanReport\Runner\ResultCache;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

interface ReportGenerator
{
    public function addCommandOptions(Command $command): void;

    public function canBeDumpedInFile(): bool;

    public function generate(InputInterface $input, ResultCache $result, SortField $sortBy = SortField::None): string;

    public static function format(): string;
}
