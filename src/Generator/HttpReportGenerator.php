<?php

declare(strict_types=1);

namespace JDecool\PHPStanReport\Generator;

use InvalidArgumentException;
use JDecool\PHPStanReport\Generator\Http\HttpCurlHandler;
use JDecool\PHPStanReport\Runner\ResultCache;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Throwable;

final class HttpReportGenerator implements ReportGenerator
{
    public function __construct(
        private readonly HttpCurlHandler $http,
    ) {}

    public function addCommandOptions(Command $command): void
    {
        $command->addOption('report-http-target-url', null, InputOption::VALUE_REQUIRED, 'The target URL to send the report to');
        $command->addOption('report-http-add-header', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Add a header to the HTTP request');
    }

    public function canBeDumpedInFile(): bool
    {
        return false;
    }

    public function generate(InputInterface $input, ResultCache $result, SortField $sortBy = SortField::None): string
    {
        $targetUrl = $input->getOption('report-http-target-url');

        if (!$targetUrl) {
            throw new InvalidArgumentException('Target URL must be provided with --report-http-target-url option');
        }

        if (filter_var($targetUrl, FILTER_VALIDATE_URL) === false) {
            throw new InvalidArgumentException('Invalid target URL provided');
        }

        $headers = $input->getOption('report-http-add-header') ?? [];

        try {
            $result = $this->http->send($targetUrl, $result->toArray(), $headers);
        } catch (Throwable $e) {
            throw new ReportGenerationException("Failed to send report to $targetUrl: {$e->getMessage()}", previous: $e);
        }

        return "Report sent to $targetUrl (HTTP status: {$result['http_code']})";
    }

    public static function format(): string
    {
        return 'http';
    }
}
