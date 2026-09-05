# jooservices/laravel-controller

This file adds project-only rules.

- PHP `>= 8.5`, Laravel package: `illuminate/*` `^12|^13`, Orchestra Testbench `^10|^11`
- Current public line: **v4.0.1** (major **v4** — see `UPGRADE-4.0.md`, `CHANGELOG.md`)
- All PHP tooling via Docker (`php:8.5-cli-bookworm`)
- CI on GitHub-hosted `ubuntu-latest` runners; test matrix covers Laravel 12 and 13
- Controllers stay thin; presentation via Laravel Resources; response envelope wraps Resource output
- v4 contracts: Problem Details profile, OpenAPI `resources/openapi/envelope.v4.yaml`, `meta.pagination`, `StatusHealthCheck`
- `respondWithPagination()` accepts LengthAware / simple / Cursor paginators only (rejects raw payloads)
- Lints at **max** with **no ignore**: PHPStan max, full PSR-12 PHPCS, full PHPMD rulesets, Pint `per`
