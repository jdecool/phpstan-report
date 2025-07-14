<?php

declare(strict_types=1);

namespace JDecool\PHPStanReport\Generator;

use JDecool\PHPStanReport\Runner\ResultCache;
use PHPStan\Analyser\Error;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

final class GitlabReportGenerator implements ReportGenerator
{
    /**
     * GitLab severity levels in order of severity (lowest to highest)
     *
     * @var array<string>
     */
    private const GITLAB_SEVERITY_LEVELS = [
        'info',
        'minor',
        'major',
        'critical',
        'blocker',
    ];

    public function addCommandOptions(Command $command): void
    {
        $command->addOption(
            'report-gitlab-severity-mapping',
            null,
            InputOption::VALUE_REQUIRED,
            'JSON string mapping error identifiers to GitLab severity levels (info, minor, major, critical, blocker)',
        );
    }

    public function canBeDumpedInFile(): bool
    {
        return true;
    }

    public function generate(InputInterface $input, ResultCache $result, SortField $sortBy = SortField::None): string
    {
        $severityMapping = $this->getSeverityMapping($input);

        $errors = [];

        foreach ($this->errorIterator($result->getErrors()) as $error) {
            $errors[] = $this->transform($error, $severityMapping);
        }

        foreach ($this->errorIterator($result->getLocallyIgnoredErrors()) as $error) {
            $errors[] = $this->transform($error, $severityMapping);
        }

        return json_encode($errors, flags: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public static function format(): string
    {
        return 'gitlab';
    }

    /**
     * @param array<Error[]> $errors
     * @return \Generator<Error>
     */
    private function errorIterator(array $errors): \Generator
    {
        foreach ($errors as $phpstanErrors) {
            foreach ($phpstanErrors as $error) {
                yield $error;
            }
        }
    }

    /**
     * @return array<string, string>
     */
    private function getSeverityMapping(InputInterface $input): array
    {
        $mappingJson = $input->getOption('report-gitlab-severity-mapping');

        if ($mappingJson === null) {
            return [];
        }

        $mapping = json_decode($mappingJson, true);

        if (!is_array($mapping)) {
            throw new \InvalidArgumentException('Invalid JSON provided for report-gitlab-severity-mapping option');
        }

        foreach ($mapping as $identifier => $severity) {
            if (!in_array($severity, self::GITLAB_SEVERITY_LEVELS, true)) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Invalid severity level "%s" for identifier "%s". Valid levels are: %s',
                        $severity,
                        $identifier,
                        implode(', ', self::GITLAB_SEVERITY_LEVELS),
                    ),
                );
            }
        }

        return $mapping;
    }

    /**
     * @param array<string, string> $severityMapping
     * @return string
     */
    private function determineSeverity(Error $error, array $severityMapping): string
    {
        $identifier = $error->getIdentifier();

        if ($identifier !== null && isset($severityMapping[$identifier])) {
            return $severityMapping[$identifier];
        }

        return $error->canBeIgnored() ? 'major' : 'blocker';
    }

    /**
     * @param array<string, string> $severityMapping
     * @return array{description: string, fingerprint: string, severity: string, location: array{path: string, lines: array{begin: int}}}
     */
    private function transform(Error $error, array $severityMapping): array
    {
        return [
            'description' => $error->getMessage(),
            'fingerprint' => hash(
                'sha256',
                implode([$error->getFile(), $error->getLine(), $error->getMessage()]),
            ),
            'severity' => $this->determineSeverity($error, $severityMapping),
            'location' => [
                'path' => $error->getFilePath(),
                'lines' => [
                    'begin' => $error->getLine(),
                ],
            ],
        ];
    }
}
