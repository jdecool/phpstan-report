<?php

namespace JDecool\PHPStanReport\Bridge\PHPStan\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class AnalyseCommandDefinition
{
    /**
     * @return array<InputArgument|InputOption>
     */
    public function getInputDefinition(): array
    {
        return [
            new InputArgument('paths', InputArgument::OPTIONAL | InputArgument::IS_ARRAY, 'Paths with source code to run analysis on'),
            new InputOption('configuration', 'c', InputOption::VALUE_REQUIRED, 'Path to project configuration file'),
            new InputOption('level', 'l', InputOption::VALUE_REQUIRED, 'Level of rule options - the higher the stricter'),
            new InputOption('no-progress', null, InputOption::VALUE_NONE, 'Do not show progress bar, only results'),
            new InputOption('debug', null, InputOption::VALUE_NONE, 'Show debug information - which file is analysed, do not catch internal errors'),
            new InputOption('autoload-file', 'a', InputOption::VALUE_REQUIRED, 'Project\'s additional autoload file path'),
            new InputOption('error-format', null, InputOption::VALUE_REQUIRED, 'Format in which to print the result of the analysis', null),
            new InputOption('generate-baseline', 'b', InputOption::VALUE_OPTIONAL, 'Path to a file where the baseline should be saved', false),
            new InputOption('allow-empty-baseline', null, InputOption::VALUE_NONE, 'Do not error out when the generated baseline is empty'),
            new InputOption('memory-limit', null, InputOption::VALUE_REQUIRED, 'Memory limit for analysis'),
            new InputOption('xdebug', null, InputOption::VALUE_NONE, 'Allow running with Xdebug for debugging purposes'),
            new InputOption('fix', null, InputOption::VALUE_NONE, 'Launch PHPStan Pro'),
            new InputOption('watch', null, InputOption::VALUE_NONE, 'Launch PHPStan Pro'),
            new InputOption('pro', null, InputOption::VALUE_NONE, 'Launch PHPStan Pro'),
            new InputOption('fail-without-result-cache', null, InputOption::VALUE_NONE, 'Return non-zero exit code when result cache is not used'),
        ];
    }
}
