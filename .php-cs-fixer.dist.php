<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude('tests/data')
    ->ignoreVCSIgnored(true)
;

$config = new PhpCsFixer\Config();
$config
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PER-CS2.0' => true,
        '@PER-CS2.0:risky' => true,
    ])
    ->setFinder($finder)
;

return $config;
