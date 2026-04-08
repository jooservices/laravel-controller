# CI/CD

## CI

The GitHub Actions CI workflow runs:

- `composer audit`
- a lint matrix for Pint, PHPCS, PHPStan, and PHPMD
- PHPUnit with coverage generation
- optional Codecov upload when `CODECOV_TOKEN` is configured

Additional repository workflows:

- `PR Labeler` applies labels from `.github/labeler.yml`
- `Semantic PR Title` enforces conventional PR titles
- `OpenSSF Scorecard` publishes SARIF security posture results on the default branch schedule
- `Secret Scanning` is checked in as a disabled-by-default workflow and can be re-enabled when `GITLEAKS_LICENSE` is configured

## Composer command consistency

This repository now matches the `jooservices/dto` command map for the core package lifecycle:

- `test`
- `test:coverage`
- `lint:pint`
- `lint:pint:fix`
- `lint:phpcs`
- `lint:phpstan`
- `lint:phpmd`
- `lint`
- `lint:all`
- `lint:fix`
- `check`
- `ci`

Intentional differences from `jooservices/dto`:

- no `lint:cs` or `lint:cs:fix`, because this package does not include `php-cs-fixer`

## Git hooks

This repository now uses `captainhook` for local git hook consistency.

Installed hooks enforce:

- conventional commit message format on `commit-msg`
- staged PHP linting, Pint, PHPCS, and PHPStan on `pre-commit`
- test execution on `pre-push`
- staged `gitleaks` scans on `pre-commit`
- commit-range `gitleaks` scans on `pre-push`, with a fallback note when the binary is missing

Composer installs and updates automatically run `captainhook install --force --skip-existing`.

## Release

The release workflow runs on tags matching `v*.*.*`.

It creates a GitHub release and can optionally trigger a Packagist refresh when `PACKAGIST_USERNAME` and `PACKAGIST_TOKEN` are configured as repository secrets.