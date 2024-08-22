<?php

namespace JDecool\PHPStanReport\Logger;

use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class LoggerFactory
{
    public function __construct(
        private readonly DebugResolver $debugResolver,
    ) {
    }

    public function create(): LoggerInterface
    {
        if ($this->debugResolver->value === false) {
            return new NullLogger();
        }

        $logger = new Logger('phpstan-report');
        $logger->pushHandler(new StreamHandler('php://stdout', Level::Debug));

        return $logger;
    }
}
