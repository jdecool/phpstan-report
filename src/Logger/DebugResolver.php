<?php

namespace JDecool\PHPStanReport\Logger;

use Symfony\Component\Console\Input\ArgvInput;

class DebugResolver
{
    public readonly bool $value;

    public function __construct(bool $debug)
    {
        $argv = new ArgvInput();

        $this->value = $debug || $argv->hasParameterOption(['--debug']);
    }
}
