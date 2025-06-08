<?php

declare(strict_types=1);

namespace JDecool\PHPStanReport\Generator;

use JDecool\PHPStanReport\Runner\ResultCache;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

final class JsonReportGenerator implements ReportGenerator
{
    public function addCommandOptions(Command $command): void {}

    public function canBeDumpedInFile(): bool
    {
        return true;
    }

    public function generate(InputInterface $input, ResultCache $result, SortField $sortBy = SortField::None): string
    {
        $data = $result->toArray();

        match ($sortBy) {
            SortField::Identifier => ksort($data['errors_map']),
            SortField::Occurrence => arsort($data['errors_map']),
            SortField::None => $data['errors_map'],
        };

        return json_encode($data, flags: JSON_PRETTY_PRINT);
    }

    public static function format(): string
    {
        return 'json';
    }
}
