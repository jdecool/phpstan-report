<?php

declare(strict_types=1);

namespace JDecool\PHPStanReport\Generator;

use JDecool\PHPStanReport\Runner\ResultCache;
use PHPStan\Analyser\Error;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

final class GitlabReportGenerator implements ReportGenerator
{
    public function addCommandOptions(Command $command): void {}

    public function canBeDumpedInFile(): bool
    {
        return true;
    }

    public function generate(InputInterface $input, ResultCache $result, SortField $sortBy = SortField::None): string
    {
        $errors = [];

        foreach ($this->errorIterator($result->getErrors()) as $error) {
            $errors[] = $this->transform($error);
        }

        foreach ($this->errorIterator($result->getLocallyIgnoredErrors()) as $error) {
            $errors[] = $this->transform($error);
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
     * @param Error $error
     * @return array{description: string, fingerprint: string, severity: string, location: array{path: string, lines: array{begin: int}}}
     */
    private function transform(Error $error): array
    {
        return [
            'description' => $error->getMessage(),
            'fingerprint' => hash(
                'sha256',
                implode([$error->getFile(), $error->getLine(), $error->getMessage()]),
            ),
            'severity' => $error->canBeIgnored() ? 'major' : 'blocker',
            'location' => [
                'path' => $error->getFilePath(),
                'lines' => [
                    'begin' => $error->getLine(),
                ],
            ],
        ];
    }
}
