<?php

declare(strict_types=1);

namespace JDecool\PHPStanReport\Generator;

use JDecool\PHPStanReport\Runner\ResultCache;
use NumberFormatter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

final class DistributionGraphReportGenerator implements ReportGenerator
{
    public function __construct(
        private readonly NumberFormatter $formatter,
    ) {}

    public function addCommandOptions(Command $command): void {}

    public function canBeDumpedInFile(): bool
    {
        return true;
    }

    public function generate(InputInterface $input, ResultCache $result, SortField $sortBy = SortField::None): string
    {
        $data = $result->toArray();

        $data['errors_map'] = match ($sortBy) {
            SortField::None => $this->sortByNormalDistribution($data['errors_map']),
            SortField::Identifier => $this->sortByKey($data['errors_map']),
            SortField::Occurrence => $this->sortByValue($data['errors_map']),
        };

        if (empty($data['errors_map'])) {
            return "No ignored errors found.\n";
        }

        $output = "Ignored Error Distribution:\n\n";
        $maxCount = 0;
        foreach ($data['errors_map'] as $currentCount) {
            if ($currentCount > $maxCount) {
                $maxCount = $currentCount;
            }
        }

        foreach ($data['errors_map'] as $identifier => $currentCount) {
            $barLength = ($maxCount > 0) ? (int) (($currentCount / $maxCount) * 50) : 0;
            $bar = str_repeat('#', $barLength);
            $formattedCount = $this->formatter->format($currentCount, NumberFormatter::DECIMAL);
            $output .= sprintf("%-50s | %s (%s)\n", $identifier, $bar, $formattedCount);
        }

        $totalIgnored = array_sum($data['errors_map']);
        $output .= "\nTotal ignored errors: " . $this->formatter->format($totalIgnored, NumberFormatter::DECIMAL) . "\n";

        return $output;
    }

    public static function format(): string
    {
        return 'distribution';
    }

    private function sortByNormalDistribution(array $errorsMap): array
    {
        asort($errorsMap);

        $distributedData = [];

        foreach ($errorsMap as $key => $value) {
            $size = count($distributedData);
            $position = intval($size / 2);

            $distributedData = array_slice($distributedData, 0, $position) + [$key => $value] + $distributedData;
        }

        return array_reverse($distributedData);
    }

    private function sortByKey(array $errorsMap): array
    {
        ksort($errorsMap);

        return $errorsMap;
    }

    private function sortByValue(array $errorsMap): array
    {
        arsort($errorsMap);

        return $errorsMap;
    }
}
