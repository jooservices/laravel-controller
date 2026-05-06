# CI And Release

Automation expectations for this repository:

- CI should run Composer audit, linting, and tests with coverage
- release automation should be tag-driven
- optional third-party integrations must be guarded by secret availability checks
- documentation and README should not promise automation that does not exist in `.github/workflows/`
- `develop` is the active integration branch in the current remote state
- CI should not list `master` or `main` unless those branches exist again
- Pint, PHPCS, PHPStan, PHPMD, PHP-CS-Fixer, PHPUnit, and coverage should stay aligned with Composer scripts
