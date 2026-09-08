<?php

declare(strict_types=1);

namespace JDecool\PHPStanReport;

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Command\Command;

final class Application extends BaseApplication
{
    public const NAME = 'PHPStan Report';
    public const VERSION = '@dev';

    /**
     * @param iterable<Command> $commands
     */
    public function __construct(iterable $commands)
    {
        parent::__construct(self::NAME, self::VERSION);

        // Symfony Console 7.4 replaced Application::add() with addCommand() and 8.0 removed the former.
        /** @phpstan-ignore function.alreadyNarrowedType */
        $addCommand = method_exists($this, 'addCommand') ? 'addCommand' : 'add';

        foreach ($commands as $command) {
            /** @phpstan-ignore method.notFound */
            $this->$addCommand($command);
        }

        $this->setDefaultCommand('analyze');
    }
}
