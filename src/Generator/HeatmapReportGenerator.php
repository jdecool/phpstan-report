<?php

declare(strict_types=1);

namespace JDecool\PHPStanReport\Generator;

use JDecool\PHPStanReport\Runner\ResultCache;
use PHPStan\Analyser\Error;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

/**
 * @phpstan-type HeatmapOptions array{
 *     min_width?: int,
 *     max_width?: int,
 *     min_height?: int,
 *     module_min_width?: int,
 *     module_min_height?: int,
 *     module_max_width?: int,
 *     module_max_height?: int,
 *     padding?: int,
 *     title?: string,
 * }
 */
final class HeatmapReportGenerator implements ReportGenerator
{
    private const DEFAULT_OPTIONS = [
        'min_width' => 800,
        'max_width' => 2000,
        'min_height' => 400,
        'module_min_width' => 150,
        'module_min_height' => 80,
        'module_max_width' => 300,
        'module_max_height' => 120,
        'padding' => 20,
        'title' => 'Error Analysis Heatmap',
    ];

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

        return $this->generateHeatmap($data['errors_map'], $result->countTotalErrors(), $result->getLevel());
    }

    public static function format(): string
    {
        return 'heatmap';
    }

    /**
     * @param array<string, int> $errorsMap
     * @param HeatmapOptions $options
     */
    private function generateHeatmap(array $errorsMap, int $totalErrors, string $errorLevel, array $options = []): string
    {
        $options += self::DEFAULT_OPTIONS;

        if (empty($errorsMap) || $totalErrors == 0) {
            return $this->generateEmptyHeatmap($options);
        }

        $errorCount = count($errorsMap);
        $maxCount = max($errorsMap);
        $minCount = min($errorsMap);

        $layout = $this->calculateOptimalLayout($errorCount, $options);

        // Calculate SVG dimensions
        $svgWidth = max($options['min_width'], $layout['total_width']);
        $svgWidth = min($options['max_width'], $svgWidth);

        $headerHeight = 50;
        $summaryHeight = 100;
        $legendHeight = 50;
        $spacing = $options['padding'];

        // Calculate actual content height needed
        $contentHeight = ($layout['module_height'] * $layout['rows']) + ($spacing * ($layout['rows'] - 1));

        // Calculate total SVG height with proper spacing
        $totalCalculatedHeight = $headerHeight + $spacing + $contentHeight + ($spacing * 2) + $summaryHeight + $spacing + $legendHeight + $spacing;
        $svgHeight = max($options['min_height'], $totalCalculatedHeight);

        // Start SVG with responsive viewBox
        $svg = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $svg .= '<svg width="' . $svgWidth . '" height="' . $svgHeight . '" viewBox="0 0 ' . $svgWidth . ' ' . $svgHeight . '" xmlns="http://www.w3.org/2000/svg">' . "\n";

        // Add responsive CSS styles
        $svg .= '<defs>' . "\n";
        $svg .= '<style>' . "\n";
        $svg .= '.title { font-family: Arial, sans-serif; font-size: ' . min(24, max(16, $svgWidth / 40)) . 'px; font-weight: bold; text-anchor: middle; }' . "\n";
        $svg .= '.module-text { font-family: Arial, sans-serif; font-size: ' . min(14, max(10, $layout['module_width'] / 15)) . 'px; font-weight: bold; text-anchor: middle; fill: white; }' . "\n";
        $svg .= '.count-text { font-family: Arial, sans-serif; font-size: ' . min(12, max(8, $layout['module_width'] / 20)) . 'px; text-anchor: middle; fill: white; }' . "\n";
        $svg .= '.summary-text { font-family: Arial, sans-serif; font-size: ' . min(13, max(9, $svgWidth / 70)) . 'px; text-anchor: middle; fill: white; }' . "\n";
        $svg .= '.legend-text { font-family: Arial, sans-serif; font-size: ' . min(11, max(8, $svgWidth / 80)) . 'px; text-anchor: middle; fill: white; }' . "\n";
        $svg .= '.border { stroke: #333; stroke-width: 1; }' . "\n";
        $svg .= '.module { transition: opacity 0.3s; }' . "\n";
        $svg .= '.module:hover { opacity: 0.8; }' . "\n";
        $svg .= '</style>' . "\n";
        $svg .= '</defs>' . "\n";

        // Title section
        $svg .= '<rect x="0" y="0" width="' . $svgWidth . '" height="' . $headerHeight . '" fill="#f0f0f0" class="border"/>' . "\n";
        $svg .= '<text x="' . ($svgWidth / 2) . '" y="' . ($headerHeight / 2 + 5) . '" class="title">' . htmlspecialchars($options['title']) . '</text>' . "\n";

        // Generate error modules with proper row tracking
        $startY = $headerHeight + $spacing;
        $currentX = $spacing;
        $currentY = $startY;
        $currentRow = 0;
        $itemsInCurrentRow = 0;
        $moduleIndex = 0;

        foreach ($errorsMap as $errorType => $count) {
            // Check if we need to move to next row
            if ($itemsInCurrentRow >= $layout['cols']) {
                $currentX = $spacing;
                $currentY = $startY + (($currentRow + 1) * ($layout['module_height'] + $spacing));
                $currentRow++;
                $itemsInCurrentRow = 0;
            }

            $color = $this->getErrorColor($count, $maxCount, $totalErrors);

            // Draw module rectangle with rounded corners for modern look
            $svg .= '<g class="module">' . "\n";
            $svg .= '<rect x="' . $currentX . '" y="' . $currentY . '" width="' . $layout['module_width'] . '" height="' . $layout['module_height'] . '" rx="5" ry="5" fill="' . $color . '" class="border"/>' . "\n";

            // Add error type text (truncate if too long)
            $textX = $currentX + ($layout['module_width'] / 2);
            $textY = $currentY + ($layout['module_height'] / 2) - 8;

            $displayErrorType = strlen($errorType) > 20 ? substr($errorType, 0, 17) . '...' : $errorType;
            $svg .= '<text x="' . $textX . '" y="' . $textY . '" class="module-text">' . htmlspecialchars($displayErrorType) . '</text>' . "\n";

            // Add count and percentage
            $countY = $textY + 18;
            $percentage = round(($count / $totalErrors) * 100, 1);
            $countText = $count . ' error' . ($count > 1 ? 's' : '') . ' (' . $percentage . '%)';
            $svg .= '<text x="' . $textX . '" y="' . $countY . '" class="count-text">' . $countText . '</text>' . "\n";

            // Add tooltip title for accessibility
            $svg .= '<title>' . htmlspecialchars($errorType . ': ' . $count . ' errors (' . $percentage . '%)') . '</title>' . "\n";
            $svg .= '</g>' . "\n";

            $currentX += $layout['module_width'] + $spacing;
            $itemsInCurrentRow++;
            $moduleIndex++;
        }

        // Calculate the actual bottom of the last row of modules
        $lastModuleBottom = $startY + ($currentRow * ($layout['module_height'] + $spacing)) + $layout['module_height'];

        // Summary section - positioned after all modules with proper spacing
        $summaryY = $lastModuleBottom + ($spacing * 2);
        $summaryWidth = $svgWidth - ($spacing * 2);

        $svg .= '<rect x="' . $spacing . '" y="' . $summaryY . '" width="' . $summaryWidth . '" height="' . $summaryHeight . '" rx="5" ry="5" fill="#2c5aa0" class="border"/>' . "\n";

        $summaryTextX = $svgWidth / 2;
        $summaryBaseY = $summaryY + 25;

        $svg .= '<text x="' . $summaryTextX . '" y="' . $summaryBaseY . '" class="module-text">Summary Statistics</text>' . "\n";
        $svg .= '<text x="' . $summaryTextX . '" y="' . ($summaryBaseY + 20) . '" class="summary-text">Total Errors: ' . $totalErrors . ' | Unique Types: ' . $errorCount . '</text>' . "\n";
        $svg .= '<text x="' . $summaryTextX . '" y="' . ($summaryBaseY + 35) . '" class="summary-text">Error Level: ' . $errorLevel . '</text>' . "\n";

        // Most and least common errors
        $mostCommonError = array_keys($errorsMap)[0];
        $leastCommonError = array_keys($errorsMap)[count($errorsMap) - 1];
        $mostCommonCount = $errorsMap[$mostCommonError];
        $leastCommonCount = $errorsMap[$leastCommonError];

        $mostCommonText = 'Most Common: ' . $this->truncateText($mostCommonError, 15) . ' (' . $mostCommonCount . ')';
        $leastCommonText = 'Least Common: ' . $this->truncateText($leastCommonError, 15) . ' (' . $leastCommonCount . ')';

        $svg .= '<text x="' . ($summaryTextX - 100) . '" y="' . ($summaryBaseY + 55) . '" class="summary-text">' . htmlspecialchars($mostCommonText) . '</text>' . "\n";
        $svg .= '<text x="' . ($summaryTextX + 100) . '" y="' . ($summaryBaseY + 55) . '" class="summary-text">' . htmlspecialchars($leastCommonText) . '</text>' . "\n";

        // Dynamic Legend
        $legendY = $summaryY + $summaryHeight + $spacing;
        $legend = $this->generateLegend($errorsMap, $maxCount, $minCount, $totalErrors);

        $legendItemWidth = min(150, ($svgWidth - ($spacing * 2)) / count($legend));
        $currentLegendX = $spacing;

        foreach ($legend as $item) {
            $svg .= '<rect x="' . $currentLegendX . '" y="' . $legendY . '" width="' . $legendItemWidth . '" height="30" rx="3" ry="3" fill="' . $item['color'] . '" class="border"/>' . "\n";
            $svg .= '<text x="' . ($currentLegendX + $legendItemWidth / 2) . '" y="' . ($legendY + 20) . '" class="legend-text">' . htmlspecialchars($item['label']) . '</text>' . "\n";
            $currentLegendX += $legendItemWidth + 10;
        }

        // Close SVG
        $svg .= '</svg>' . "\n";

        return $svg;
    }

    /**
     * @param HeatmapOptions $options
     * @return array{
     *     cols: int,
     *     rows: int,
     *     module_width: int,
     *     module_height: int,
     *     total_width: int,
     *     total_height: int,
     * }
     */
    public function calculateOptimalLayout(int $errorCount, array $options): array
    {
        if ($errorCount <= 0) {
            return [
                'cols' => 1,
                'rows' => 1,
                'module_width' => $options['module_min_width'],
                'module_height' => $options['module_min_height'],
                'total_width' => $options['min_width'],
                'total_height' => $options['module_min_height'],
            ];
        }

        // Calculate optimal columns based on error count
        $optimalCols = ceil(sqrt($errorCount * 1.5)); // Slightly favor width over height
        $optimalCols = max(2, min($optimalCols, 6)); // Between 2-6 columns

        $rows = (int) ceil($errorCount / $optimalCols);

        // Calculate module dimensions
        $availableWidth = $options['max_width'] - ($options['padding'] * ($optimalCols + 1));
        $moduleWidth = min($options['module_max_width'], max($options['module_min_width'], $availableWidth / $optimalCols));

        $moduleHeight = min($options['module_max_height'], max($options['module_min_height'], $moduleWidth * 0.6)); // Maintain aspect ratio

        $totalWidth = ($moduleWidth * $optimalCols) + ($options['padding'] * ($optimalCols + 1));
        $totalHeight = ($moduleHeight * $rows) + ($options['padding'] * ($rows - 1));

        return [
            'cols' => $optimalCols,
            'rows' => $rows,
            'module_width' => $moduleWidth,
            'module_height' => $moduleHeight,
            'total_width' => $totalWidth,
            'total_height' => $totalHeight,
        ];
    }

    public function getErrorColor(int $count, int $maxCount, int $totalErrors): string
    {
        $percentage = $count / $totalErrors;

        if ($count >= $maxCount && $maxCount >= 3) {
            return '#8B0000'; // Dark red for very high counts
        } elseif ($count >= $maxCount && $maxCount == 2) {
            return '#CC0000'; // Red for high counts
        } elseif ($percentage >= 0.2) {
            return '#FF3333'; // Medium-high red
        } elseif ($percentage >= 0.1) {
            return '#FF6666'; // Medium red
        } elseif ($count > 1) {
            return '#FF9999'; // Light red
        } else {
            return '#FFB366'; // Orange for single errors
        }
    }

    /**
     * @param array<string, int> $errorsMap
     * @return array<int, array{color: string, label: string}>
     */
    public function generateLegend(array $errorsMap, int $maxCount, int $minCount, int $totalErrors): array
    {
        $legend = [];
        $counts = array_values($errorsMap);
        $uniqueCounts = array_unique($counts);
        rsort($uniqueCounts); // Sort descending

        // Analyze the actual data distribution to create meaningful categories
        $categories = [];

        foreach ($uniqueCounts as $count) {
            $percentage = ($count / $totalErrors) * 100;
            $color = $this->getErrorColor($count, $maxCount, $totalErrors);

            // Count how many error types have this count
            $errorTypesWithThisCount = array_count_values($counts)[$count];

            // Create label based on actual data
            if ($count == $maxCount && $count >= 5) {
                $label = "Critical ({$count} errors)";
            } elseif ($count == $maxCount && $count >= 3) {
                $label = "High ({$count} errors)";
            } elseif ($count >= 2) {
                if ($errorTypesWithThisCount > 1) {
                    $label = "Medium ({$count} errors each)";
                } else {
                    $label = "Medium ({$count} errors)";
                }
            } elseif ($count == 1) {
                if ($errorTypesWithThisCount > 1) {
                    $label = "Low (1 error each, {$errorTypesWithThisCount} types)";
                } else {
                    $label = "Low (1 error)";
                }
            } else {
                $label = "{$count} errors";
            }

            // Only add if we don't already have this color/category
            $colorExists = false;
            foreach ($categories as $cat) {
                if ($cat['color'] === $color) {
                    $colorExists = true;
                    break;
                }
            }

            if (!$colorExists) {
                $categories[] = [
                    'color' => $color,
                    'label' => $label,
                    'count' => $count,
                    'types_count' => $errorTypesWithThisCount,
                ];
            }
        }

        // If we have too many categories, consolidate similar ones
        if (count($categories) > 4) {
            $legend = $this->consolidateLegendCategories($categories, $maxCount);
        } else {
            $legend = $categories;
        }

        // Always add summary at the end
        $legend[] = ['color' => '#2c5aa0', 'label' => 'Summary'];

        return $legend;
    }

    /**
     * @param array<int, array{color: string, label: string, count: int, types_count: int}> $categories
     * @return array<int, array{color: string, label: string}>
     */
    public function consolidateLegendCategories(array $categories, int $maxCount)
    {
        $consolidated = [];

        // Group by similar error counts or percentages
        $highErrors = [];
        $mediumErrors = [];
        $lowErrors = [];

        foreach ($categories as $cat) {
            if ($cat['count'] >= max(3, $maxCount * 0.7)) {
                $highErrors[] = $cat;
            } elseif ($cat['count'] >= 2 || $cat['count'] >= $maxCount * 0.3) {
                $mediumErrors[] = $cat;
            } else {
                $lowErrors[] = $cat;
            }
        }

        // Create consolidated legend entries
        if (!empty($highErrors)) {
            $highCounts = array_column($highErrors, 'count');
            if (count($highCounts) > 1) {
                $minHigh = min($highCounts);
                $maxHigh = max($highCounts);
                $label = $minHigh == $maxHigh ? "High ({$minHigh} errors)" : "High ({$minHigh}-{$maxHigh} errors)";
            } else {
                $label = "High ({$highCounts[0]} errors)";
            }
            $consolidated[] = ['color' => $highErrors[0]['color'], 'label' => $label];
        }

        if (!empty($mediumErrors)) {
            $mediumCounts = array_column($mediumErrors, 'count');
            if (count($mediumCounts) > 1) {
                $minMed = min($mediumCounts);
                $maxMed = max($mediumCounts);
                $label = $minMed == $maxMed ? "Medium ({$minMed} errors)" : "Medium ({$minMed}-{$maxMed} errors)";
            } else {
                $label = "Medium ({$mediumCounts[0]} errors)";
            }
            $consolidated[] = ['color' => $mediumErrors[0]['color'], 'label' => $label];
        }

        if (!empty($lowErrors)) {
            $lowCounts = array_column($lowErrors, 'count');
            $totalLowTypes = array_sum(array_column($lowErrors, 'types_count'));
            if (min($lowCounts) == 1 && max($lowCounts) == 1) {
                $label = $totalLowTypes > 1 ? "Low (1 error each, {$totalLowTypes} types)" : "Low (1 error)";
            } else {
                $minLow = min($lowCounts);
                $maxLow = max($lowCounts);
                $label = $minLow == $maxLow ? "Low ({$minLow} errors)" : "Low ({$minLow}-{$maxLow} errors)";
            }
            $consolidated[] = ['color' => $lowErrors[0]['color'], 'label' => $label];
        }

        return $consolidated;
    }

    /**
     * @param HeatmapOptions $options
     */
    public function generateEmptyHeatmap(array $options): string
    {
        $svg = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $svg .= '<svg width="' . $options['min_width'] . '" height="200" xmlns="http://www.w3.org/2000/svg">' . "\n";
        $svg .= '<rect width="100%" height="100%" fill="#f5f5f5"/>' . "\n";
        $svg .= '<text x="50%" y="50%" text-anchor="middle" font-family="Arial" font-size="18" fill="#666">No Error Data Available</text>' . "\n";
        $svg .= '</svg>' . "\n";

        return $svg;
    }

    private function truncateText(string $text, int $maxLength): string
    {
        return strlen($text) > $maxLength ? substr($text, 0, $maxLength - 3) . '...' : $text;
    }
}
