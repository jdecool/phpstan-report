<?php

declare(strict_types=1);

namespace JDecool\PHPStanReport\Tests\PHPUnit\Generator;

use JDecool\PHPStanReport\Generator\GitlabReportGenerator;
use JDecool\PHPStanReport\Generator\SortField;
use JDecool\PHPStanReport\Runner\PHPStanResultCache;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Command\Command;

final class GitlabReportGeneratorTest extends TestCase
{
    private GitlabReportGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->generator = new GitlabReportGenerator();
    }

    #[Test]
    #[DataProvider('generateReportProvider')]
    public function generateReport(SortField $sort, string $expected): void
    {
        $input = $this->createMock(InputInterface::class);
        $input->method('getOption')
            ->with('report-gitlab-severity-mapping')
            ->willReturn(null);

        $output = $this->generator->generate(
            $input,
            PHPStanResultCache::fromFile(__DIR__ . '/../../data/cache.php'),
            $sort,
        );

        static::assertJsonStringEqualsJsonString($expected, $output);
    }

    #[Test]
    public function generateReportWithSeverityMapping(): void
    {
        $severityMapping = json_encode([
            'missingType.property' => 'info',
            'argument.type' => 'critical',
        ]);

        $input = $this->createMock(InputInterface::class);
        $input->method('getOption')
            ->with('report-gitlab-severity-mapping')
            ->willReturn($severityMapping);

        $output = $this->generator->generate(
            $input,
            PHPStanResultCache::fromFile(__DIR__ . '/../../data/cache.php'),
            SortField::None,
        );

        $decodedOutput = json_decode($output, true);
        static::assertIsArray($decodedOutput);
        static::assertNotEmpty($decodedOutput);

        foreach ($decodedOutput as $error) {
            static::assertArrayHasKey('severity', $error);
            static::assertContains($error['severity'], ['info', 'minor', 'major', 'critical', 'blocker']);
        }
    }

    #[Test]
    public function generateReportWithInvalidJsonSeverityMapping(): void
    {
        $input = $this->createMock(InputInterface::class);
        $input->method('getOption')
            ->with('report-gitlab-severity-mapping')
            ->willReturn('invalid-json');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid JSON provided for report-gitlab-severity-mapping option');

        $this->generator->generate(
            $input,
            PHPStanResultCache::fromFile(__DIR__ . '/../../data/cache.php'),
            SortField::None,
        );
    }

    #[Test]
    public function generateReportWithInvalidSeverityLevel(): void
    {
        $severityMapping = json_encode([
            'missingType.property' => 'invalid-severity',
        ]);

        $input = $this->createMock(InputInterface::class);
        $input->method('getOption')
            ->with('report-gitlab-severity-mapping')
            ->willReturn($severityMapping);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid severity level "invalid-severity" for identifier "missingType.property"');

        $this->generator->generate(
            $input,
            PHPStanResultCache::fromFile(__DIR__ . '/../../data/cache.php'),
            SortField::None,
        );
    }

    #[Test]
    public function addCommandOptionsAddsCorrectOption(): void
    {
        $command = $this->createMock(Command::class);
        $command->expects(static::once())
            ->method('addOption')
            ->with(
                'report-gitlab-severity-mapping',
                null,
                \Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED,
                'JSON string mapping error identifiers to GitLab severity levels (info, minor, major, critical, blocker)',
            );

        $this->generator->addCommandOptions($command);
    }

    #[Test]
    public function canBeDumpedInFileReturnsTrue(): void
    {
        static::assertTrue($this->generator->canBeDumpedInFile());
    }

    #[Test]
    public function formatReturnsGitlab(): void
    {
        static::assertSame('gitlab', GitlabReportGenerator::format());
    }

    public static function generateReportProvider(): iterable
    {
        yield [
            SortField::Identifier,
            file_get_contents(__DIR__ . '/../../data/expected/generator/gitlab-generator-identifier-ordered.json'),
        ];

        yield [
            SortField::Occurrence,
            file_get_contents(__DIR__ . '/../../data/expected/generator/gitlab-generator-counter-ordered.json'),
        ];
    }
}
