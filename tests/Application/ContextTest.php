<?php

namespace JDecool\PHPStanReport\Tests\Application;

use Faker;
use JDecool\PHPStanReport\Application\Context;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArgvInput;

class ContextTest extends TestCase
{
    private Faker\Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Faker\Factory::create();
    }

    #[Test]
    #[DataProvider('provideDebugMode')]
    public function debugMode(array $args, bool $debugMode, bool $expected): void
    {
        $context = new Context(new ArgvInput($args), $debugMode);

        static::assertSame($expected, $context->isDebug());
    }

    #[Test]
    public function phpstanBinaryResolvedFromArgs(): void
    {
        $args = [
            'bin/phpstan-report',
            '--phpstan-bin=' . __FILE__,
        ];
        $context = new Context(new ArgvInput($args), $this->faker->boolean());

        static::assertSame(__FILE__, $context->getPhpstanBinary());
    }

    public static function provideDebugMode(): iterable
    {
        yield 'not enabled if flag is false' => [
            'args' => [],
            'debugMode' => false,
            'expected' => false,
        ];

        yield 'enabled if flag is true' => [
            'args' => [],
            'debugMode' => true,
            'expected' => true,
        ];

        yield 'enabled if debug option is given' => [
            'args' => ['bin/phpstan-report', '--debug'],
            'debugMode' => false,
            'expected' => true,
        ];
    }
}
