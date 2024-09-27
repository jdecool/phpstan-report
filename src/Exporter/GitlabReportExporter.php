<?php

namespace JDecool\PHPStanReport\Exporter;

use JDecool\PHPStanReport\Runner\PHPStanResultCache;
use PHPStan\Analyser\Error;
use Symfony\Component\Console\Output\OutputInterface;

final class GitlabReportExporter implements ReportExporter
{
    public function export(PHPStanResultCache $result): string
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
