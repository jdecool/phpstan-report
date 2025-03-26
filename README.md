PHPStan Report
==============

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

The main command provided by this package is `analyze`. Here's the basic usage:

```bash
$ php vendor/bin/phpstan-report analyze
```

## Options

* `--report-output-format`: Specify the output format for the report
* `--report-without-analyze`: Generate a report without running the PHPStan analysis
* `--report-continue-on-error`: Continue report generation even if the analysis fails
* `--report-maximum-allowed-errors`: Set the maximum number of allowed errors
* `--report-sort-by`: Sort the report results (options: identifier, occurrence)
* `--report-exclude-identifier`: Identifier to exclude from the report (accepts multiple values)
* `--report-file-<format>`: Export report in an output file for a particular format
* `--report-http-target-url`: The target URL to send the report to (available only if output format is `http`)
* `--report-http-add-header`: Add a header to the HTTP request (available only if output format is `http`)

Available formats are: `text`, `html`, `http`, `json` and `gitlab`.

For a full list of options, run:

```bash
$ php vendor/bin/phpstan-report analyze --help
```

## Examples

Run analysis and generate a text report:

```bash
$ php vendor/bin/phpstan-report analyze src tests
```

Generate an HTML report without running analysis:

```bash
$ php vendor/bin/phpstan-report analyze --report-without-analyze --report-output-format=html
```

Run analysis, continue on error, and save report to a file:

```bash
$ php vendor/bin/phpstan-report analyze --report-continue-on-error --report-file-json=report.json src
```
