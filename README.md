# PHPStan Report

[![Build Status](https://github.com/jdecool/phpstan-report/actions/workflows/ci.yaml/badge.svg)](https://actions-badge.atrox.dev/jdecool/phpstan-report/goto?ref=main)
[![License](https://poser.pugx.org/jdecool/phpstan-report/license)](https://packagist.org/packages/jdecool/phpstan-report)
[![Latest Stable Version](https://poser.pugx.org/jdecool/phpstan-report/v/stable)](https://packagist.org/packages/jdecool/phpstan-report)
[![Latest Unstable Version](https://poser.pugx.org/jdecool/phpstan-report/v/unstable)](https://packagist.org/packages/jdecool/phpstan-report)

A simple wrapper around PHPStan to extends PHPStan's functionality by providing a customizable report generation feature.
It allows you to run PHPStan analysis and generate reports on ignored errors in various formats.

## Installation

You can install the package via composer:

```bash
composer require --dev jdecool/phpstan-report
```

## Usage

This package provides two main commands:

### Analyze Command

The `analyze` command runs PHPStan analysis and generates reports on ignored errors:

```bash
php vendor/bin/phpstan-report analyze
```

#### Options

- `--report-output-format`: Specify the output format for the report
- `--report-without-analyze`: Generate a report without running the PHPStan analysis
- `--report-continue-on-error`: Continue report generation even if the analysis fails
- `--report-maximum-allowed-errors`: Set the maximum number of allowed errors
- `--report-sort-by`: Sort the report results (options: identifier, occurrence)
- `--report-exclude-identifier`: Identifier to exclude from the report (accepts multiple values)
- `--report-file-<format>`: Export report in an output file for a particular format
- `--report-http-target-url`: The target URL to send the report to (available only if output format is `http`)
- `--report-http-add-header`: Add a header to the HTTP request (available only if output format is `http`)
- `--report-gitlab-severity-mapping`: JSON string mapping error identifiers to GitLab severity levels (available only if output format is `gitlab`)

Available formats are: `text`, `html`, `http`, `json`, `gitlab` and `heatmap`.

For a full list of options, run:

```bash
php vendor/bin/phpstan-report analyze --help
```

### View Command

The `view` command displays detailed information about specific ignored errors from the PHPStan result cache:

```bash
php vendor/bin/phpstan-report view <identifier> [<identifier>...]
```

This command allows you to examine specific error identifiers without running a new analysis.

#### Options

The `view` command takes error identifiers as arguments and displays detailed information about those errors in a table format showing:
- Error identifier
- Error message
- File path
- Line number

For help with the view command, run:

```bash
php vendor/bin/phpstan-report view --help
```

## Examples

Run analysis and generate a text report:

```bash
php vendor/bin/phpstan-report analyze src tests
```

Generate an HTML report without running analysis:

```bash
php vendor/bin/phpstan-report analyze --report-without-analyze --report-output-format=html
```

Run analysis, continue on error, and save report to a file:

```bash
php vendor/bin/phpstan-report analyze --report-continue-on-error --report-file-json=report.json src
```

Generate a heatmap report of files with most ignored errors:

```bash
php vendor/bin/phpstan-report analyze --report-file-heatmap=heatmap.svg src
```

Generate a GitLab report with custom severity mapping:

```bash
php vendor/bin/phpstan-report analyze --report-output-format=gitlab \
  --report-gitlab-severity-mapping='{"missingType.property":"info","argument.type":"critical"}' src
```

For more details on GitLab severity mapping, see [docs/gitlab-severity-mapping.md](docs/gitlab-severity-mapping.md).

### View Command Examples

View details about a specific error identifier:

```bash
php vendor/bin/phpstan-report view "missingType.iterableValue"
```

View details about multiple error identifiers:

```bash
php vendor/bin/phpstan-report view "missingType.iterableValue" "nullCoalesce.expr"
```
