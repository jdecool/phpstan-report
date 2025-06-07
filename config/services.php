<?php

declare(strict_types=1);

use JDecool\PHPStanReport\Application;
use JDecool\PHPStanReport\Command\AnalyzeCommand;
use JDecool\PHPStanReport\Generator\GitlabReportGenerator;
use JDecool\PHPStanReport\Generator\HeatmapReportGenerator;
use JDecool\PHPStanReport\Generator\HtmlReportGenerator;
use JDecool\PHPStanReport\Generator\HttpReportGenerator;
use JDecool\PHPStanReport\Generator\JsonReportGenerator;
use JDecool\PHPStanReport\Generator\NumberFormatterFactory;
use JDecool\PHPStanReport\Generator\SvgReportGenerator;
use JDecool\PHPStanReport\Generator\TextReportGenerator;
use JDecool\PHPStanReport\Logger\LoggerFactory;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Filesystem\Filesystem;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_locator;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services();
    $services->defaults()->autowire()->autoconfigure();

    $services->instanceof(Command::class)->tag('app.command');
    $services->load('JDecool\\PHPStanReport\\', __DIR__ . '/../src/*')->exclude(__DIR__ . '/../src/bootstrap.php');

    $services->set(Filesystem::class);

    $services->set(Logger::class)->factory([service(LoggerFactory::class), 'create']);
    $services->alias(LoggerInterface::class, Logger::class);

    $services->set(NumberFormatter::class, NumberFormatter::class)->factory([service(NumberFormatterFactory::class), 'create']);

    $services->set(GitlabReportGenerator::class)->tag('app.report_generator');
    $services->set(HeatmapReportGenerator::class)->tag('app.report_generator');
    $services->set(HtmlReportGenerator::class)->tag('app.report_generator');
    $services->set(HttpReportGenerator::class)->tag('app.report_generator');
    $services->set(JsonReportGenerator::class)->tag('app.report_generator');
    $services->set(SvgReportGenerator::class)->tag('app.report_generator');
    $services->set(TextReportGenerator::class)->tag('app.report_generator');
    $services->set(AnalyzeCommand::class)->arg('$generator', tagged_locator('app.report_generator', defaultIndexMethod: 'format'));

    $services->set(Application::class)->public()->args(['$commands' => tagged_iterator('app.command')]);
    $services->set(Application\Context::class)->arg('$debug', '%app.debug%');

    $services->set(ArgvInput::class);
};
