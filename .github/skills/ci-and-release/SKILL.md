# CI And Release

Automation expectations for this repository:

- CI should run Composer audit, linting, and tests with coverage
- release automation should be tag-driven
- optional third-party integrations must be guarded by secret availability checks
- documentation and README should not promise automation that does not exist in `.github/workflows/`
- `develop` is the active integration branch
- `master` is the stable/release branch
- release branches should be created from latest `develop` and target `master`
- Pint, PHPCS, PHPStan, PHPMD, PHP-CS-Fixer, PHPUnit, and coverage should stay aligned with Composer scripts
