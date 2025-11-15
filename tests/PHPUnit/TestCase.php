<?php

declare(strict_types=1);

namespace JDecool\PHPStanReport\Tests\PHPUnit;

use PHPUnit\Framework\TestCase as BaseTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\VarDumper\Test\VarDumperTestTrait;

abstract class TestCase extends BaseTestCase
{
    use VarDumperTestTrait;

    protected function assertDumpFileEquals(mixed $value, string $expectedFile, string $message = ''): void
    {
        $this->updateExpectations($expectedFile, $value);

        $expected = file_get_contents($expectedFile) ?: $this->fail("Unable to open $expectedFile");

        $this->assertDumpEquals($expected, $value, message: $message);
    }

    protected function updateExpectations(string $file, mixed $expected): void
    {
        if (!UPDATE_EXPECTATIONS) {
            return;
        }

        $fs = new Filesystem();
        $fs->mkdir(dirname($file));

        file_put_contents($file, $this->getDump($expected) . "\n");
    }
}
