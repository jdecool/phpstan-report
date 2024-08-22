<?php

namespace JDecool\PHPStanReport\Exporter;

use JDecool\PHPStanReport\Runner\PHPStanResultCache;
use PHPStan\Analyser\Error;
use Symfony\Component\Console\Output\OutputInterface;

final class GitlabReportExporter implements ReportExporter
{
    public function export(OutputInterface $output, PHPStanResultCache $result): void
    {
        $errors = [];

        foreach ($this->errorIterator($result->getErrors()) as $error) {
            $errors[] = $this->transform($error);
        }

        foreach ($this->errorIterator($result->getLocallyIgnoredErrors()) as $error) {
            $errors[] = $this->transform($error);
        }

        $output->writeln(json_encode($errors, flags: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    public static function format(): string
    {
        return 'gitlab';
    }

    private function errorIterator(array $errors): \Generator
    {
        foreach ($errors as $phpstanErrors) {
            foreach ($phpstanErrors as $error) {
                yield $error;
            }
        }
    }

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
