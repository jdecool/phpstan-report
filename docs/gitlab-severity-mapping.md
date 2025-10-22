# GitLab Severity Mapping

When using the `gitlab` output format, you can customize the severity level assigned to specific PHPStan error identifiers using the `--report-gitlab-severity-mapping` option.

## Valid GitLab Severity Levels

The following severity levels are supported (from lowest to highest):

- `info`
- `minor`
- `major` (default for ignorable errors)
- `critical`
- `blocker` (default for non-ignorable errors)

## Default Behavior

Without custom mapping, the GitLab report generator uses the following default logic:

- **Non-ignorable errors**: `blocker`
- **Ignorable errors**: `major`

## Usage

The `--report-gitlab-severity-mapping` option accepts a JSON string that maps PHPStan error identifiers to GitLab severity levels.

### Basic Example

```bash
php vendor/bin/phpstan-report analyze --report-output-format=gitlab \
  --report-gitlab-severity-mapping='{"missingType.property":"info","argument.type":"critical"}' src
```

### Mark Missing Type Errors as Informational

```bash
php vendor/bin/phpstan-report analyze --report-output-format=gitlab \
  --report-gitlab-severity-mapping='{"missingType.property":"info","missingType.iterableValue":"info"}' src
```

### Elevate Type Errors to Critical

```bash
php vendor/bin/phpstan-report analyze --report-output-format=gitlab \
  --report-gitlab-severity-mapping='{"argument.type":"critical","return.type":"blocker"}' src
```

### Complex Mapping

```bash
php vendor/bin/phpstan-report analyze --report-output-format=gitlab \
  --report-gitlab-severity-mapping='{
    "missingType.property": "info",
    "missingType.iterableValue": "minor",
    "argument.type": "critical",
    "return.type": "blocker"
  }' src
```
