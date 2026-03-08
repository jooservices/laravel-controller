# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.0] - 2025-03-09

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
