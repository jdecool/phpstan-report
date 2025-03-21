<?php

declare(strict_types=1);

namespace JDecool\PHPStanReport\Tests\PHPUnit\Logger;

use JDecool\PHPStanReport\Application\Context;
use JDecool\PHPStanReport\Logger\LoggerFactory;
use Monolog\Logger;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Input\ArgvInput;

final class LoggerFactoryTest extends TestCase
{
    #[Test]
    public function nullLoggerCreatedIfNotRunningInDebugMode(): void
    {
        $factory = $this->createFactoryInstance(debug: false);

        $logger = $factory->create();

        static::assertInstanceOf(NullLogger::class, $logger);
    }

    #[Test]
    public function loggerCreatedIfRunningInDebugMode(): void
    {
        $factory = $this->createFactoryInstance(debug: true);

        $logger = $factory->create();

        static::assertInstanceOf(Logger::class, $logger);
    }

    private function createFactoryInstance(ArgvInput $argv = new ArgvInput([]), bool $debug = false): LoggerFactory
    {
        $context = new Context(new ArgvInput([]), debug: $debug);

        return new LoggerFactory($context);
    }

}
