# JOOservices Laravel Controller

[![CI](https://github.com/jooservices/laravel-controller/actions/workflows/ci.yml/badge.svg?branch=develop)](https://github.com/jooservices/laravel-controller/actions/workflows/ci.yml)
[![codecov](https://codecov.io/gh/jooservices/laravel-controller/graph/badge.svg)](https://codecov.io/gh/jooservices/laravel-controller)
[![Quality gate status](https://sonarcloud.io/api/project_badges/measure?project=jooservices_laravel-controller&metric=alert_status)](https://sonarcloud.io/summary/new_code?id=jooservices_laravel-controller)
[![OpenSSF Scorecard](https://api.securityscorecards.dev/projects/github.com/jooservices/laravel-controller/badge)](https://securityscorecards.dev/viewer/?uri=github.com/jooservices/laravel-controller)
[![PHP Version](https://img.shields.io/badge/PHP-8.5%2B-blue.svg)](https://www.php.net/)
[![Release](https://img.shields.io/badge/version-4.0.0-blue.svg)](CHANGELOG.md)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)
[![Packagist Version](https://img.shields.io/packagist/v/jooservices/laravel-controller)](https://packagist.org/packages/jooservices/laravel-controller)

**JOOservices Laravel Controller** is a Laravel API controller foundation for standardized JSON response envelopes, RFC 7807 Problem Details, OpenAPI envelope schemas, pagination metadata, status endpoints, trace IDs, and formatter-based response customization.

Composer package: `jooservices/laravel-controller` — current line: **v4.0.0**. Upgrading from v1.x: see [UPGRADE-4.0.md](UPGRADE-4.0.md).

## Features

- base API controller helpers for success, error, validation, status, and no-content responses
- optional RFC 7807 Problem Details (`application/problem+json`) via profile or `respondWithProblem()`
- stable OpenAPI 3.1 envelope schemas (`resources/openapi/envelope.v4.yaml`)
- Laravel `JsonResource` and `ResourceCollection` friendly response helpers
- standardized response envelope with configurable keys
- length-aware, cursor, and offset pagination helpers (`meta.pagination`)
- echo of Idempotency-Key / rate-limit / Retry-After headers into `meta` (no storage)
- trace ID support through a configurable request header
- optional status endpoint with pluggable `StatusHealthCheck` probes
- custom `ResponseFormatter` contract for teams that need a different top-level JSON shape
- optional exception response helper for common Laravel exceptions
- read-only `php artisan laravel-controller:doctor` diagnostics

## Installation

```bash
composer require jooservices/laravel-controller:^4.0
```

## Publish Config

```bash
php artisan vendor:publish --provider="JOOservices\LaravelController\Providers\LaravelControllerServiceProvider" --tag="config"
```

Optional translations:

```bash
php artisan vendor:publish --provider="JOOservices\LaravelController\Providers\LaravelControllerServiceProvider" --tag="laravel-controller-lang"
```

## Quick Start

Use the package at the controller boundary. Keep request validation, business logic, and persistence in your application layers:

```php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\UserIndexRequest;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use JOOservices\LaravelController\Http\Controllers\BaseApiController;

final class UserController extends BaseApiController
{
    public function index(UserIndexRequest $request, UserService $users): JsonResponse
    {
        return $this->respondWithPagination(
            paginator: $users->paginate($request->validated()),
            resourceClass: UserResource::class,
            message: 'Users retrieved successfully.',
        );
    }
}
```

## Standard Architecture Usage

Recommended flow:

```text
Request -> Controller -> FormRequest -> Service -> Repository -> Model
Model / entity / data object -> Laravel Resource -> API response envelope -> JsonResponse
```

Laravel Resource remains the presentation transformer. JOOservices Laravel Controller wraps the transformed payload in the API response envelope.

## Response Envelope Example

```json
{
  "success": true,
  "code": 200,
  "message": "Users retrieved successfully.",
  "data": [],
  "meta": {},
  "errors": null,
  "trace_id": "550e8400-e29b-41d4-a716-446655440000"
}
```

## Resource Example

```php
public function show(UserShowRequest $request, UserService $users): JsonResponse
{
    return $this->respondWithResource(
        resource: new UserResource($users->findForDisplay($request->validated('id'))),
        message: 'User retrieved successfully.',
    );
}
```

DTOs, `Arrayable`, `JsonSerializable`, and objects with `toArray()` may be accepted as input data, but they do not replace Laravel Resources as the presentation layer.

## Pagination Example

```php
public function index(UserIndexRequest $request, UserService $users): JsonResponse
{
    return $this->respondWithPagination(
        paginator: $users->paginate($request->validated()),
        resourceClass: UserResource::class,
        message: 'Users retrieved successfully.',
    );
}
```

Cursor and offset helpers nest fields under `meta.pagination`. You may pass a Laravel `CursorPaginator` directly to `respondWithCursorPagination()`.

## Error Response Example

```php
public function archive(UserArchiveRequest $request, UserService $users): JsonResponse
{
    if (! $users->canArchive($request->validated('id'))) {
        return $this->respondWithError(
            message: 'User cannot be archived.',
            code: 409,
            errors: ['user' => ['The user has active dependencies.']],
        );
    }

    $users->archive($request->validated('id'));

    return $this->respondNoContent();
}
```

For Problem Details, see [docs/02-user-guide/problem-details.md](docs/02-user-guide/problem-details.md) or set `response_profile` to `problem+json`.

## Status Endpoint

When package routes are enabled, the status endpoint is available under the configured prefix.
Without health checks it is a liveness probe (HTTP 200). With `status.checks` configured it acts as readiness: any failed check returns HTTP 503 and `status: unavailable`.

Built-in checks: `database`, `cache`, `queue`. Custom checks: class-strings implementing `StatusHealthCheck`.


```bash
GET /api/v1/status
```

Run diagnostics from the CLI:

```bash
php artisan laravel-controller:doctor
php artisan laravel-controller:doctor --json
```

## Custom Formatter

```php
<?php

namespace App\Support;

use JOOservices\LaravelController\Contracts\ResponseFormatter;

final class ApiResponseFormatter implements ResponseFormatter
{
    public function format(array $response): array
    {
        return [
            'ok' => $response['success'],
            'status' => $response['code'],
            'message' => $response['message'],
            'payload' => $response['data'],
            'error' => $response['errors'],
            'request_id' => $response['trace_id'],
        ];
    }
}
```

## Configuration

Important config keys:

- `response_profile` (`envelope` | `problem+json`)
- `response_formatter`
- `keys`
- `meta_headers`
- `trace_id.header`
- `use_translations`
- `success_codes`
- `validation.message`
- `routes.enabled`
- `routes.prefix`
- `routes.auto_map_host_routes`
- `status` (including pluggable checks)
- `pagination_links`
- `item_links`

## Current Limitations And Non-Goals

This package is:

- base API controller helpers
- standard response envelope helpers
- pagination and status response helpers
- OpenAPI envelope schema artifact
- formatter contract
- optional exception response helper

This package is not:

- CRUD generator
- service layer replacement
- repository replacement
- validation package
- full application exception-handler framework
- JSON:API full implementation
- idempotency store or rate-limit enforcer (headers are echoed only)
- business logic layer

## Documentation

- [Documentation Hub](docs/README.md)
- [Upgrade to v4](UPGRADE-4.0.md)
- [Architecture](docs/00-architecture/01-project-overview.md)
- [Getting Started](docs/01-getting-started/quick-start.md)
- [User Guide](docs/02-user-guide/response-envelopes.md)
- [Problem Details](docs/02-user-guide/problem-details.md)
- [OpenAPI Contract](docs/02-user-guide/openapi-contract.md)
- [Examples](docs/03-examples/basic-controller.md)
- [Development](docs/04-development/setup.md)
- [Release Process](docs/04-development/release-process.md)
- [Maintenance](docs/05-maintenance/01-risks-legacy-and-gaps.md)

## AI Contributor Support

- [AGENTS.md](AGENTS.md)
- [CLAUDE.md](CLAUDE.md)
- [AI Skills Map](ai/skills/README.md)
- [AI Skills Usage Guide](ai/skills/USAGE.md)

## Development Commands

```bash
composer lint
composer lint:all
composer lint:fix
composer test
composer test:coverage
composer check
composer ci
```

Prefer Docker (`docker compose run --rm php …`) when host PHP is not 8.5.

## Security And Contributing

Use GitHub issues for bug reports and security coordination unless a dedicated security policy is added. See [CONTRIBUTING.md](CONTRIBUTING.md).

## License

JOOservices Laravel Controller is open-sourced software licensed under the MIT license.
