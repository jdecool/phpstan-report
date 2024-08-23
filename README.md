PHPStan Report
==============

[![Build Status](https://github.com/jdecool/phpstan-report/actions/workflows/ci.yaml/badge.svg)](https://actions-badge.atrox.dev/jdecool/phpstan-report/goto?ref=main)
[![License](https://poser.pugx.org/jdecool/phpstan-report/license)](https://packagist.org/packages/jdecool/phpstan-report)
[![Latest Stable Version](https://poser.pugx.org/jdecool/phpstan-report/v/stable)](https://packagist.org/packages/jdecool/phpstan-report)
[![Latest Unstable Version](https://poser.pugx.org/jdecool/phpstan-report/v/unstable)](https://packagist.org/packages/jdecool/phpstan-report)

A simple wrapper around PHPStan to extract some useful information from the report.

## Installation

```bash
composer require --dev jdecool/phpstan-report
```

## Usage

Run the `phpstan-report analyze` command like you would run the `phpstan analyze` command.

You can change the output format using the `--format` option. The available formats are `text` and `json`.
