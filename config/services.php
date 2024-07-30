<?php

declare(strict_types=1);

use JDecool\PHPStanReport\Application;
use JDecool\PHPStanReport\Runner\PHPStanRunner;
use JDecool\PHPStanReport\Runner\PHPStanRunnerFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services();
    $services->defaults()->autowire()->autoconfigure();

    $services->instanceof(Command::class)->tag('app.command');
    $services->load('JDecool\\PHPStanReport\\', __DIR__.'/../src/*');

    $services->set(PHPStanRunnerFactory::class);
    $services->set(PHPStanRunner::class)->factory([service(PHPStanRunnerFactory::class), 'create']);

    $services->set(Application::class)->public()->args(['$commands' => tagged_iterator('app.command')]);
};
