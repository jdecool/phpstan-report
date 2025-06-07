<?php

declare(strict_types=1);

namespace JDecool\PHPStanReport\Generator;

use JDecool\PHPStanReport\Runner\ResultCache; // Changed Model to Runner
use JDecool\PHPStanReport\Generator\SortField; // Changed Model to Generator
use NumberFormatter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

final class SvgReportGenerator implements ReportGenerator
{
    public function __construct(
        private readonly NumberFormatter $formatter,
    ) {
    }

    public function addCommandOptions(Command $command): void
    {
    }

    public function canBeDumpedInFile(): bool
    {
        return true;
    }

    public function generate(InputInterface $input, ResultCache $result, SortField $sortBy = SortField::Identifier): string
    {
        $errorsMap = $result->getErrorsMap($sortBy); // This should be fine if ResultCache from Runner has this method

        $width = 800;
        $height = 600;
        $padding = 50;
        $barSpacing = 10;
        $labelHeight = 20; // Approximate height for text labels

        if (empty($errorsMap)) {
            return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="$width" height="$height" viewBox="0 0 $width $height">
    <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" font-family="sans-serif" font-size="20">No errors found.</text>
</svg>
SVG;
        }

        $numBars = count($errorsMap);
        $maxErrors = 0;
        foreach ($errorsMap as $count) {
            if ($count > $maxErrors) {
                $maxErrors = $count;
            }
        }

        if ($maxErrors === 0) { // Handle case where there are error types but all counts are 0
            return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="$width" height="$height" viewBox="0 0 $width $height">
    <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" font-family="sans-serif" font-size="20">All error counts are zero.</text>
</svg>
SVG;
        }

        $availableWidthForBars = $width - (2 * $padding);
        $barWidth = ($availableWidthForBars - (($numBars - 1) * $barSpacing)) / $numBars;
        if ($barWidth <= 0) { // Fallback if too many bars for the width
            $barWidth = 10; // Minimum bar width
            $availableWidthForBars = $numBars * $barWidth + ($numBars - 1) * $barSpacing;
            $width = $availableWidthForBars + 2 * $padding; // Adjust overall width
        }

        $graphHeight = $height - (2 * $padding) - $labelHeight; // Height available for bars themselves

        $svgElements = [];
        $currentX = $padding;

        foreach ($errorsMap as $identifier => $count) {
            $barHeight = ($count / $maxErrors) * $graphHeight;
            if ($barHeight < 0) $barHeight = 0; // Ensure non-negative height

            $rectY = $padding + $graphHeight - $barHeight;

            $svgElements[] = sprintf(
                '<rect x="%.2F" y="%.2F" width="%.2F" height="%.2F" fill="#4CAF50" />',
                $currentX,
                $rectY,
                $barWidth,
                $barHeight
            );

            // Add text label below the bar
            $textY = $padding + $graphHeight + $labelHeight / 1.5; // Position text below the bar baseline
            $svgElements[] = sprintf(
                '<text x="%.2F" y="%.2F" font-family="sans-serif" font-size="12" text-anchor="middle" dominant-baseline="middle">%s (%d)</text>',
                $currentX + ($barWidth / 2),
                $textY,
                htmlspecialchars((string)$identifier, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
                $count
            );

            $currentX += $barWidth + $barSpacing;
        }

        // Add Y-axis labels (simple version: 0 and maxErrors)
        $svgElements[] = sprintf(
            '<text x="%d" y="%d" font-family="sans-serif" font-size="12" text-anchor="end" dominant-baseline="middle">0</text>',
            $padding - 5,
            $padding + $graphHeight
        );
        $svgElements[] = sprintf(
            '<text x="%d" y="%d" font-family="sans-serif" font-size="12" text-anchor="end" dominant-baseline="middle">%d</text>',
            $padding - 5,
            $padding,
            $maxErrors // Added missing argument for the count
        );

        // Add X-axis line
        $svgElements[] = sprintf(
            '<line x1="%d" y1="%.2F" x2="%.2F" y2="%.2F" stroke="#333" stroke-width="1"/>',
            $padding,
            $padding + $graphHeight,
            $width - $padding,
            $padding + $graphHeight
        );

        // Add Y-axis line
        $svgElements[] = sprintf(
            '<line x1="%d" y1="%d" x2="%d" y2="%.2F" stroke="#333" stroke-width="1"/>',
            $padding,
            $padding,
            $padding,
            $padding + $graphHeight
        );

        $svgContent = implode("\n    ", $svgElements);

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="$width" height="$height" viewBox="0 0 $width $height">
    $svgContent
</svg>
SVG;
    }

    public static function format(): string
    {
        return "svg";
    }
}
