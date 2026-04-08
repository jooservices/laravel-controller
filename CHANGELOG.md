# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.2.0] - 2026-04-08

### Added

- Custom response formatter contract and configuration support for alternate JSON envelopes
- Structured documentation tree under `docs/00-architecture` through `docs/04-development`
- Repository AI skill pack, GitHub Actions workflows, and local `captainhook` hook automation
- Clover coverage output and Codecov-ready coverage artifacts

### Changed

- README navigation, badges, and workflow summary to match the current repository toolchain
- Composer command map to align with the broader JOOservices package conventions
- Legacy flat docs now act as compatibility pointers into the structured documentation tree

### Fixed

- Portable repository-policy links in agent instruction files
- Pre-push `gitleaks` behavior so actual secret scan failures block pushes
- Labeler patterns and semantic PR title workflow defaults for current repository usage

## [1.1.0] - 2026-03-09

### Added

- Response reference documentation (`docs/response-reference.md`)
- Publishable language file for localized messages (`resources/lang/en/messages.php`)

### Changed

- Status health check uses `CacheRepository` contract instead of `Cache` facade for better testability
- Improved PHPStan and PHPCS compliance (type hints, line length, variable naming)
- Documentation updates in README, user guide, and examples

### Fixed

- PHPStan type safety for config access and translation return type
- PHPMD short variable and static access warnings

## [1.0.0] - Initial release

- Standardized API response envelope and helpers
- Pagination (length-aware, cursor, offset) with optional HAL-style links
- Exception handling and validation message configuration
- Status/health endpoint with optional checks (database, cache, queue)
- Trace ID support, rate limit helper, configurable success codes and item links
