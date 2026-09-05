# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [4.0.0] - 2026-09-05

### Added

- RFC 7807 Problem Details via `response_profile` (`problem+json`) and `respondWithProblem()`.
- `ProblemDetailsFormatter` and OpenAPI 3.1 schemas in `resources/openapi/envelope.v4.yaml` (`EnvelopeContract`).
- Echo of Idempotency-Key, rate-limit, and Retry-After request headers into envelope `meta` (`meta_headers` config; echo only).
- Pluggable status health checks via `StatusHealthCheck` class-strings alongside built-in `database` / `cache` / `queue`.
- Upgrade guide: [UPGRADE-4.0.md](UPGRADE-4.0.md).

### Changed

- Cursor and offset pagination metadata nested under `meta.pagination` (parity with length-aware pagination).
- Iterable `respondWithCursorPagination()` derives `has_more` from a non-null `next_cursor` (removed `$hasMore` argument).
- Invalid or missing API Resource classes throw `UnexpectedValueException` instead of falling back to raw payloads.
- `ResourceCollection` payloads respect `JsonResource::withoutWrapping()`.
- Default `success` envelope flag is true only for HTTP 2xx unless `success_codes` is set.
- Status readiness checks return HTTP 503 when any configured check fails; probes live in `StatusHealthChecker`.
- Repository scaffold aligned with JOOservices package standards (Docker PHP 8.5, Pint `per`, full lint gate).

### Removed

- Deprecated `paginated()` helper — use `respondWithPagination()`.
- `envelope_204` config — `noContent()` always returns an empty 204 body (RFC 9110).

### Fixed

- `AuthorizationException::asNotFound()` preserves HTTP 404 instead of forcing 403.
- `HttpException` response headers (for example `Retry-After`) are preserved.
- `ModelNotFoundException` returns a public `Resource not found` message; details go to the exception reporter.

## [1.4.0] - 2026-06-25

### Added

- Added Laravel 13 support alongside Laravel 12: `illuminate/console`, `illuminate/http`, `illuminate/routing`, and `illuminate/support` now accept `^12.0|^13.0`
- Added `orchestra/testbench:^11.0` to `require-dev` and a CI matrix testing both Laravel 12 and Laravel 13

### Fixed

- Fixed the release workflow sending a Packagist URL instead of the GitHub repository URL to Packagist's `update-package` endpoint, which prevented Packagist from refreshing on tagged releases.

## [1.3.0] - 2026-05-11

### Added

- Configurable `routes.auto_map_host_routes` switch for applications that want to own all API route registration.
- Additional response helper coverage for resource collection metadata, 204 envelopes, and doctor diagnostics.

### Changed

- Resource collection top-level links are preserved under `meta.links` instead of being flattened into `meta`.
- Doctor command status timeout validation now matches runtime behavior by accepting integers and digit strings.
- CI, release checklist, and agent guidance now reflect `master` as the stable/release branch and `develop` as the integration branch.
- Composer lock refreshed within the existing PHP 8.5 and Laravel 12 constraints.

### Fixed

- Release flow documentation no longer assumes the repository has no stable branch.

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
