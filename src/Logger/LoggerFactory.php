<?php

namespace JDecool\PHPStanReport\Logger;

use JDecool\PHPStanReport\Application;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class LoggerFactory
{
    public function __construct(
        private readonly Application\Context $context,
    ) {}

    public function create(): LoggerInterface
    {
        if ($this->context->isDebug() === false) {
            return new NullLogger();
        }

        $logger = new Logger('phpstan-report');
        $logger->pushHandler(new StreamHandler('php://stdout', Level::Debug));

        return $logger;
    }
}
