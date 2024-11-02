<?php

namespace JDecool\PHPStanReport\Generator;

use JDecool\PHPStanReport\Runner\PHPStanResultCache;
use JDecool\PHPStanReport\Runner\ResultCache;

final class JsonReportGenerator implements ReportGenerator
{
    public function generate(ResultCache $result, SortField $sortBy = SortField::Identifier): string
    {
        $data = $result->toArray();

        match ($sortBy) {
            SortField::Identifier => ksort($data['errors_map']),
            SortField::Counter => arsort($data['errors_map']),
        };

        return json_encode($data, flags: JSON_PRETTY_PRINT);
    }

    public static function format(): string
    {
        return 'json';
    }
}
