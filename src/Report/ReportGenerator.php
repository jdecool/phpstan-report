<?php

namespace JDecool\PHPStanReport\Report;

use JDecool\PHPStanReport\Runner\PHPStanResultCache;
use Symfony\Component\Console\Output\OutputInterface;

final class ReportGenerator
{
    public function __invoke(OutputInterface $output, PHPStanResultCache $result)
    {
        echo "Processing results...\n\n";

        echo "* Total error(s): {$result->countTotalErrors()}\n";
        echo "  * Error(s): {$result->countErrors()}\n";
        echo "  * Locally ignored error(s): {$result->countLocallyIgnoredErrors()}\n";
        echo "  * Line(s) to ignore: {$result->countLinesToIgnore()}\n";
    }
}
