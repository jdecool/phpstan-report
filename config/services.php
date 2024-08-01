<?php

declare(strict_types=1);

use JDecool\PHPStanReport\Application;
use JDecool\PHPStanReport\Command\ExportCommand;
use JDecool\PHPStanReport\Command\ReportCommand;
use JDecool\PHPStanReport\Exporter\GitlabReportExporter;
use JDecool\PHPStanReport\Generator\JsonReportGenerator;
use JDecool\PHPStanReport\Generator\TextReportGenerator;
use JDecool\PHPStanReport\Runner\PHPStanRunner;
use JDecool\PHPStanReport\Runner\PHPStanRunnerFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_locator;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services();
    $services->defaults()->autowire()->autoconfigure();

    $services->instanceof(Command::class)->tag('app.command');
    $services->load('JDecool\\PHPStanReport\\', __DIR__.'/../src/*');

    $services->set(GitlabReportExporter::class)->tag('app.report_exporter');
    $services->set(ExportCommand::class)->arg('$exporter', tagged_locator('app.report_exporter', defaultIndexMethod: 'format'));

    $services->set(JsonReportGenerator::class)->tag('app.report_generator');
    $services->set(TextReportGenerator::class)->tag('app.report_generator');
    $services->set(ReportCommand::class)->arg('$generator', tagged_locator('app.report_generator', defaultIndexMethod: 'format'));

    $services->set(PHPStanRunnerFactory::class);
    $services->set(PHPStanRunner::class)->factory([service(PHPStanRunnerFactory::class), 'create']);

    $services->set(Application::class)->public()->args(['$commands' => tagged_iterator('app.command')]);
};
